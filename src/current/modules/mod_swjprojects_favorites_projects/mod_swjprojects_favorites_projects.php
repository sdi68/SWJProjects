<?php
/*
 * @package    SWJProjects Component
 * @subpackage    mod_swjprojects_favorite_projects
 * @version    2.0.1
 * @author Econsult Lab.
 * @based on   SW JProjects Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2023 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru
 */

/**
 *
 * The module mod_swjprojects_favorite_projects file
 * @package     pkg_swjprojects_payments
 * @subpackage  mod_swjprojects_favorite_projects
 * @version     2.0.1
 * @since       1.0.0
 *
 * Variable definitions
 * @var Joomla\Registry\ $params
 * @var ModuleHelper     $module
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

require_once __DIR__ . '/helper.php';

// Register helpers
JLoader::register('SWJProjectsHelperRoute', JPATH_SITE . '/components/com_swjprojects/helpers/route.php');
JLoader::register('SWJProjectsHelperImages', JPATH_SITE . '/components/com_swjprojects/helpers/images.php');
JLoader::register('SWJProjectsHelperTranslation', JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/translation.php');

// Load language
$language = Factory::getLanguage();
$language->load('com_swjprojects', JPATH_SITE, $language->getTag(), true);
$use_cat_filter = $params->get('use_cat_filter', false);

// Prepare model
BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_swjprojects/models');
Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_swjprojects/tables');
$items    = array();
$projects = $params->get('projects', null);
if ($use_cat_filter)
{

	$model = BaseDatabaseModel::getInstance('Projects', 'SWJProjectsModel', array('ignore_request' => true));
	$model->setState('category.id', $params->get('category', 1));
	$model->setState('params', Factory::getApplication()->getParams());
	$model->setState('filter.published', 1);
	$model->setState('list.limit', $params->get('limit', 0));
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
	$tmps = $model->getItems();
	if (is_array($tmps))
	{
		foreach ($tmps as $tmp)
		{
			if (in_array($tmp->id, $projects))
			{
				/**
				 * Выводим только базовые или самостоятельные проекты
				 * @since 2.0.1
				 */
				$params       = new Registry($tmp->projects->params);
				$base_project = $params->get('base_project', '');
				if ($base_project)
					continue;
				$items[] = $tmp;
			}
		}
	}
}
else
{
	$model = BaseDatabaseModel::getInstance('Project', 'SWJProjectsModel', array('ignore_request' => true));
	$model->setState('params', Factory::getApplication()->getParams());
	if (is_array($projects))
	{
		foreach ($projects as $project)
		{
			/**
			 * Выводим только базовые или самостоятельные проекты
			 * @since 2.0.1
			 */
			$tmp          = $model->getItem($project);
			$params       = new Registry($tmp->projects->params);
			$base_project = $params->get('base_project', '');
			if ($base_project)
				continue;
			$items[] = $tmp;
		}
	}
}

// Show module
require ModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));
