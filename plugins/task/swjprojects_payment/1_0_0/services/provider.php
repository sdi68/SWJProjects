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

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Task\SWJProjectsPayment\Extension\SWJProjectsPayment;

/**
 * The task SWJProgects_payment plugin service provider
 * @package pkg_swjprojects_payments
 * @subpackage  task/swjprojects_payment
 * @version 1.0.0
 * @since 1.0.0
 *
 */

return new class implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param Container $container The DI container.
     *
     * @return  void
     *
     * @since   1.0.0
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $plugin = new SWJProjectsPayment(
                    $container->get(DispatcherInterface::class),
                    (array)PluginHelper::getPlugin('task', 'swjprojects_payment')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
