<?php
/*
 * @package    SW JProjects Component
 * @subpackage    system/SWJPayment plugin
 * @version    1.0.0
 * @author Econsult Lab.
 * @copyright  Copyright (c) 2023 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru
 */

defined('_JEXEC') or die('Restricted access');
/**
 * Отображения блока строки выбора платежного шлюза на странице проекта
 * @package pkg_swjprojects_payments
 * @subpackage  system/swjpayment
 * @version 1.0.0
 * @since 1.0.0
 *
 * @var Object $vars
 **/
?>
<?php if (!isset($vars->error)): ?>
    <label for="payment_gateway">
        <input type="radio" name="payment_gateway" onchange="swj_payment_choise(this); return true;"
               value="<?php echo $vars->plugin; ?>"/>
        <span><?php echo $vars->payment_name; ?></span>
    </label>
<?php else: ?>
    <label><?php echo $vars->payment_name; ?></label>
    <div class="alert alert-danger"><?php echo $vars->error->getMessage(); ?></div>
<?php endif; ?>
