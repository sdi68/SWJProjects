<?php
/*
 * @package    SW JProjects Payment
 * @subpackage module mod_swjprojects_downloads
 * @version    1.0.0
 * @author     Econsult lab - https://econsultlab.ru
 * @copyright  Copyright (c) 2022 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru/
 */

/**
 * The module mod_swjprojects_downloads file
 * @package pkg_swjprojects_payments
 * @subpackage  mod_swjprojects_downloads
 * @version 1.0.0
 * @since 1.0.0
 *
 * Variable definitions
 * @var Joomla\Registry\ $params
 * @var ModuleHelper $module
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Version;

require_once __DIR__ . '/helper.php';

// Register helpers
JLoader::register('SWJProjectsHelperRoute', JPATH_SITE . '/components/com_swjprojects/helpers/route.php');
JLoader::register('SWJProjectsHelperImages', JPATH_SITE . '/components/com_swjprojects/helpers/images.php');
JLoader::register('SWJProjectsHelperTranslation', JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/translation.php');

// Load language
$language = Factory::getLanguage();
$language->load('com_swjprojects', JPATH_SITE, $language->getTag(), true);

if ((new Version())->isCompatible('4.0')) {
    /** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
    $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
    $wr = $wa->getRegistry();
    $wr->addRegistryFile('/media/mod_swjprojects_downloads/joomla.assets.json');
    $wa->useScript('mod_swjprojects_downloads.front');
    $wa->useStyle('mod_swjprojects_downloads.front');
    $wa->useScript('bootstrap.modal');

} else {
    $doc = JFactory::getDocument();
    $doc->addScript('/media/mod_swjprojects_downloads/js/front.js');
    $doc->addStyleSheet('/media/mod_swjprojects_downloads/css/front.css');
    HTMLHelper::_('bootstrap.modal');
}
// Инициализируем текстовые переменные для js скрипта
Text::script('MOD_SWJPROJECTS_DOWNLOADS_UPDATE_ORDER_MODAL_HEADER');
Text::script('MOD_SWJPROJECTS_DOWNLOADS_UPDATE_ORDER_MODAL_BODY');
Text::script('MOD_SWJPROJECTS_DOWNLOADS_DELETE_ORDER_MODAL_BODY');

$list = modSwjProjectsDownloadsHelper::getDownloadList();

// Show module
require ModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));
