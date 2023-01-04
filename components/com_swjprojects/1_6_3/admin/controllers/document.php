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

use Joomla\CMS\MVC\Controller\FormController;

class SWJProjectsControllerDocument extends FormController
{
	/**
	 * The URL view list variable.
	 *
	 * @var  string
	 *
	 * @since  1.4.0
	 */
	protected $view_list = 'documentation';

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var  string
	 *
	 * @since  1.4.0
	 */
	protected $text_prefix = 'COM_SWJPROJECTS_DOCUMENT';
}