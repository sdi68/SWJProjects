<?php
/*
 * @package    SW JProjects Component
 * @subpackage    com_swjprojects
 * @version    1.6.3
 * @author Econsult Lab.
 * @based on   SW JProjects Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2023 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru
 */

defined('_JEXEC') or die;

JLoader::register('SWJProjectsHelper', __DIR__ . '/helpers/swjprojects.php');
JLoader::register('SWJProjectsHelperImages', __DIR__ . '/helpers/images.php');
JLoader::register('SWJProjectsHelperKeys', __DIR__ . '/helpers/keys.php');
JLoader::register('SWJProjectsHelperTranslation', __DIR__ . '/helpers/translation.php');

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

if (!Factory::getUser()->authorise('core.manage', 'com_swjprojects'))
{
	throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

$controller = BaseController::getInstance('SWJProjects');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();