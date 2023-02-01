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

JLoader::register('SWJProjectsHelperKeys', JPATH_ADMINISTRATOR . '/components/com_swjprojects/helpers/keys.php');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormField;
use Joomla\Registry\Registry;

class JFormFieldKey extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since  1.3.0
	 */
	protected $type = 'key';

	/**
	 * Name of the layout being used to render the field.
	 *
	 * @var  string
	 *
	 * @since  1.3.0
	 */
	protected $layout = 'components.swjprojects.field.key';

	/**
	 * Key length.
	 *
	 * @var  string
	 *
	 * @since  1.3.0
	 */
	protected $length = null;

	/**
	 * Key characters.
	 *
	 * @var  string
	 *
	 * @since  1.3.0
	 */
	protected $characters = null;

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  1.3.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if ($return = parent::setup($element, $value, $group))
		{
			$this->length     = (!empty((int) $this->element['length'])) ? $this->element['length']
				: ComponentHelper::getParams('com_swjprojects')->get('key_length');
			$this->characters = (!empty($this->element['characters'])) ? (string) $this->element['characters']
				: implode(',', SWJProjectsHelperKeys::getCharacters());
		}

		return $return;
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array Layout data array.
	 *
	 * @since  1.3.0
	 */
	protected function getLayoutData()
	{
		// Prepare characters
		$characters = array_filter(array_map('trim', explode(',', $this->characters)), function ($element) {
			return (!empty($element));
		});
		$characters = new Registry($characters);

		$data               = parent::getLayoutData();
		$data['length']     = (int) $this->length;
		$data['characters'] = $characters->toString();

		return $data;
	}
}