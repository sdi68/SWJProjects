<?php
/*
 * @package    SW JProjects Payment
 * @subpackage plugin system/swjprojects
 * @version    1.0.0
 * @author     Econsult lab - https://econsultlab.ru
 * @copyright  Copyright (c) 2022 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru/
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');
/**
 * Отображения блока оплаты на странице проекта
 * @package pkg_swjprojects_payments
 * @subpackage  system/swjpayment
 * @version 1.0.0
 * @since 1.0.0
 *
 * @var Object $vars
 **/
?>

<div class="swj-payment-block">
    <?php if (!isset($vars->error)): ?>
        <div class="swj-order-details">
            <input type="submit" id="swj-order-btn" class="btn btn-success btn-large"
                   value="<?php echo Text::_('PLG_SYSTEM_SWJPAYMENT_BTN_CREATE_ORDER'); ?>"/>
        </div>
        <div class="swj-get_payment hidden">
            <div><strong><?php echo Text::_('PLG_SYSTEM_SWJPAYMENT_PAYMENT_BLOCK_TITLE'); ?></strong></div>

            <div class="swj-items-wrap">
                <?php echo $vars->html; ?>
            </div>
            <input type="submit" id="swj-pay-btn" disabled="disabled" class="btn btn-success btn-large"
                   value="<?php echo Text::_('PLG_SYSTEM_SWJPAYMENT_BTN_SUBMIT'); ?>"/>
        </div>
        <input type="hidden" name="project_price" value="<?php echo $vars->item->payment->get('price'); ?>"/>
        <input type="hidden" name="project_title" value="<?php echo $vars->item->title; ?>"/>
        <input type="hidden" name="project_id" value="<?php echo $vars->item->id; ?>"/>
        <input type="hidden" name="order_number" value="<?php echo $vars->order_number; ?>"/>
        <input type="hidden" name="user_email" value="<?php echo $vars->user_email; ?>"/>
        <input type="hidden" name="user" value="<?php echo $vars->user_id; ?>"/>

    <?php else: ?>
        <div class="alert alert-danger"><?php echo $vars->error->getMessage(); ?></div>
    <?php endif; ?>
</div>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', (event) => {
        var _inps = document.querySelectorAll('input[name = "payment_gateway"]');

        if (_inps.length) {
            _inps[0].setAttribute('checked', "checked");
            swj_payment_choise(_inps[0]);
        }

        var _btn_order = document.getElementById("swj-order-btn");
        if (typeof _btn_order === "undefined" || _btn_order == null)
            return;

        _btn_order.addEventListener('click', (event) => {
            let _data = {
                note: '',
                email: document.querySelector('input[name = "user_email"]').value,
                order: document.querySelector('input[name = "order_number"]').value,
                user: document.querySelector('input[name = "user"]').value,
                projects: document.querySelector('input[name = "project_id"]').value,
                date_start: '',
                date_end: '',
                limit: 0,
                state: 0,
                project_title: document.querySelector('input[name = "project_title"]').value,
                project_price: document.querySelector('input[name = "project_price"]').value,
            };
            console.log(_data);
            SWJPayment.createNewOrder(_data,<?php echo (int)$this->component_params->get('swjpayment_logging', 0); ?>);
        });

    });

    function swj_payment_choise(inp) {
        if (inp.getAttribute('checked') == "checked") {
            document.getElementById('swj-pay-btn').removeAttribute('disabled');
        } else {
            document.getElementById('swj-pay-btn').setAttribute('disabled', 'disabled');
        }
    }

</script>