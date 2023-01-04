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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;

if (!class_exists('SWJPaymentStatuses'))
    require_once JPATH_PLUGINS . '/system/swjpayment/classes/SWJPaymentStatuses.php';

JLoader::register('SWJProjectsHelperKeys', JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/keys.php');
BaseDatabaseModel::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_swjprojects/models');
Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_swjprojects/tables');

/**
 * Helper`s для работы с дополнением заказа
 * @package pkg_swjprojects_payments
 * @subpackage  system/swjpayment
 * @version 1.0.0
 * @since 1.0.0
 */
class SWJPaymentOrderHelper
{
    /**
     * Обновляет информацию по заказу
     * @param array $data Информация по заказу
     * @return bool
     * @since 1.0.0
     */
    public static function updateOrder(array $data): bool
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__swjprojects_order'))
            ->set($db->quoteName('processor') . ' = ' . $db->quote($data['processor']))
            ->set($db->quoteName('transaction_id') . ' = ' . $db->quote($data['transaction_id']))
            ->set($db->quoteName('extra') . ' = ' . $db->quote($data['extra']))
            ->set($db->quoteName('payment_received_date') . ' = ' . $db->quote($data['payment_received_date']))
            ->set($db->quoteName('payment_status') . ' = ' . $db->quote($data['payment_status']))
            ->where($db->quoteName('key_id') . ' IN (SELECT ' . $db->quoteName('id') . ' FROM ' . $db->quoteName('#__swjprojects_keys') .
                ' WHERE ' . $db->quoteName('order') . ' = ' . $db->quote($data['order_number']) . ')');
        $db->setQuery($query);
        return $db->execute();
    }

    /**
     * Снимает или ставит с публикации ключ
     * @param int $key_id Идентификатор ключа
     * @param bool $publish Опубликовать/снять с публикации
     * @return bool
     * @throws Exception
     * @since 1.0.0
     */
    public static function setOrderState(int $key_id, bool $publish = false): bool
    {
        /** @var  SWJProjectsModelKey $model */
        $model = BaseDatabaseModel::getInstance('Key', 'SWJProjectsModel', ['ignore_request' => true]);
        $order = $model->getItem($key_id);
        $order->state = (int)$publish;
        $order->key_regenerate = false;
        return $model->save(json_decode(json_encode($order), true));
    }

    /**
     * Получает заказ по номеру заказа
     * @param string $order_number Номер заказа
     * @return mixed|null
     * @since 1.0.0
     */
    public static function getOrderByOrderNumber(string $order_number): mixed
    {
        if ($order_number) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('id')
                ->from($db->quoteName('#__swjprojects_keys'))
                ->where($db->quoteName('order') . ' = ' . $db->quote($order_number));
            $db->setQuery($query);
            $id = $db->loadResult();
            return self::getOrderById($id);
        }
        return null;
    }


    /**
     * Удаляет заказ по номеру заказа
     * @param string $order_number Номер заказа
     * @return bool
     * @since 1.0.0
     */
    public static function deleteOrderByOrderNumber(string $order_number): bool
    {
        $order = self::getOrderByOrderNumber($order_number);
        if (is_null($order))
            return false;
        /** @var  SWJProjectsModelKey $model */
        //$model = BaseDatabaseModel::getInstance('Key', 'SWJProjectsModel',['ignore_request' => true]);
        $id = $order->id;
        // TODO через модель не удаляется т.к. не позволяют разрешения пока удалим вручную
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__swjprojects_keys'))
            ->where($db->quoteName('id') . '=' . $db->quote($id));
        $ret = $db->setQuery($query)->execute();
        $ret &= self::_deleteExtraOrder($id);
        return $ret;
    }

    /**
     * Создает новый заказ
     * @param array $order_data Данные по заказу
     * @return int|null
     * @throws Exception
     * @since 1.0.0
     */
    public static function createNewOrder(array $order_data): ?int
    {
        /** @var  SWJProjectsModelKey $model */
        $model = BaseDatabaseModel::getInstance('Key', 'SWJProjectsModel', ['ignore_request' => true]);
        $key_id = $model->save($order_data);
        if ($key_id) {
            $extra = array(
                'key_id' => $key_id,
                'payment_create_date' => Factory::getDate()->toSql(),
                'processor' => '',
                'transaction_id' => '',
                'payment_received_date' => '',
                'extra' => '');
            self::_createNewExtraOrder($extra);
            return (int)$key_id;
        }
        return null;
    }

    /**
     * Получает заказ по идентификатору
     * @param int $id
     * @return mixed|null
     * @since 1.0.0
     */
    public static function getOrderById(int $id): mixed
    {
        if ($id) {
            /** @var  SWJProjectsModelKey $model */
            $model = BaseDatabaseModel::getInstance('Key', 'SWJProjectsModel', ['ignore_request' => true]);
            $order = $model->getItem($id);
            // Получаем дополнение заказа
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__swjprojects_order'))
                ->where($db->quoteName('key_id') . ' = ' . $db->quote($id));
            $db->setQuery($query);
            $res = $db->loadObject();
            // Объединяем с доп. полями заказа
            $order->extra = $res;
            return $order;
        }
        return null;
    }

    /**
     * Создает дополнение к заказу с платежными данными
     * @param array $data Данные заказа
     * @return void
     * @since 1.0.0
     */
    private static function _createNewExtraOrder(array $data): void
    {
        $db = Factory::getDbo();
        $columns = 'key_id,payment_create_date,processor,transaction_id,payment_received_date,extra';

        $values = $data['key_id'] . ',"' . self::_getEmptyDateFormat($data['payment_create_date']) . '","' . $data['processor'] . '","' . $data['transaction_id'] . '","'
            . self::_getEmptyDateFormat($data['payment_received_date']) . '","' . $data['extra'] . '"';

        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__swjprojects_order'))
            ->columns($columns)
            ->values($values);
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Удаляет платежное дополнение к заказу
     * @param int $key_id Идентификатор ключа
     * @return bool
     * @since 1.0.0
     */
    private static function _deleteExtraOrder(int $key_id): bool
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__swjprojects_order'))
            ->where($db->quoteName('key_id') . ' = ' . $db->quote($key_id));
        $db->setQuery($query);
        return $db->execute();
    }

    /**
     * Формирует пустое значение для даты в sql запросы
     * @param $date
     * @return mixed|string
     * @since 1.0.0
     */
    private static function _getEmptyDateFormat($date): mixed
    {
        if (empty($date)) {
            return Factory::getDbo()->getNullDate();
        }
        return $date;
    }


    /**
     * Проверка корректности закза
     * TODO Не реализовано, надо определить критерии
     * @param $order
     * @return bool
     * @since 1.0.0
     */
    public static function validateOrder($order): bool
    {
        if ($order->extra && $order->extra->transaction_id || 1) {
            return true;
        }
        return false;
    }

    /**
     * Получает идентификаторы не оплаченных заказов
     * @return array|mixed
     * @since 1.0.0
     */
    public static function getEmptyOrdersIds(): mixed
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('k.order'))
            ->from($db->quoteName('#__swjprojects_order', 'o'))
            ->innerJoin($db->quoteName('#__swjprojects_keys', 'k') . ' ON ' . $db->quoteName('k.id') . ' = ' . $db->quoteName('o.key_id'))
            ->where($db->quoteName('o.transaction_id') . ' = ""');
        $db->setQuery($query);
        return $db->loadColumn();
    }

    /**
     * Проверяет успешную оплату заказа
     * @param string $order_number Номер заказа
     * @return bool
     * @since 1.0.0
     */
    public static function IsOrderPaid(string $order_number): bool
    {
        $order = self::getOrderByOrderNumber($order_number);
        if ($order->extra && $order->extra->payment_status) {
            return $order->extra->payment_status == SWJPaymentStatuses::SWJPAYMENT_STATUS_CONFIRMED;
        }
        return false;
    }

}