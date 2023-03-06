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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User;
use Joomla\CMS\Version;

if (!class_exists('SWJProjectsHelperRoute'))
    require_once JPATH_ROOT . '/components/com_swjprojects/helpers/route.php';

if (!class_exists('SWJPaymentStatuses'))
    require_once JPATH_PLUGINS . '/system/swjpayment/classes/SWJPaymentStatuses.php';

if (!class_exists('SWJPaymentOrderHelper'))
    require_once JPATH_PLUGINS . '/system/swjpayment/helpers/order.php';

if (!class_exists('SWJProjectsHelperTranslation'))
    require_once JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/translation.php';

if (!class_exists('SWJProjectsHelperImages'))
    require_once JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/images.php';

JLoader::register('SWJProjectsHelperKeys', JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/keys.php');
BaseDatabaseModel::addIncludePath(JPATH_ROOT . '/components/com_swjprojects/models');
Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_swjprojects/tables');

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

    /**
     * Формирует данные по версиям проекта
     * @param int $project_id Идентификатор проекта
     * @param string $key Ключ скачивания
     * @return array
     * @since 1.0.0
     */
    public static function _prepareVersions(int $project_id, string $key): array
    {
        $versions = self::_getVersionsList($project_id);
        foreach ($versions as $i => $version) {
            $version->download_link = JUri::base() . SWJProjectsHelperRoute::getDownloadRoute(
                    $version->id,
                    $version->project_id,
                    'paid_project',
                    $key
                );
        }
        return $versions;
    }

    /**
     * Получает список версий продукта
     * @param int $project_id
     * @return mixed
     * @since 1.0.0
     */
    private static function _getVersionsList(int $project_id)
    {
        /** @var  SWJProjectsModelVersions $model */
        $model = BaseDatabaseModel::getInstance('Versions', 'SWJProjectsModel', ['ignore_request' => true]);
        $model->setState('project.id', $project_id);
        return $model->getItems();
    }

}