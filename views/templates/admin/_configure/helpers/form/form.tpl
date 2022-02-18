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

{extends file="helpers/form/form.tpl"}
{block name="input_row"}
    {if $input.type == 'th_title'}
        <div class="form-group">
            <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                <div class="custom-html-title">{$input.name|escape:'htmlall':'UTF-8'}</div>
            </div>
        </div>
    {elseif $input.type == 'th_sub_title'}
        <div class="form-group">
            <div class="col-xs-12 col-lg-8 col-lg-offset-2">
                <div class="custom-html-sub-title">{$input.name|escape:'htmlall':'UTF-8'}</div>
            </div>
        </div>
    {elseif $input.type == 'th_html'}
        <div class="form-group th-html-input">
            <div class="col-xs-12 col-lg-10 col-lg-offset-1">
                {$input.html_content nofilter}
            </div>
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
