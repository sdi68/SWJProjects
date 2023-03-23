<?php
/*
 * @package    SW JProjects Component
 * @version    1.6.3
 * @author Econsult Lab.
 * @based on   SW JProjects Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2023 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Version;


if (!class_exists('pkg_swjprojectsInstallerScript'))
{
	class pkg_swjprojectsInstallerScript
	{
		/**
		 * Minimum PHP version required to install the extension.
		 *
		 * @var  string
		 *
		 * @since  1.0.0
		 */
		protected $minimumPhp = '7.0';

		/**
		 * Minimum Joomla version required to install the extension.
		 *
		 * @var  string
		 *
		 * @since  1.0.0
		 */
		protected $minimumJoomla = '3.9.0';

		/**
		 * Runs right before any installation action.
		 *
		 * @param   string            $type    Type of PostFlight action.
		 * @param   InstallerAdapter  $parent  Parent object calling object.
		 *
		 * @return  boolean True on success, false on failure.
		 *
		 * @throws  Exception
		 *
		 * @since  1.0.0
		 */
		function preflight($type, $parent)
		{
			// Check compatible
			if (!$this->checkCompatible()) return false;

			// Check update server
			if ($type == 'update')
			{
				$this->checkUpdateServer();
			}

			// Массив расширений, необходимых к установке
			$dependecies[] = array(
				'type'    => 'package',  // Тип расширения
				'element' => 'pkg_eclabs', // Название расширения, как оно зафиксировано #__extensions.element
				'url'     => "https://econsultlab.ru/uploads/joomla/packages/eclabs/update_eclabs.xml" // URL сервера обновлений расширения
			);
			foreach ($dependecies as $dependecy)
			{
				$info = $this->getDependency($dependecy);
				//var_export($info);
				if ($info)
				{
					if ($this->checkInstalled($info))
					{
						$this->installDependency($parent, $info['downloads']['downloadurl']);
					}
				}
			}

			return true;
		}

		/**
		 * Method to check compatible.
		 *
		 * @return  boolean True on success, false on failure.
		 *
		 * @throws  Exception
		 *
		 * @since  1.2.0
		 */
		protected function checkCompatible()
		{
			// Check old joomla
			if (!class_exists('Joomla\CMS\Version'))
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('PKG_SWJPROJECTS_ERROR_COMPATIBLE_JOOMLA',
					$this->minimumJoomla), 'error');

				return false;
			}

			$app      = Factory::getApplication();
			$jversion = new Version();

			// Check php
			if (!(version_compare(PHP_VERSION, $this->minimumPhp) >= 0))
			{
				$app->enqueueMessage(Text::sprintf('PKG_SWJPROJECTS_ERROR_COMPATIBLE_PHP', $this->minimumPhp),
					'error');

				return false;
			}

			// Check joomla version
			if (!$jversion->isCompatible($this->minimumJoomla))
			{
				$app->enqueueMessage(Text::sprintf('PKG_SWJPROJECTS_ERROR_COMPATIBLE_JOOMLA', $this->minimumJoomla),
					'error');

				return false;
			}

			return true;
		}

		/**
		 * Method to check update server and change if need.
		 *
		 * @since  1.2.0
		 */
		protected function checkUpdateServer()
		{
			$old = array(
				'https://www.septdir.com/jupdate?element=pkg_swjprojects',
				'https://www.septdir.com/marketplace/joomla/update?element=pkg_swjprojects'
			);
			$new = 'https://www.septdir.com/solutions/joomla/update?element=pkg_swjprojects';

			$db      = Factory::getDbo();
			$query   = $db->getQuery(true)
				->select(array('update_site_id', 'location'))
				->from($db->quoteName('#__update_sites'))
				->where($db->quoteName('name') . ' = ' . $db->quote('SW JProjects'));
			$current = $db->setQuery($query)->loadObject();

			if (in_array($current->location, $old))
			{
				$current->location = $new;
				$db->updateObject('#__update_sites', $current, array('update_site_id'));
			}
		}

		/**
		 * This method is called when extension is updated.
		 *
		 * @param   InstallerAdapter  $parent  Parent object calling object.
		 *
		 * @since  1.3.0
		 */
		public function update($parent)
		{
			// Unset package id for JLSitemap plugin
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('extension_id')
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
				->where($db->quoteName('folder') . ' = ' . $db->quote('jlsitemap'))
				->where('package_id <>  0');
			if ($plugin = $db->setQuery($query)->loadResult())
			{
				$db->setQuery('UPDATE #__extensions SET package_id = 0 WHERE extension_id = ' . $plugin)->execute();
			}
		}

		/**
		 * Получает информацию о расширении от сервера обновления
		 *
		 * @param   array  $dependency  Массив зависимого зависимости
		 *
		 * @return false|array
		 *
		 * @throws Exception
		 * @since 1.0.0
		 */
		protected function getDependency(array $dependency)
		{
			$app        = Factory::getApplication();
			$version    = new Version;
			$httpOption = new Registry;
			$httpOption->set('userAgent', $version->getUserAgent('Joomla', true, false));

			// JHttp transport throws an exception when there's no response.
			try
			{
				$http     = HttpFactory::getHttp($httpOption);
				$response = $http->get($dependency['url'], array(), 20);
			}
			catch (\RuntimeException $e)
			{
				$response = null;
			}

			if ($response === null || $response->code !== 200)
			{
				$app->enqueueMessage(Text::sprintf('COM_INSTALLER_MSG_ERROR_CANT_CONNECT_TO_UPDATESERVER', $dependency['url']), 'warning');

				return false;
			}

			$xml = new SimpleXMLElement($response->body);
			$ret = json_decode(json_encode($xml), true);

			return $ret['update'] ?? false;
		}


		/**
		 * Устанавливает расширение по URL
		 *
		 * @param           $parent
		 * @param   string  $url  URL сервера обновлений расширения
		 *
		 * @return bool
		 *
		 * @throws Exception
		 * @since 1.0.0
		 */
		protected function installDependency($parent, string $url): bool
		{
			// Load installer plugins for assistance if required:
			PluginHelper::importPlugin('installer');

			$app = Factory::getApplication();

			$package = null;

			// This event allows an input pre-treatment, a custom pre-packing or custom installation.
			// (e.g. from a JSON description).
			$results = $app->triggerEvent('onInstallerBeforeInstallation', array($this, &$package));

			if (in_array(true, $results, true))
			{
				return true;
			}

			if (in_array(false, $results, true))
			{
				return false;
			}

			// Download the package at the URL given.
			$p_file = JInstallerHelper::downloadPackage($url);

			// Was the package downloaded?
			if (!$p_file)
			{
				$app->enqueueMessage(Text::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'), 'error');

				return false;
			}

			$config   = Factory::getConfig();
			$tmp_dest = $config->get('tmp_path');

			// Unpack the downloaded package file.
			$package = JInstallerHelper::unpack($tmp_dest . '/' . $p_file, true);

			// This event allows a custom installation of the package or a customization of the package:
			$results = $app->triggerEvent('onInstallerBeforeInstaller', array($this, &$package));

			if (in_array(true, $results, true))
			{
				return true;
			}

			if (in_array(false, $results, true))
			{
				JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

				return false;
			}

			// Get an installer instance.
			$installer = new Installer();

			/*
			 * Check for a Joomla core package.
			 * To do this we need to set the source path to find the manifest (the same first step as JInstaller::install())
			 *
			 * This must be done before the unpacked check because JInstallerHelper::detectType() returns a boolean false since the manifest
			 * can't be found in the expected location.
			 */
			if (is_array($package) && isset($package['dir']) && is_dir($package['dir']))
			{
				$installer->setPath('source', $package['dir']);

				if (!$installer->findManifest())
				{
					JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
					$app->enqueueMessage(Text::sprintf('COM_INSTALLER_INSTALL_ERROR', '.'), 'warning');

					return false;
				}
			}

			// Was the package unpacked?
			if (!$package || !$package['type'])
			{
				JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
				$app->enqueueMessage(Text::_('COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE'), 'error');

				return false;
			}

			// Install the package.
			if (!$installer->install($package['dir']))
			{
				// There was an error installing the package.
				$msg     = Text::sprintf('COM_INSTALLER_INSTALL_ERROR',
					Text::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
				$result  = false;
				$msgType = 'error';
			}
			else
			{
				// Package installed successfully.
				$msg     = Text::sprintf('COM_INSTALLER_INSTALL_SUCCESS',
					Text::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
				$result  = true;
				$msgType = 'message';
			}

			// This event allows a custom a post-flight:
			$app->triggerEvent('onInstallerAfterInstaller', array($parent, &$package, $installer, &$result, &$msg));

			$app->enqueueMessage($msg, $msgType);

			// Cleanup the install files.
			if (!is_file($package['packagefile']))
			{
				$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
			}

			JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

			return $result;
		}

		/**
		 * Проверяет возможность и необходимость установки расширения
		 *
		 * @param   array  $info  Параметры устанавливаемого расширения
		 *
		 * @return bool
		 *
		 * @since 1.0.0
		 */
		protected function checkInstalled(array $info): bool
		{
			// Получаем информацию об установленном расширении
			$db    = Factory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName('manifest_cache'))
				->from($db->qn('#__extensions'))
				->where($db->quoteName('type') . ' = ' . $db->quote($info['type']))
				->where($db->quoteName('element') . ' = ' . $db->quote($info['element']));
			$params = json_decode($db->setQuery($query)->loadResult(), true);
			//var_dump($params);
			$ret = false;
			if (is_null($params))
			{
				// Расширение не установлено. Надо устанавливать.
				$ret = true;
			}
			else if (is_array($params))
			{
				// Если на сайте более старая версия, то можно устанавливать расширение
				$ret = (bool) version_compare($info['version'], $params['version'], '>');
			}

			return $ret;
		}
	}

}