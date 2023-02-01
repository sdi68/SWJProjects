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

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

extract($displayData);
/**
 * Layout variables
 * -----------------
 *
 * @var  stdClass $project  Project.
 * @var  stdClass $category Main project category.
 * @var  string   $title    Type of page text.
 * @var  int      $mb       Column count for block (default 3).
 *
 */
?>
<div class="project info mb-<?php echo $mb ?? 3; ?>">
    <h1><?php echo($project->title . ($title ? ' - ' . $title : '')); ?></h1>
    <div>
		<?php if (!empty($project->categories)): ?>
            <strong><?php echo Text::_('COM_SWJPROJECTS_CATEGORIES'); ?>: </strong>
			<?php $i = 0;
			foreach ($project->categories as $category)
			{
				if ($i > 0) echo ', ';
				$i++;
				echo '<a href="' . $category->link . '">' . $category->title . '</a>';
			}
			?>
		<?php else: ?>
            <strong><?php echo Text::_('COM_SWJPROJECTS_CATEGORY'); ?>: </strong>
            <a href="<?php echo $category->link; ?>">
				<?php echo $category->title; ?>
            </a>
		<?php endif; ?>
    </div>
</div>
