<?php
/**
 * @package    SW JProjects Component
 * @version    1.5.5
 * @author     Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2018 - 2020 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  string $class      Classes for the input.
 * @var  string $id         DOM id of the field.
 * @var  string $name       Name of the input field.
 * @var  array  $value      Filed value array.
 * @var  string $characters Key characters.
 * @var  int    $length     Key characters length.
 */

HTMLHelper::script('com_swjprojects/field-key.min.js', array('version' => 'auto', 'relative' => true));
?>
<div class="<?php echo $class; ?>" input-key="container" data-length="<?php echo $length; ?>"
	 data-characters='<?php echo $characters; ?>'>
	<p input-key="success" class="alert alert-success" style="display: none;">
		<?php echo Text::_('COM_SWJPROJECTS_KEY_REGENERATE_SUCCESS'); ?>
	</p>
	<p>
		<a href="#" input-key="show" class="btn btn-danger"><?php echo Text::_('JSHOW'); ?></a>
		<a href="#" input-key="generate" class="btn btn-success">
			<?php echo Text::_('COM_SWJPROJECTS_KEY_REGENERATE'); ?>
		</a>
	</p>
	<code input-key="key" style="display: none;"></code>
	<input type="hidden" input-key="field" value="<?php echo $value; ?>" name="<?php echo $name; ?>">
</div>
