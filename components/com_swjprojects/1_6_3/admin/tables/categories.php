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

use Joomla\CMS\Table\Nested;

class SWJProjectsTableCategories extends Nested
{
	/**
	 * Cache for the root id.
	 *
	 * @var  integer
	 *
	 * @since  1.0.0
	 */
	protected static $root_id = 1;

	/**
	 * Constructor.
	 *
	 * @param   JDatabaseDriver &$db  Database connector object
	 *
	 * @since  1.0.0
	 */
	function __construct(&$db)
	{
		parent::__construct('#__swjprojects_categories', 'id', $db);

		// Set the alias since the column is called state
		$this->setColumnAlias('published', 'state');
	}
}