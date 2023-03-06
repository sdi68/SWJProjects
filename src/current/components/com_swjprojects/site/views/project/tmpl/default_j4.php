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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::script('com_swjprojects/popup.min.js', array('version' => 'auto', 'relative' => true));
HTMLHelper::stylesheet('com_swjprojects/site.min.css', array('version' => 'auto', 'relative' => true));
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('bootstrap.modal');
?>
<div id="SWJProjects" class="project">
	<?php echo LayoutHelper::render('components.swjprojects.block.title', array("project" => $this->project, "category" => $this->category, "title" => "", "mb" => 3)); ?>
    <div class="row mb-3">
		<?php echo LayoutHelper::render('components.swjprojects.block.project', array("payment" => $this->project->payment, "project" => $this->project, "version" => $this->version, "md" => 3)); ?>
        <div class="col-md-9">
			<?php echo LayoutHelper::render('components.swjprojects.block.buttons', array("project" => $this->project, "mb" => 3)); ?>
            <div class="card mb-3">
                <div class="card-body">
					<?php if (!empty($this->project->introtext)): ?>
                        <p class="description">
							<?php echo $this->project->introtext; ?>
                        </p>
					<?php endif; ?>
					<?php if (!empty($this->project->fulltext)): ?>
                        <div class="fulltext">
							<?php echo $this->project->fulltext; ?>
                        </div>
					<?php endif; ?>
                </div>
            </div>
			<?php echo HTMLHelper::_('uitab.startTabSet', 'projectTab', array('active' => 'whats_new', 'class')); ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'projectTab', 'whats_new', Text::_('COM_SWJPROJECTS_WHATS_NEW')); ?>
			<?php if ($this->version && !empty($this->version->changelog)): ?>
				<?php foreach ($this->version->changelog as $item):
					if (empty($item['title']) && empty($item['description'])) continue;
					?>
                    <div class="item">
						<?php if (!empty($item['title'])): ?>
                            <h3><?php echo $item['title']; ?></h3>
						<?php endif; ?>
						<?php if (!empty($item['description'])): ?>
                            <div class="description"><?php echo $item['description']; ?></div>
						<?php endif; ?>
                    </div>
                    <hr>
				<?php endforeach; ?>
                <div class="text-right small muted">
					<?php echo HTMLHelper::_('date', $this->version->date, Text::_('DATE_FORMAT_LC6')); ?>
                </div>
			<?php endif; ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<?php if ($this->project->joomla):
				$type = $this->project->joomla->get('type'); ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'projectTab', 'joomla', Text::_('COM_SWJPROJECTS_JOOMLA')); ?>
                <ul class="list-unstyled">
                    <li>
                        <strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_TYPE'); ?>: </strong>
						<?php echo Text::_('COM_SWJPROJECTS_JOOMLA_TYPE_' . $type); ?>
                    </li>
					<?php if ($type === 'plugin'): ?>
                        <li>
                            <strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_FOLDER'); ?>: </strong>
							<?php echo utf8_ucfirst($this->project->joomla->get('folder')); ?>
                        </li>
					<?php endif; ?>
					<?php if ($type === 'template' || $type === 'module'): ?>
                        <li>
                            <strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_LOCATION'); ?>: </strong>
							<?php echo ($this->project->joomla->get('client_id')) ?
								Text::_('COM_SWJPROJECTS_JOOMLA_LOCATION_ADMINISTRATOR')
								: Text::_('COM_SWJPROJECTS_JOOMLA_LOCATION_SITE') ?>
                        </li>
					<?php endif; ?>
					<?php if ($type === 'package' && !empty($this->project->joomla->get('package_composition'))): ?>
                        <li>
                            <strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_PACKAGE_COMPOSITION'); ?>: </strong>
							<?php
							$compositions = array();
							foreach ($this->project->joomla->get('package_composition') as $composition)
							{
								$compositions[] = Text::_('COM_SWJPROJECTS_JOOMLA_TYPE_' . $composition);
							}
							echo implode(', ', $compositions); ?>
                        </li>
					<?php endif; ?>
					<?php if ($this->project->joomla->get('version')): ?>
                        <li>
                            <strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_VERSION'); ?>: </strong>
							<?php echo implode(', ', $this->project->joomla->get('version')); ?>
                        </li>
					<?php endif; ?>
                </ul>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<?php endif; ?>
			<?php if ($this->project->gallery): ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'projectTab', 'gallery', Text::_('COM_SWJPROJECTS_IMAGES_GALLERY')); ?>
				<?php foreach (array_chunk($this->project->gallery, 2) as $r => $row): ?>
                    <div class="row">
						<?php foreach ($row as $image): ?>
                            <div class="col-md-6 mb-3">
                                <a href="<?php echo $image->src; ?>" data-popup target="_blank">
									<?php echo HTMLHelper::image($image->src, htmlspecialchars($image->text)); ?>
									<?php if ($image->text): ?>
                                        <div class="lead"><?php echo $image->text; ?></div>
									<?php endif; ?>
                                </a>
                            </div>
						<?php endforeach; ?>
                    </div>
				<?php endforeach; ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<?php endif; ?>

			<?php if (!empty($this->relations)): ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'projectTab', 'relations', Text::_('COM_SWJPROJECTS_RELATIONS')); ?>
				<?php foreach (array_chunk($this->relations, 2) as $r => $row): ?>
                    <div class="row">
						<?php foreach ($row as $relation): ?>
                            <div class="col-md-6 mb-3">
                                <a href="<?php echo $relation['link']; ?>" target="_blank">
                                    <div class="h5"><?php echo $relation['title']; ?></div>
									<?php echo HTMLHelper::image($relation['icon'], htmlspecialchars($relation['title'])); ?>
                                </a>
                            </div>
						<?php endforeach; ?>
                    </div>
				<?php endforeach; ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<?php endif; ?>
			<?php
			// Выводим дополнительные данные проекта
			if (($this->project->download_type === 'paid') && !empty($this->project->extra))
			{
				echo $this->project->extra;
			}
			?>
			<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
        </div>
    </div>
</div>