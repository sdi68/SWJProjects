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
 * @var  stdClass $project Project.
 * @var  int      $mb      Column count for block (default 3).
 *
 */
?>
<div class="mb-<?php echo $mb ?? 3; ?>">
    <a href="<?php echo $project->link; ?>"
       class="btn btn-outline-primary btn-sm ms-1 mb-1">
		<?php echo Text::_('COM_SWJPROJECTS_PROJECT'); ?>
    </a>
    <a href="<?php echo $project->versions; ?>"
       class="btn btn-outline-primary btn-sm ms-1 mb-1">
		<?php echo Text::_('COM_SWJPROJECTS_VERSIONS'); ?>
    </a>
	<?php if ($project->documentation): ?>
        <a href="<?php echo $project->documentation; ?>"
           class="btn btn-outline-primary btn-sm ms-1 mb-1">
			<?php echo Text::_('COM_SWJPROJECTS_DOCUMENTATION'); ?>
        </a>
	<?php endif; ?>
	<?php if ($urls = $project->urls->toArray()): ?>
		<?php foreach ($urls as $txt => $url):
			if (empty($url)) continue; ?>
            <a href="<?php echo $url; ?>" target="_blank"
               class="btn btn-outline-primary btn-sm ms-1 mb-1">
				<?php echo Text::_('COM_SWJPROJECTS_URLS_' . $txt); ?>
            </a>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
