<?php
/*
 * @package    SW JProjects Payment
 * @subpackage module mod_swjprojects_favorites_projects
 * @version    1.0.0
 * @author     Econsult lab - https://econsultlab.ru
 * @copyright  Copyright (c) 2022 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 *  @link       https://econsultlab.ru/
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Version;

defined('_JEXEC') or die;

/**
 * The Module helper file
 * @package pkg_swjprojects_payments
 * @subpackage  mod_swjprojects_favorites_projects
 * @version 1.0.0
 * @since 1.0.0
 *
 */
class modSwjProjectsFavoritesProjectsHelper
{

    /**
     * Обработчик обращений по ajax
     * @return array|false[]
     * @throws Exception
     * @since 1.0.0
     */
    public static function getAjax()
    {
        $input = Factory::getApplication()->getInput();
        $action = $input->get('action', '');
        $module_id = $input->get('module_id', null);
        $params = self::_getModuleParams($module_id);
        switch ($action) {
            case 'update_p_list':
                $catid = $input->get('catid', '');
                $db = Factory::getDbo();
                $query = $db->getQuery(true)
                    ->select(array('p.id', 'p.element'))
                    ->from($db->quoteName('#__swjprojects_projects', 'p'));

                if (is_numeric($catid)) {
                    $query->where($db->quoteName('catid') . '=' . $db->quote($catid));
                }

                // Join over translates
                JLoader::register('SWJProjectsHelperTranslation',
                    JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/translation.php');
                $translate = SWJProjectsHelperTranslation::getDefault();
                $query->select(array('t_p.title as title'))
                    ->leftJoin($db->quoteName('#__swjprojects_translate_projects', 't_p')
                        . ' ON t_p.id = p.id AND ' . $db->quoteName('t_p.language') . ' = ' . $db->quote($translate));

                // Group by
                $query->group(array('p.id'));

                // Add the list ordering clause
                $query->order($db->escape('p.ordering') . ' ' . $db->escape('asc'));

                $items = $db->setQuery($query)->loadAssocList('id');

                $options = HTMLHelper::_('select.options', $items, 'id', 'title', $params->projects);

                return array('action' => $action, 'options' => $options);
            case 'getJoomlaVersion':
                return array('action' => $action, 'version_suffix' => self::_getJoomlaVersionSuffix());
            default:
                return array(false);
        }
        return array(false);
    }

    /**
     * Получает параметры модуля
     * @param string $id Идентификатор модуля
     * @return mixed
     * @since 1.0.0
     */
    private static function _getModuleParams($id)
    {
        jimport('joomla.application.module.helper'); // подключаем нужный класс, один раз на странице, перед первым выводом
        $module = JModuleHelper::getModuleById($id);
        return json_decode($module->params, false); // декодирует JSON с параметрами модуля
    }

    /**
     * Получаем версию Joomla
     * @return string
     * @since 1.0.0
     */
    private static function _getJoomlaVersionSuffix(): string
    {
        return ((new Version())->isCompatible('4.0')) ? '4' : '3';
    }
}