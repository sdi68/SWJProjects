<?php
/*
 * @package    SWJProjects Component
 * @subpackage    mod_swjprojects_projects
 * @version    2.0.1
 * @author Econsult Lab.
 * @based on   SW JProjects Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2023 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Registry\Registry;

// Register helpers
JLoader::register('SWJProjectsHelperRoute', JPATH_SITE . '/components/com_swjprojects/helpers/route.php');
JLoader::register('SWJProjectsHelperImages', JPATH_SITE . '/components/com_swjprojects/helpers/images.php');
JLoader::register('SWJProjectsHelperTranslation', JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/translation.php');

// Load language
$language = Factory::getLanguage();
$language->load('com_swjprojects', JPATH_SITE, $language->getTag(), true);

// Prepare model
BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_swjprojects/models');
$model = BaseDatabaseModel::getInstance('Projects', 'SWJProjectsModel', array('ignore_request' => true));
/** @var Registry $params */
$model->setState('category.id', $params->get('category', 1));
$model->setState('params', Factory::getApplication()->getParams());
$model->setState('filter.published', 1);
$model->setState('list.limit', $params->get('limit', 5));
$model->setState('list.start', 0);

$ordering = $params->get('ordering');

if ($ordering === 'rand()')
{
	$model->setState('list.ordering', Factory::getDbo()->getQuery(true)->Rand());
}
else
{
	$direction = $params->get('direction', 1) ? 'DESC' : 'ASC';
	$model->setState('list.direction', $direction);
	$model->setState('list.ordering', $ordering);
}

// Get items
/**
 * Выводим только базовые или самостоятельные проекты
 * @since 2.0.1
 */
$items = array();
foreach ($model->getItems() as $tmp)
{
	$params       = new Registry($tmp->projects->params);
	$base_project = $params->get('base_project', '');
	if ($base_project)
		continue;
	$items[] = $tmp;
}

// Show module
require ModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));
