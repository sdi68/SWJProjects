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

use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

if (!class_exists('SWJPaymentPlugin')) {
    require_once JPATH_PLUGINS . '/system/swjpayment/classes/swjpayment_plugin.php';
}

/**
 * Абстрактный класс платежных плагинов пакета
 * @package pkg_swjprojects_payments
 * @subpackage  system/swjpayment
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class SWJGatewayPlugin extends SWJPaymentPlugin
{

    /**
     * Контекст проекта
     * @since 1.0.0
     */
    protected const COMPONENT_CONTEXT = 'com_swjprojects.project';

    /**
     * Наименование способа оплаты
     * @var mixed|string
     * @since 1.0.0
     */
    protected $_payment_name = '';

    /**
     * Статус оплаты, полученный от процессинга
     * @var mixed|string
     * @since 1.0.0
     */
    protected $_response_statuses = array();

    /**
     * Конструктор
     * @param $subject
     * @param $config
     * @throws Exception
     * @since 1.0.0
     */
    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);
        $this->_payment_name = $this->getPluginParam('plugin_name', '');
        $this->_setResponseStatuses();
    }

    /**
     * Формирует платежные параметры шлюза
     * @param object $vars Параметры платежа
     * @return void
     * @since 1.0.0
     */
    protected abstract function _getPaymentHTMLParams(object &$vars): void;


    /**
     * Обрабатывает результат оплаты от платежного шлюза
     * @param string $processor Наименование платежного шлюза
     * @param array $post Данные, полученные от шлюза
     * @param array $payment_response Обработанные данные платежа
     * @return bool
     * @since 1.0.0
     */
    public abstract function onProcessPayment(string $processor, array $post, array &$payment_response): bool;

    /**
     * Формирует данные по проведенному платежу
     * @param object $order Заказ
     * @param array $payment_response Данные проведенного платежа
     * @return bool
     * @since 1.0.0
     */
    public abstract function onGetPaymentInfo(object $order, array &$payment_response): bool;

    /**
     * Переводит статус оплаты шлюза в общий статус пакета
     * @param string $status
     * @return string
     * @since 1.0.0
     */
    protected abstract function _translatePaymentStatus(string $status): string;

    /**
     * Устанавливает соответствие статусов оплаты шлюза с общими статусами оплаты пакета
     * @return void
     * @since 1.0.0
     */
    protected abstract function _setResponseStatuses(): void;

    /**
     * Формирует общие данные для формирования платежа
     * @param object $item Заказ
     * @param object $vars параметры платежа
     * @return void
     * @since 1.0.0
     */
    private function __getCommonPaymentHTMLParams(object $item, object &$vars): void
    {
        $vars->payment_name = $this->_payment_name;
        $vars->plugin = $this->_name;
        $vars->item = $item;
        $vars->user_email = $this->current_user->get('email', '');
        $vars->user_id = $this->current_user->get('id', '');
        // TODO добавить телефон пользователя
        $vars->phone = $this->current_user->get('phone', '');
        $vars->user_fullname = $this->current_user->get('name', '');
        //$vars->return_url = URI::base() . 'index.php?option=com_ajax&plugin=swjpayment&format=json&action=delete_order&processor=swjyookassa&order_number=' . $vars->order_number;
        //$vars->return_url = URI::base() . 'index.php?option=com_ajax&plugin=swjpayment&format=json&action=user_return&processor=swjyookassa&order_number=' . $vars->order_number;
        $vars->return_url = URI::base() . 'index.php?option=com_ajax&plugin=swjpayment&format=json&action=user_return&order_number=' . $vars->order_number;
    }


    /**
     * Отображает платежную информацию в блоке проекта Пользователю
     * @param string $context Контекст отображения
     * @param object $item Заказ
     * @param string $item_layout Шаблон отображения
     * @param int $order_number Номер заказа
     * @param string $html HTML вывода блока
     * @return bool
     * @throws Exception
     * @since 1.0.0
     */
    public final function onShowPaymentHTML(string $context, object $item, string $item_layout, int $order_number, string &$html): bool
    {
        if ($this->_checkContext($context)) {
            $vars = new stdClass();
            $vars->common_layout = $item_layout;
            $vars->order_number = $order_number;
            $this->__getCommonPaymentHTMLParams($item, $vars);
            $this->_getPaymentHTMLParams($vars);
            $html .= $this->_buildLayout($vars);
        }
        return true;
    }

    /**
     * Проверка на контекст отображения проекта
     * @param string $context Контекст
     * @return bool
     * @since 1.0.0
     */
    protected final function _checkContext(string $context): bool
    {
        return $context == self::COMPONENT_CONTEXT;
    }

    /**
     * Проверка на соответствие платежного шлюза
     * @param string $processor Наименование шлюза
     * @return bool
     * @since 1.0.0
     */
    protected final function _checkProcessor(string $processor): bool
    {
        return !empty($processor) && $processor == $this->_name;
    }
}
