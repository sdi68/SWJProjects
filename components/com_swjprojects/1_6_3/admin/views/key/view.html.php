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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Version;

class SWJProjectsViewKey extends HtmlView
{
	/**
	 * Model state variables.
	 *
	 * @var  Joomla\CMS\Object\CMSObject
	 *
	 * @since  1.3.0
	 */
	protected $state;

	/**
	 * Form object.
	 *
	 * @var  Form
	 *
	 * @since  1.3.0
	 */
	protected $form;

	/**
	 * Key object.
	 *
	 * @var  object
	 *
	 * @since  1.3.0
	 */
	protected $item;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @throws  Exception
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since  1.3.0
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode('\n', $errors), 500);
		}

		// Add title and toolbar
		$this->addToolbar();

		// Re-generate field
		if (empty($this->item->id))
		{
			$this->form->removeField('key_regenerate');
		}

		return parent::display($tpl);
	}

	/**
	 * Add title and toolbar.
	 *
	 * @throws  Exception
	 *
	 * @since  1.3.0
	 */
	protected function addToolbar()
	{
		$isNew     = ($this->item->id == 0);
		$canDo     = SWJProjectsHelper::getActions('com_swjprojects', 'key', $this->item->id);
		$toolbar   = Toolbar::getInstance();

		// Disable menu
		Factory::getApplication()->input->set('hidemainmenu', true);

		// Set page title
		$title = ($isNew) ? Text::_('COM_SWJPROJECTS_KEY_ADD') : Text::_('COM_SWJPROJECTS_KEY_EDIT');
		ToolbarHelper::title(Text::_('COM_SWJPROJECTS') . ': ' . $title, 'cube');

		// Add apply & save buttons
		if ($canDo->get('core.edit'))
		{
			ToolbarHelper::apply('key.apply');
			ToolbarHelper::save('key.save');
		}

		// Add save new button
		if ($canDo->get('core.create'))
		{
			ToolbarHelper::save2new('key.save2new');
		}

		// Add cancel button
		ToolbarHelper::cancel('key.cancel', 'JTOOLBAR_CLOSE');

	}
}