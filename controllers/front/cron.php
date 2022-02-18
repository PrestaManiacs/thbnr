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

class ThbnrCronModuleFrontController extends ModuleFrontController
{
    public $ajax;

    public function display()
    {
        $this->ajax = 1;

        $key = Configuration::get('THBNR_KEY');
        if (empty($key) || $key !== Tools::getValue('key')) {
            Tools::redirect('404');
            die;
        }

        if (!Configuration::get('THBNR_LIVE_MODE')) {
            Tools::redirect('404');
            die;
        }

        $this->module->handleCurrencies();
        die;
    }
}
