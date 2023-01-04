<?php
/*
 * @package    SW JProjects Payment
 * @subpackage plugin task/swjpayment
 * @version    1.0.0
 * @author     Econsult lab - https://econsultlab.ru
 * @copyright  Copyright (c) 2022 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru/
 */

namespace Joomla\Plugin\Task\SWJProjectsPayment\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use LogicException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

if (!class_exists('SWJPaymentOrderHelper'))
    require_once JPATH_PLUGINS . '/system/swjpayment/helpers/order.php';

/**
 * The task SWJProgects_payment plugin
 * @package pkg_swjprojects_payments
 * @subpackage  task/swjprojects_payment
 * @version 1.0.0
 * @since 1.0.0
 *
 */
final class SWJProjectsPayment extends CMSPlugin implements SubscriberInterface
{
    use TaskPluginTrait;

    /**
     * @var string[]
     *
     * @since 1.0.0
     */
    protected const TASKS_MAP = [
        'swjprojects_payment.deleteEmptyOrders' => [
            'langConstPrefix' => 'PLG_TASK_SWJPROJECTS_PAYMENT_TASK_DELETE_EMPTY_ORDERS',
            'form' => 'delete_empty_orders',
            'method' => 'deleteEmptyOrders',
        ],
    ];

    /**
     * @inheritDoc
     *
     * @return string[]
     *
     * @since 1.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onTaskOptionsList' => 'advertiseRoutines',
            'onExecuteTask' => 'standardRoutineHandler',
            'onContentPrepareForm' => 'enhanceTaskItemForm',
        ];
    }

    /**
     * @var boolean
     * @since 1.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Constructor.
     *
     * @param DispatcherInterface $dispatcher The dispatcher
     * @param array $config An optional associative array of configuration settings
     *
     * @since   1.0.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config)
    {
        parent::__construct($dispatcher, $config);
    }

    /**
     * @param ExecuteTaskEvent $event The onExecuteTask event
     *
     * @return integer  The exit code
     *
     * @throws \RuntimeException
     * @throws LogicException*@throws \Exception
     * @throws \Exception
     * @since 1.0.0
     */
    protected function deleteEmptyOrders(ExecuteTaskEvent $event): int
    {
        $params = $event->getArgument('params');

        $ids = \SWJPaymentOrderHelper::getEmptyOrdersIds();
        $this->logTask($this->getApplication()->getLanguage()->_('PLG_TASK_SWJPROJECTS_PAYMENT_TASK_DELETE_EMPTY_ORDERS_STARTED'), 'info');
        if (is_array($ids)) {
            $this->logTask(
                sprintf(
                    $this->getApplication()->getLanguage()->_('PLG_TASK_SWJPROJECTS_PAYMENT_TASK_DELETE_EMPTY_ORDERS_TO_DELETED'),
                    count($ids)
                ), 'info');

            foreach ($ids as $order_number) {
                if (\SWJPaymentOrderHelper::deleteOrderByOrderNumber($order_number)) {
                    // успешное удаление заказа
                    $this->logTask(
                        sprintf(
                            $this->getApplication()->getLanguage()->_('PLG_TASK_SWJPROJECTS_PAYMENT_TASK_DELETE_EMPTY_ORDERS_DELETE_ORDER_SUCCESS'),
                            $order_number
                        ), 'info');
                } else {
                    // Ошибка при удалении заказа
                    $this->logTask(
                        sprintf(
                            $this->getApplication()->getLanguage()->_('PLG_TASK_SWJPROJECTS_PAYMENT_TASK_DELETE_EMPTY_ORDERS_DELETE_ORDER_ERROR'),
                            $order_number
                        ), 'error');

                }
            }
        } else {
            $this->logTask($this->getApplication()->getLanguage()->_('PLG_TASK_SWJPROJECTS_PAYMENT_TASK_DELETE_EMPTY_ORDERS_NONE'), 'info');
        }
        $this->logTask($this->getApplication()->getLanguage()->_('PLG_TASK_SWJPROJECTS_PAYMENT_TASK_DELETE_EMPTY_ORDERS_FINISHED'), 'info');
        return TaskStatus::OK;
    }
}
