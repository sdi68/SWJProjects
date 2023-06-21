<?php
/*
 * @package    SWJProjects Component
 * @subpackage    com_swjprojects
 * @version    2.0.1
 * @author Econsult Lab.
 * @based on   SW JProjects Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2023 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('list');

/**
 * @package     SWJProjects
 *
 * @since       2.0.1
 */
class JFormFieldProjects extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $type = 'projects';

	/**
	 * Field options array.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $_options = null;

	/**
	 * Вывод только базовых компонентов.
	 *
	 * @var  string
	 *
	 * @since 2.0.1
	 */
	protected $only_visible = 'true';

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   2.0.1
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->only_visible = (string) $this->element['only_visible'];
		}

		return $return;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	protected function getOptions()
	{
		if ($this->_options === null)
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select(array('p.id', 'p.element', 'p.catid'))
				->from($db->quoteName('#__swjprojects_projects', 'p'));

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
			$query->order($db->escape('p.element') . ' ' . $db->escape('asc'));
			$query->order($db->escape('p.ordering') . ' ' . $db->escape('asc'));

			// Выводим только базовые компоненты (те у которых только сервер обновлений - скрываем)
			if ($this->only_visible == 'true')
				$query->where('p.params NOT LIKE "%\\"only_update\\":\\"1\\"%"');

			$items = $db->setQuery($query)->loadObjectList('id');

			// Check admin type view
			$app       = Factory::getApplication();
			$component = $app->input->get('option', 'com_swjprojects');
			$view      = $app->input->get('view', 'project');
			$id        = $app->input->getInt('id', 0);
			$sameView  = ($app->isClient('administrator') && $component == 'com_swjprojects' && $view == 'project');

			// Prepare options
			$options = parent::getOptions();
			foreach ($items as $i => $item)
			{
				$option          = new stdClass();
				$option->value   = $item->id;
				$option->text    = ((!empty($item->title)) ? $item->title : $item->element) . ' (' . $this->getCategoryPath($item->catid) . ')';
				$option->disable = ($sameView && $item->id == $id);

				$options[] = $option;
			}

			$this->_options = $options;
		}

		return $this->_options;
	}

	/**
	 * Формирует иерархию категорий проекта с разделителем /
	 *
	 * @param   int  $category_id  Идентификатор категории проекта
	 *
	 * @return string
	 *
	 * @since 2.0.1
	 */
	private function getCategoryPath(int $category_id): string
	{
		JLoader::register('SWJProjectsHelperTranslation',
			JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/translation.php');
		$translate = SWJProjectsHelperTranslation::getDefault();

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('parent_id'))
			->from($db->quoteName('#__swjprojects_categories', 'c'))
			->where($db->quoteName('c.id') . '=' . $db->quote($category_id));

		$query->select($db->quoteName('t_c.title', 'title'))
			->leftJoin($db->quoteName('#__swjprojects_translate_categories', 't_c')
				. ' ON t_c.id = c.id AND ' . $db->quoteName('t_c.language') . ' = ' . $db->quote($translate));

		$result = $db->setQuery($query)->loadAssoc();
		$path   = $result['title'];
		if ($result['parent_id'])
		{
			$path = $this->getCategoryPath($result['parent_id']) . '/' . $path;
		}
		if (str_starts_with($path, '/'))
		{
			$path = substr($path, 1, strlen($path) - 1);
		}

		return $path;
	}

}