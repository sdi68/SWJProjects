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
 * Отображения блока деталей заказа на странице проекта
 * @package pkg_swjprojects_payments
 * @subpackage  system/swjpayment
 * @version 1.0.0
 * @since 1.0.0
 *
 *  @var Object $vars
 **/

if(!isset($vars->error)):
?>
<div class = "swj-order-details-wrap" >
    <div class = "order-item">
    <label>Номер заказа</label><div><?php echo $vars->order; ?></div>
    </div>
    <div class = "order-item">
        <label>Компонент</label><div><?php echo $vars->project_title; ?></div>
    </div>
    <div class = "order-item">
        <label>Цена</label><div><?php echo $vars->project_price. '&nbsp;&#x20bd;' ; ?></div>
    </div>
    <?php if($vars->date_start): ?>
        <div class = "order-item">
            <label>Лицензия действует с</label><div><?php echo $vars->date_start; ?></div>
        </div>
    <?php endif; ?>
    <?php if($vars->date_end): ?>
        <div class = "order-item">
            <label>Лицензия действует по</label><div><?php echo $vars->date_end == "0000-00-00 00:00:00" ? "Бессрочно" : $vars->date_end; ?></div>
        </div>
    <?php endif; ?>
    <div class = "order-item">
        <label>Покупатель</label><div><?php echo $vars->user_fullname; ?></div>
    </div>
    <div class = "order-item">
        <label>Email</label><div><?php echo $vars->user_email; ?></div>
    </div>
    <?php if($vars->phone): ?>
        <div class = "order-item">
            <label>Телефон</label><div><?php echo $vars->user_phone; ?></div>
        </div>
    <?php endif; ?>
    <?php if($vars->phone): ?>
        <div class = "order-item">
            <label>Email</label><div><?php echo $vars->user_email; ?></div>
        </div>
    <?php endif; ?>
</div>
<?php else: ?>
<div class = "alert alert-danger"><?php echo Text::_('PLG_SYSTEM_SWJPAYMENT_CREATE_ORDER_ERROR');?></div>
<?php endif; ?>
