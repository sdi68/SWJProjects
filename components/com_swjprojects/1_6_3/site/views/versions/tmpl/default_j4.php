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
use Joomla\CMS\Layout\LayoutHelper;
HTMLHelper::stylesheet('com_swjprojects/site.min.css', array('version' => 'auto', 'relative' => true));
?>
<div id="SWJProjects" class="versions">
    <?php echo LayoutHelper::render('components.swjprojects.block.title', array("project"=>$this->project,"category"=>$this->category,"title" =>Text::_('COM_SWJPROJECTS_VERSIONS'),"mb"=>3)); ?>
	<div class="row mb-3">
        <?php echo LayoutHelper::render('components.swjprojects.block.project', array("payment" => $this->project->payment,"project"=>$this->project,"version" => $this->project->version, "md"=>3)); ?>
		<div class="col-md-9">
            <?php echo LayoutHelper::render('components.swjprojects.block.buttons', array("project"=>$this->project,"mb"=>3)); ?>
			<?php if (empty($this->items)) : ?>
				<div class="alert alert-info">
					<span class="icon-info-circle" aria-hidden="true"></span><span
							class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
					<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php else : ?>
				<div class="versionsList">
					<div class="items">
						<?php foreach (array_chunk($this->items, 3) as $r => $row): ?>
							<div class="row mb-3">
								<?php foreach ($row as $i => $item): ?>
									<div class="col-md-4">
										<div class="card">
											<div class="card-body">
												<h5 class="card-title">
													<a href="<?php echo $item->link; ?>"><?php echo $item->version->version; ?></a>
												</h5>
												<ul class="list-unstyled">
													<li>
														<strong><?php echo Text::_('JDATE'); ?>: </strong>
														<?php echo HTMLHelper::_('date', $item->date, Text::_('DATE_FORMAT_LC3')); ?>
													</li>
													<li>
														<strong><?php echo Text::_('COM_SWJPROJECTS_VERSION_TAG'); ?>
															: </strong>
														<span class="text-<?php echo ($item->tag->key == 'stable') ? 'success' : 'error'; ?>">
															<?php echo $item->tag->title; ?>
														</span>
													</li>
													<?php if (!empty($item->joomla_version)): ?>
														<li>
															<strong><?php echo Text::_('COM_SWJPROJECTS_JOOMLA_VERSION'); ?>
																: </strong>
															<?php echo $item->joomla_version; ?>
														</li>
													<?php endif; ?>
													<?php if ($item->downloads): ?>
														<li>
															<strong><?php echo Text::_('COM_SWJPROJECTS_STATISTICS_DOWNLOADS'); ?>
																: </strong>
															<?php echo $item->downloads; ?>
														</li>
													<?php endif; ?>
												</ul>
												<?php if ($item->download_type === 'free'): ?>
													<div>
														<a href="<?php echo $item->download; ?>" target="_blank"
														   class="btn col-12 btn-<?php echo ($item->tag->key == 'stable') ? 'success' : 'secondary'; ?> float-end">
															<?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD'); ?>
														</a>
													</div>
												<?php endif; ?>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="pagination">
					<?php echo $this->pagination->getPagesLinks(); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>