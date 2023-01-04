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
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\User;
use Joomla\CMS\Version;

/**
 * @package pkg_swjprojects_payments
 * @subpackage  system/swjpayment
 * @version 1.0.0
 * @since 1.0.0
 */
class SWJPaymentHelper
{
    /**
     * Сохранить лог плагина в файл.
     *
     * @param string $name Имя файла
     *
     * @param array $data Данные для сохранения в лог
     *
     * @param bool $enabled Разрешить логирование
     *
     * @since   1.0.0
     *
     */
    public static function Storelog(string $name, array $data, bool $enabled = false): void
    {
        if (!$enabled)
            return;

        // Add timestamp to the entry
        $entry = Factory::getDate()->format('[Y-m-d H:i:s]') . ' - ' . json_encode($data) . "\n";

        // Compute the log file's path.
        static $path;
        if (!$path) {
            $config = new JConfig();
            $path = $config->log_path . '/' . $name . '.php';
            if (!file_exists($path)) {
                file_put_contents($path, "<?php die('Forbidden.'); ?>\n\n");
            }
        }
        file_put_contents($path, $entry, FILE_APPEND);
    }

    /**
     * Формирует данные пользователя сайта
     * @param object $vars Объект для сохранения свойств данных пользователя
     * @param User $user Пользователь
     * @return void
     * @throws Exception
     * @since 1.0.0
     */
    public static function getCurrentUserData(object &$vars, User $user)
    {
        //$user       = Factory::getApplication()->getIdentity();
        if ($user->get('id', 0)) {
            $vars->user = $user;
            $vars->user_email = $user->get('email', '');
            $vars->user_id = $user->get('id', '');
            // TODO добавить телефон пользователя
            $vars->phone = $user->get('phone', '');
            $vars->user_fullname = $user->get('name', '');
        } else {
            $vars->error = new Exception(Text::_('PLG_SYSTEM_SWJPAYMENT_PAYMENT_USER_NOT_LOGGED_ON'));
        }
    }


    /**
     * Получить текущего пользователя
     * @return User|null
     * @throws Exception
     * @since 1.0.0
     */
    public static function getCurrentUser(): ?User
    {
        return Factory::getApplication()->getIdentity();
    }

    /**
     * Формирует информацию по версии Joomla
     * @return string
     * @since 1.0,0
     */
    public static function _getJoomlaVersionSuffix()
    {
        return ((new Version())->isCompatible('4.0')) ? '4' : '3';
    }

}