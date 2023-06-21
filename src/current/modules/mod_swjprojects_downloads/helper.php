<?php
/*
 * @package    SWJProjects Component
 * @subpackage    mod_swjprojects_downloads
 * @version    2.0.1
 * @author Econsult Lab.
 * @based on   SW JProjects Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2023 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

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
 * The module mod_swjprojects_downloads helper file
 * @package     pkg_swjprojects_payments
 * @subpackage  mod_swjprojects_downloads
 * @version     1.0.0
 * @since       1.0.0
 */
class modSwjProjectsDownloadsHelper
{
	/**
	 * Обработчик обращений по ajax
	 * @return array|false[]
	 * @throws Exception
	 * @since 1.0.0
	 */
	public static function getAjax()
	{
		$input     = Factory::getApplication()->getInput();
		$action    = $input->get('action', '');
		$module_id = $input->get('module_id', null);
		$lang      = JFactory::getLanguage();
		$base_dir  = JPATH_ROOT;
		// язык компонента
		$extension = 'com_swjprojects';
		$lang->load($extension, $base_dir);
		switch ($action)
		{
			case 'update_downloads_row':
				$html         = '';
				$order_number = $input->get('order_number', null);
				$row          = $input->get('row', null);
				if ($order_number)
				{
					$order = SWJPaymentOrderHelper::getOrderByOrderNumber($order_number);
					$tmp   = $order->projects;
					foreach ($tmp as $i => $id)
					{
						$order->projects = self::_getProject($id);
					}
					$item   = self::_getDownloadListItem($order);
					$module = self::_getModule($module_id);
					$params = new Registry($module->params);
					ob_start();
					include ModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default') . '-row');
					$html = ob_get_contents();
					ob_end_clean();
				}

				return array('action' => $action, 'html' => $html);
			default:
				return array(false);
		}

		return array(false);
	}

	/**
	 * Получает модуль по id.
	 * Получаем непосредственно из БД. Штатный метод не работает.
	 *
	 * @param   string  $id  Идентификатор модуля
	 *
	 * @return mixed|null
	 * @since 1.0.0
	 */
	private static function _getModule($id)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__modules'))
			->where($db->quoteName('id') . '=' . $db->quote($id));
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Получает проект
	 *
	 * @param   string  $id  Идентификатор проекта
	 *
	 * @return mixed
	 * @throws Exception
	 * @since 1.0.0
	 */
	private static function _getProject($id)
	{
		/** @var  SWJProjectsModelKey $model */
		$model = BaseDatabaseModel::getInstance('Project', 'SWJProjectsModel', ['ignore_request' => true]);
		$model->setState('params', Factory::getApplication()->getParams());

		return $model->getItem($id);
	}

	/**
	 * Формирует данные по версиям проекта
	 *
	 * @param   int     $project_id  Идентификатор проекта
	 * @param   string  $key         Ключ скачивания
	 *
	 * @return array
	 * @since 1.0.0
	 */
	private static function _prepareVersions(int $project_id, string $key): array
	{
		$versions = self::_getVersionsList($project_id);
		foreach ($versions as $i => $version)
		{
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
	 * Формирует список доступных для скачивания Пользователем продуктов
	 * @return array
	 * @throws Exception
	 * @since 1.0.0
	 */
	public static function getDownloadList(): array
	{
		$user = Factory::getApplication()->getIdentity();
		if ($user->id)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__swjprojects_keys'))
				->where($db->quoteName('user') . '=' . $db->quote($user->id));
			$db->setQuery($query);
			$ids   = $db->loadColumn();
			$items = array();
			if ($ids)
			{
				foreach ($ids as $id)
				{
					$order = SWJPaymentOrderHelper::getOrderById($id);
					$tmp   = $order->projects;
					foreach ($tmp as $i => $project_id)
					{
						$order_clone           = clone $order;
						$order_clone->projects = self::_getProject($project_id);

						/**
						 * Выводим только базовые или самостоятельные проекты
						 * @since 2.0.1
						 */
						$params       = new Registry($order_clone->projects->params);
						$base_project = $params->get('base_project', '');
						if ($base_project)
							continue;
						$items[] = self::_getDownloadListItem($order_clone);
					}
				}
			}
		}
		else
		{
			$items['error'] = new Exception(Text::_('MOD_SWJPROJECTS_DOWNLOADS_USER_NOT_LOGGED_ON'));
		}

		return $items;
	}

	/**
	 * Формирует элемент продукта для скачивания пользователя
	 *
	 * @param   mixed  $order
	 *
	 * @return array|null
	 * @since 1.0.0
	 */
	private static function _getDownloadListItem(mixed $order): ?array
	{
		if ($order)
		{
			if (is_object($order->extra))
			{
				$extra = $order->extra;
				if (!is_null($extra->payment_status) && SWJPaymentStatuses::valueExists($extra->payment_status))
				{
					$order->extra->payment_status_title = SWJPaymentStatuses::getEnumNameText($extra->payment_status);
				}
				else
				{
					$order->extra->payment_status_title = Text::_('MOD_SWJPROJECTS_DOWNLOADS_ERROR_ORDER_PAYMENT');
				}
			}
			else
			{
				$order->extra                       = new stdClass();
				$order->extra->payment_status       = SWJPaymentStatuses::SWJPAYMENT_STATUS_FREE;
				$order->extra->payment_status_title = Text::_('COM_SWJPROJECTS_DOWNLOAD_TYPE_FREE');
			}
			$item['key']      = $order;
			$item['versions'] = self::_prepareVersions($order->projects->id, $order->key);

			return $item;
		}

		return null;
	}

	/**
	 * Получает список версий продукта
	 *
	 * @param   int  $project_id
	 *
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