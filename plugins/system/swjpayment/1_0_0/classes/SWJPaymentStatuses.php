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

defined('_JEXEC') or die;

if (!class_exists('AbstractEnum')) {
    require_once __DIR__ . '/AbstractEnum.php';
}

/**
 * Нумератор статусов заказа
 * @package pkg_swjprojects_payments
 * @subpackage  system/swjpayment
 * @version 1.0.0
 * @since 1.0.0
 */
class SWJPaymentStatuses extends AbstractEnum
{

    /**
     * Статус ожидания оплаты
     * @since 1.0.0
     */
    const SWJPAYMENT_STATUS_PENDING = "P";
    /**
     * Статус успешной оплаты заказа
     * @since 1.0.0
     */
    const SWJPAYMENT_STATUS_CONFIRMED = "C";
    /**
     * Статус возврата оплаты
     * @since 1.0.0
     */
    const SWJPAYMENT_STATUS_REFUND = "RF";
    /**
     * Статус отмены оплаты заказа
     * @since 1.0.0
     */
    const SWJPAYMENT_STATUS_CANCELED = "E";
    /**
     * Статус запрета на оплату
     * @since 1.0.0
     */
    const SWJPAYMENT_STATUS_DENIED = "D";

    /**
     * Статусы, нуждающиеся в валидации
     * @var bool[]
     * @since 1.0.0
     */
    protected static $validValues = array(
        'SWJPAYMENT_STATUS_PENDING' => true,
        'SWJPAYMENT_STATUS_CONFIRMED' => true,
        'SWJPAYMENT_STATUS_REFUND' => true,
        'SWJPAYMENT_STATUS_CANCELED' => true,
        'SWJPAYMENT_STATUS_DENIED' => true
    );

}