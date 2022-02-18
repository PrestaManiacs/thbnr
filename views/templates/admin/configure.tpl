{*
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
*}

<div class="panel">
	<h3><i class="icon icon-info"></i> {l s='Live Exchange Rate from BNR (National Bank of Romania)' mod='thbnr'}</h3>
	<p>
		<strong>{l s='The data are updated in real time, shortly after 13:00, every banking day.' mod='thbnr'}</strong><br />
		<a href="https://www.bnr.ro/nbrfxrates.xml" target="_blank">{l s='https://www.bnr.ro/nbrfxrates.xml' mod='thbnr'}</a><br />
	</p>

	<p>
		{l s='To automatically update the exchange rate, you can set up a Cron Job with the following link:' mod='thbnr'}
		<a href="{$thbnr_cron_url|escape:'html':'UTF-8'}" target="_blank">{$thbnr_cron_url|escape:'htmla':'UTF-8'}</a>
	</p>
</div>
