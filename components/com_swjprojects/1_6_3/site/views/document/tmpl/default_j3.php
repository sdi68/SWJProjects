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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::stylesheet('com_swjprojects/site.min.css', array('version' => 'auto', 'relative' => true));
?>
<div id="SWJProjects" class="document">
<?php if(empty($this->item->alterDocument)): ?>
	<?php if ($cover = $this->project->images->get('cover')): ?>
		<p class="cover"><?php echo HTMLHelper::image($cover, $this->project->title); ?></p>
		<hr>
	<?php endif; ?>
	<h1><?php echo $this->item->title; ?></h1>
	<div>
		<?php if (!empty($this->item->fulltext)): ?>
			<?php echo $this->item->fulltext; ?>
		<?php elseif (!empty($this->item->introtext)): ?>
			<p><?php echo nl2br($this->item->introtext); ?></p>
		<?php endif; ?>
	</div>
<?php else: ?>
    <div class="h1"><?php echo $this->project->title . ' - ' . Text::_('COM_SWJPROJECTS_DOCUMENTATION'); ?></div>
	<?php echo $this->item->alterDocument; ?>
<?php endif; ?>
</div>