<?php
/**
 * 2006-2022 THECON SRL
 *
 * NOTICE OF LICENSE
 *
 * DISCLAIMER
 *
 * YOU ARE NOT ALLOWED TO REDISTRIBUTE OR RESELL THIS FILE OR ANY OTHER FILE
 * USED BY THIS MODULE.
 *
 * @author    THECON SRL <contact@thecon.ro>
 * @copyright 2006-2022 THECON SRL
 * @license   Commercial
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Thbnr extends Module
{
    const THBNR_RATES_URL = 'http://www.bnro.ro/nbrfxrates.xml';
    const THBNR_ISO_CODE_SOURCE = 'RON';

    public function __construct()
    {
        $this->name = 'thbnr';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Presta Maniacs';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Live Exchange Rate from BNR (National Bank of Romania)');
        $this->description = $this->l('Enable Live Exchange Rates for your Shop Currencies)');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('THBNR_LIVE_MODE', false);
        Configuration::updateValue('THBNR_KEY', Tools::strtoupper(Tools::passwdGen(12)));

        return parent::install() &&
            $this->registerHook('backOfficeHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('THBNR_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $message = '';

        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitThbnrModule')) == true) {
            $this->postProcess();

            if (count($this->_errors)) {
                $message = $this->displayError($this->_errors);
            } else {
                $message = $this->displayConfirmation($this->l('Successfully saved!'));
            }
        }

        $this->context->smarty->assign(
            array(
                'module_dir' => $this->_path,
                'thbnr_cron_url' => $this->context->link->getModuleLink($this->name, 'cron', array('key' => Configuration::get('THBNR_KEY')))
            )
        );

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        $maniacs = $this->context->smarty->fetch($this->local_path.'views/templates/admin/maniacs.tpl');

        return $maniacs.$message.$output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitThbnrModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $default_curency_obj = Currency::getDefaultCurrency();

        $this->context->smarty->assign(
            array(
                'th_df_title' => $default_curency_obj->name
            )
        );
        $dc_html = $this->context->smarty->fetch($this->local_path.'views/templates/admin/default_currency.tpl');


        $inputs = array(
            array(
                'type' => 'switch',
                'label' => $this->l('Live mode:'),
                'name' => 'THBNR_LIVE_MODE',
                'is_bool' => true,
                'desc' => $this->l('Use this module in live mode'),
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                ),
            ),
            array(
                'type' => 'th_title',
                'label' => '',
                'name' => $this->l('Enable Live Exchange by Currency'),
            ),
            array(
                'type' => 'th_html',
                'name' => 'html_default_curreny',
                'html_content' => $dc_html
            )
        );

        $currencies = Currency::getCurrenciesByIdShop($this->context->shop->id);
        foreach ($currencies as $currency) {
            if ($default_curency_obj->id == $currency['id']) {
                continue;
            }

            $inputs[] = array(
                'type' => 'switch',
                'label' => $currency['name'].':',
                'name' => 'THBNR_'.$currency['iso_code'],
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                )
            );
        }

        $inputs[] = array(
            'type' => 'th_title',
            'label' => '',
            'name' => $this->l('Logs'),
        );

        $inputs[] = array(
            'type' => 'switch',
            'label' => $this->l('Save Update Informations:'),
            'name' => 'THBNR_ENABLE_LOGS',
            'is_bool' => true,
            'values' => array(
                array(
                    'id' => 'active_on',
                    'value' => true,
                    'label' => $this->l('Enabled')
                ),
                array(
                    'id' => 'active_off',
                    'value' => false,
                    'label' => $this->l('Disabled')
                )
            )
        );

        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => $inputs,
                'submit' => array(
                    'title' => $this->l('Save Settings'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        $values =  array(
            'THBNR_LIVE_MODE' => Tools::getValue('THBNR_LIVE_MODE', Configuration::get('THBNR_LIVE_MODE')),
            'THBNR_ENABLE_LOGS' => Tools::getValue('THBNR_ENABLE_LOGS', Configuration::get('THBNR_ENABLE_LOGS'))
        );

        $currencies = Currency::getCurrencies();
        foreach ($currencies as $currency) {
            $currency_key = 'THBNR_'.$currency['iso_code'];
            $values[$currency_key] = Tools::getValue($currency_key, Configuration::get($currency_key));
        }

        return $values;
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    public function getCurrencyRates()
    {
        $get_rates = file_get_contents(self::THBNR_RATES_URL);
        $rates = $this->parseXMLDocument($get_rates);

        return $rates;
    }

    public function parseXMLDocument($xml_document)
    {
        $currency = array();
        $xml = new SimpleXMLElement($xml_document);
        foreach($xml->Body->Cube->Rate as $line) {
            $currency[] = array(
                "name" => $line["currency"],
                "value" => $line,
                "multiplier" => $line["multiplier"]
            );
        }

        return $currency;
    }

    public function getExchangeRate($currencies, $currency)
    {
        foreach($currencies as $line) {
            if($line["name"] == $currency) {
                if ($line['multiplier']) {
                    return $line["value"] / $line['multiplier'];
                } else {
                    return $line["value"];
                }
            }
        }

        return false;
    }

    public function addLog($message)
    {
        if (Configuration::get('THBNR_ENABLE_LOGS')) {
            $message = 'BNR ER: ' . $message;
            PrestaShopLogger::addLog($message, 1, null, null, null, true);
        }
    }

    public function handleCurrencies()
    {
        $rates = $this->getCurrencyRates();
        if (!is_array($rates) || count($rates) < 2) {
            $this->addLog($this->l('Cannot parse feed.'));
            return false;
        }

        if (!$defaultCurrency = Currency::getDefaultCurrency()) {
            $this->addLog($this->l('No default currency.'));
            return false;
        }

        $currencies = Currency::getCurrencies(true, false, true);
        foreach ($currencies as $currency) {
            /** @var Currency $currency */
            if ($currency->id != $defaultCurrency->id) {
                if (Configuration::get('THBNR_'.$currency->iso_code)) {
                    $this->refreshCurrency($currency, $rates, $defaultCurrency);
                }
            }
        }
    }

    public function refreshCurrency($currency_obj, $data, $defaultCurrency)
    {
        // fetch the exchange rate of the default currency
        $exchangeRate = 1;
        $tmp = $currency_obj->conversion_rate;
        if ($defaultCurrency->iso_code != self::THBNR_ISO_CODE_SOURCE) {
            $bnr_rate = $this->getExchangeRate($data, $defaultCurrency->iso_code);
            if ($bnr_rate) {
                $exchangeRate = round((float) $bnr_rate, 6);
            }
        }

        if ($defaultCurrency->iso_code == $currency_obj->iso_code) {
            $currency_obj->conversion_rate = 1;
        } else {
            if ($currency_obj->iso_code == self::THBNR_ISO_CODE_SOURCE) {
                $rate = 1;
            } else {
                $bnr_rate = $this->getExchangeRate($data, $currency_obj->iso_code);
                if ($bnr_rate) {
                    $rate = (float) $bnr_rate;
                }
            }

            if (isset($rate)) {
                $currency_obj->conversion_rate = round($exchangeRate / $rate, 6);
            }
        }

        if ($tmp != $currency_obj->conversion_rate) {
            $this->addLog($currency_obj->iso_code.' change rate - '.$tmp.' -> '.$currency_obj->conversion_rate);
            $currency_obj->update();
        } else {
            $this->addLog($currency_obj->iso_code.' change rate - '.$tmp.' -> '.$currency_obj->conversion_rate);
        }
    }
}
