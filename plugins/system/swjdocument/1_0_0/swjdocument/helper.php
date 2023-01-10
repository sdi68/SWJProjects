<?php
/*
 * @package    SW JProjects Component
 * @subpackage    system/SWJDocument plugin
 * @version    1.0.0
 * @author Econsult Lab.
 * @copyright  Copyright (c) 2023 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @package     SWJDocument
 *
 * @since       1.0.0
 */
class SWJDocumentHelper
{
	/**
	 * Сохраняет pdf файл
	 *
	 * @param   Array  $data  Данные файла PDF
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function savePDF(array $data): bool
	{
		if (self::getPDF($data['doc_id']))
		{
			return self::updatePDF($data);
		}
		else
		{

			$db      = Factory::getDbo();
			$columns = 'doc_id,pdf_create_date,pdf';
			$values  = $data['doc_id'] . ',"' . self::_getEmptyDateFormat($data['pdf_create_date']) . '","' . addslashes($data['pdf']) . '"';

			$query = $db->getQuery(true)
				->insert($db->quoteName('#__swjprojects_pdf'))
				->columns($columns)
				->values($values);
			$db->setQuery($query);

			return $db->execute();
		}
	}

	/**
	 * Обновляет данные о pdf файле
	 *
	 * @param   array  $data  Данные о файле
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function updatePDF(array $data): bool
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->update($db->quoteName('#__swjprojects_pdf'))
			->set($db->quoteName('pdf') . ' = ' . $db->quote($data['pdf']))
			->set($db->quoteName('pdf_create_date') . ' = ' . $db->quote(self::_getEmptyDateFormat($data['pdf_create_date'])))
			->where($db->quoteName('doc_id') . ' = ' . $db->quote($data['doc_id']));
		$db->setQuery($query);

		return $db->execute();
	}

	/**
	 * Удаляет pdf файл
	 *
	 * @param   int  $doc_id  Идентификатор документа
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	public static function deletePDF(int $doc_id)
	{
		// Удаляем файл
		$pdf = self::getPDF($doc_id);
		File::delete($pdf);
		$dir = dirname($pdf);
		Folder::delete($dir);

		// Удаляем из БД
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__swjprojects_pdf'))
			->where($db->quoteName('doc_id') . '=' . $db->quote($doc_id));

		return $db->setQuery($query)->execute();
	}

	/**
	 * Получает pdf файл
	 *
	 * @param   int  $doc_id  Идентификатор документа
	 *
	 * @return false|mixed|null
	 *
	 * @since 1.0.0
	 */
	public static function getPDF(int $doc_id): mixed
	{
		if ($doc_id)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('pdf'))
				->from($db->quoteName('#__swjprojects_pdf'))
				->where($db->quoteName('doc_id') . ' = ' . $db->quote($doc_id));
			$db->setQuery($query);
			$ret = $db->loadResult();

			return $ret;
		}

		return false;
	}

	/**
	 * Сохраняет pdf файл на сервере
	 *
	 * @param   Registry  $params  Параметры компонента
	 * @param   string    $doc_id  Идентификатор документа
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public static function savePDFFile(Registry $params, string $doc_id): string
	{
		// Check file folder
		$path = $params->get('files_folder', '') . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . $doc_id;
		if (!Folder::exists($path))
		{
			Folder::create($path);
		}
		$new_file = $_FILES['jform'] ?? '';
		// Remove old files
		$files = Folder::files($path, '', false, true);
		if ((!empty($new_file['tmp_name']['pdf'])) && !empty($files))
		{
			foreach ($files as $file)
			{
				File::delete($file);
			}
		}

		// Upload new file
		if (!empty($new_file['tmp_name']['pdf']))
		{
			$dest = $path . DIRECTORY_SEPARATOR . $new_file['name']['pdf'];
			if (File::upload($new_file['tmp_name']['pdf'], $dest, false, true))
				return $dest;
		}

		return '';
	}

	/**
	 * Формирует пустое значение для даты в sql запросы
	 *
	 * @param $date
	 *
	 * @return mixed|string
	 * @since 1.0.0
	 */
	private static function _getEmptyDateFormat($date): mixed
	{
		if (empty($date))
		{
			return Factory::getDbo()->getNullDate();
		}

		return $date;
	}

}

