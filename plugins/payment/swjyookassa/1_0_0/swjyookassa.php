<?php
/*
 * @package    SW JProjects Component
 * @subpackage    payment/SWJYOOKassa plugin
 * @version    1.0.0
 * @author Econsult Lab.
 * @copyright  Copyright (c) 2023 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use YooKassa\Client;

defined('_JEXEC') or die('Restricted access');

if (!class_exists('SWJGatewayPlugin')) {
    require_once JPATH_PLUGINS . '/system/swjpayment/classes/swjgateway_plugin.php';
}

$lang = JFactory::getLanguage();
$lang->load('plg_payment_swjyookassa', JPATH_ADMINISTRATOR);
require __DIR__ . '/vendor/autoload.php';


/**
 * Платежный плагин YOOKassa
 * @package pkg_swjprojects_payments
 * @subpackage  payment/swjyookassa
 * @version 1.0.0
 * @since 1.0.0
 */
class PlgPaymentSWJYookassa extends SWJGatewayPlugin
{

    /**
     * @var string Id shop in Yookassa
     * @since 1.0.0
     */
    private $shop_id = '';

    /**
     * @var string secret key yor shop in Yookassa
     * @since 1.0.0
     */
    private $secretKey = '';


    /**
     * Конструктор.
     *
     * @param $subject
     * @param $config
     * @throws Exception
     * @since 1.0.0
     */
    function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->_plugin_path = __DIR__;
        $this->shop_id = $this->getPluginParam('shop_id', '');
        $this->secretKey = $this->getPluginParam('secretKey', '');
    }

    /**
     * Переопределенный метод формирования платежных параметров
     * @param Object $vars
     * @return void
     * @since 1.0.0
     */
    protected final function _getPaymentHTMLParams(object &$vars): void
    {
        $this->_logging(array('_getPaymentHTMLParams started...'));
        $this->_logging(array('$vars', $vars));
        $idempotenceKey = uniqid('', true);
        $payment_params = array();
        $payment_params = array_merge($payment_params, $this->_buildPaymentDescription($vars));
        $payment_params = array_merge($payment_params, $this->_buildPaymentAmount($vars));
        $payment_params = array_merge($payment_params, array('capture' => true));
        $payment_params = array_merge($payment_params, $this->_buildPaymentReceipt($vars));
        $payment_params = array_merge($payment_params, $this->_buildPaymentMethodId($vars));
        $payment_params = array_merge($payment_params, $this->_buildPaymentMethodData($vars));
        $payment_params = array_merge($payment_params, $this->_buildPaymentConfirmation($vars));
        $payment_params = array_merge($payment_params, $this->_buildPaymentMetaData($vars));
        $this->_logging(array('$payment_params', $payment_params));
        $client = new Client();
        $client->setAuth($this->shop_id, $this->secretKey);
        try {
            $payment = $client->createPayment($payment_params, $idempotenceKey);
        } catch (Exception $e) {
            $this->_logging(array(Text::_('PLG_PAYMENT_SWJYOOKASSA_CREATE_PAYMENT_ERROR'), $e));
            $vars->error = $e;
            return;
        }
        $this->_logging(array('payment', $payment));
        // SDI TODO At this moment used only redirect

        switch ($this->getPluginParam('payment_confirmation_type', '')) {
            default:
            case 0:
                // redirect
                $confirmation = $payment->getConfirmation();
                $vars->action_url = $confirmation->getConfirmationUrl();
                break;
        }
        return;
    }


    /**
     * Convert number to format string
     * @param $number number to convert
     *
     * @return string
     *
     * @since 1.0.0
     */
    protected function _convertNumber($number): string
    {
        return number_format($number, 2, '.', '');
    }

    /**
     * Build payment description section of Payment object
     * @param $vars
     *
     * @return array
     *
     * @since 1.0.0
     */
    private function _buildPaymentDescription($vars)
    {
        $ret = Text::sprintf('PLG_PAYMENT_SWJYOOKASSA_ORDER_DESCRIPTION', isset($vars->item->title) && !empty($vars->item->title) ? strip_tags($vars->item->title) : '-', isset($vars->order_number) && !empty($vars->order_number) ? strip_tags($vars->order_number) : '-');
        return array('description' => $ret);
    }

    /**
     * Build payment amount section of Payment object
     * @param $vars
     *
     * @return array
     *
     * @since 1.0.0
     */
    private function _buildPaymentAmount($vars): array
    {
        $out = array();
        $value = $vars->item->payment->get('price', 0);
        if ($value > 0) {
            $value = $this->_convertNumber($value);
            $currency = $vars->item->payment->get('currency_code', 'RUB');
            $out['amount'] = array(
                "value" => $value,
                "currency" => $currency
            );
        }
        return $out;
    }

    /**
     * Build payment receipt section of Payment object
     * @param $vars
     *
     * @return array
     *
     * @since 1.0.0
     */
    private function _buildPaymentReceipt($vars): array
    {
        $out['receipt'] = array();
        $customer = array();

        if (isset($vars->user_email) && !empty($vars->user_email)) {
            $customer['email'] = $vars->user_email;
            $out['receipt']['email'] = $vars->user_email;
        }

        if (isset($vars->user_firstname) && !empty($vars->user_firstname))
            $customer['full_name'] = $vars->user_fullname;

        if (isset($vars->user_phone) && !empty($vars->phone)) {
            $customer['phone'] = $vars->phone;
            $out['receipt']['phone'] = $vars->phone;
        }

        if (count($customer))
            $out['receipt']['customer'] = $customer;

        if ($this->getPluginParam('tax_system_code', ''))
            $out['receipt']['tax_system_code'] = $this->getPluginParam('tax_system_code', '');

        $item = array();

        if (isset($vars->item->title) && !empty($vars->item->title))
            $item['description'] = $vars->item->title;
        $item['quantity'] = 1;
        $item = array_merge($item, $this->_buildPaymentAmount($vars));

        if ($this->getPluginParam('vat_code', '0'))
            $item['vat_code'] = $this->getPluginParam('vat_code', '0');

        if (count($item))
            $out['receipt']['items'][] = $item;

        return $out;
    }

    /**
     * Build payment paymentMethodId section of Payment object
     * @param $vars
     *
     * @return array
     *
     * @since 1.0.0
     */

    private function _buildPaymentMethodId($vars): array
    {
        //SDI TODO not used at this moment
        $payment_method_id = '';
        $out = array();
        if (!empty($payment_method_id))
            $out = array('payment_method_id' => $payment_method_id);
        return $out;
    }

    /**
     * Build payment paymentMethodData section of Payment object
     * @param $vars
     *
     * @return array
     *
     * @since 1.0.0
     */
    private function _buildPaymentMethodData($vars): array
    {
        // SDI TODO used at this moment only bank_card
        $out['payment_method_data'] = array();
        switch ($this->getPluginParam('payment_method_type', 'bank_card')) {
            case "bank_card":
            default:
                $out['payment_method_data']['type'] = 'bank_card';
                break;
        }
        return $out;
    }

    /**
     * Build payment paymentConfirmation section of Payment object
     * @param $vars
     *
     * @return array
     *
     * @since 1.0.0
     */
    private function _buildPaymentConfirmation($vars): array
    {
        // SDI TODO used at this moment only redirect
        $out['confirmation'] = array();
        switch ($this->getPluginParam('payment_confirmation_type', '')) {
            case 0:
                $out['confirmation'] = array(
                    'type' => 'redirect',
                    'return_url' => $vars->return_url,
                    'confirmation_url' => '',
                );
                break;
            default:
                break;
        }

        return $out;
    }


    /**
     * Build payment paymentMetaData section of Payment object
     * @param $vars
     *
     * @return array
     *
     * @since 1.0.0
     */
    private function _buildPaymentMetaData($vars): array
    {
        $out['metadata']['order_id'] = $vars->order_number;
        return $out;
    }

    /**
     * Обработка поступивших данных платежа
     * @param string $processor
     * @param array $post
     * @param array $payment_response
     * @return bool
     * @since 1.0.0
     */
    public function onProcessPayment(string $processor, array $post, array &$payment_response): bool
    {
        $this->_logging(array("SWJYookassa.onProcessPayment"));
        if ($this->_checkProcessor($processor)) {
            $payment_response = array_merge($payment_response, array(
                    "processor" => $processor,
                    "payment_status" => $this->_translatePaymentStatus($post['object']['status']),
                    "transaction_id" => $post['object']['id'],
                    "payment_received_date" => Factory::getDate($post['object']['created_at'])->toSql(),
                    "order_number" => $post['object']['metadata']['order_id'],
                    "amount" => $post['object']['amount']['value'])
            );
            $this->_logging(array("SWJYookassa.onProcessPayment payment_response",$payment_response));
            return true;
        }
        return true;
    }

    /**
     * Трансляция статус платежа шлюза в общий статус
     * @param string $status
     * @return string
     * @since 1.0.0
     */
    protected final function _translatePaymentStatus(string $status): string
    {
        return $this->_response_statuses[$status] ?? SWJPaymentStatuses::SWJPAYMENT_STATUS_DENIED;
    }

    /**
     * Сопоставление статусов платежа шлюза с общими статусами
     * @return void
     * @since 1.0.0
     */
    protected final function _setResponseStatuses(): void
    {
        $this->_response_statuses = array(
            // платеж успешно завершен, деньги будут перечислены на ваш расчетный счет в соответствии с вашим договором с ЮKassa.
            // Это финальный и неизменяемый статус.
            'succeeded' => SWJPaymentStatuses::SWJPAYMENT_STATUS_CONFIRMED,
            // платеж создан и ожидает действий от пользователя
            'pending' => SWJPaymentStatuses::SWJPAYMENT_STATUS_PENDING,
            // платеж оплачен, деньги авторизованы и ожидают списания.
            'waiting_for_capture' => SWJPaymentStatuses::SWJPAYMENT_STATUS_PENDING,
            // платеж отменен. Вы увидите этот статус, если вы отменили платеж самостоятельно, истекло время на принятие платежа
            // или платеж был отклонен ЮKassa или платежным провайдером. Это финальный и неизменяемый статус.
            'canceled' => SWJPaymentStatuses::SWJPAYMENT_STATUS_CANCELED,
            //'Denied'=>SWJPaymentStatuses::SWJPAYMENT_STATUS_DENIED,
            //'Refunded'=>SWJPaymentStatuses::SWJPAYMENT_STATUS_REFUND
        );
    }

    /**
     * Запрос статуса платежа в шлюз
     * @param object $order
     * @param array $payment_response
     * @return bool
     * @since 1.0.0
     */
    public function onGetPaymentInfo(object $order, array &$payment_response): bool
    {
        if ($this->_checkProcessor($order->extra->processor)) {
            $client = new Client();
            $client->setAuth($this->shop_id, $this->secretKey);
            $this->_logging(array($order->extra->processor, 'Getting payment info ', $order->extra->transaction_id));
            try {
                $payment = $client->getPaymentInfo($order->extra->transaction_id);
            } catch (Exception $e) {
                $this->_logging(array($order->extra->processor, 'error by getting payment info', $e));
                $payment_response['error'] = $e->getMessage();
                $payment_response['processor'] = $order->extra->processor;
                $payment_response['order_number'] = $order->order;
                return true;
            }
            $this->_logging(array($order->extra->processor, 'payment info', $payment));
            $payment_response = array_merge($payment_response, array(
                    "processor" => $order->extra->processor,
                    "payment_status" => $this->_translatePaymentStatus($this->_translatePaymentStatus($payment->getStatus())),
                    "transaction_id" => $payment->getId(),
                    "payment_received_date" => Factory::getDate($payment->getCreatedAt())->toSql(),
                    "order_number" => $payment->getMetadata()->__get('order_id'),
                    "amount" => $payment->getAmount()->value,
                    "extra" => ""
                )
            );
            return true;
        }
        return false;
    }
}
