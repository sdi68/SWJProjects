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
<div id="SWJProjects" class="documentation">
    <?php echo LayoutHelper::render('components.swjprojects.block.title', array("project"=>$this->project,"category"=>$this->category,"title" =>Text::_('COM_SWJPROJECTS_DOCUMENTATION'),"mb"=>3)); ?>
	<div class="row mb-3">
        <?php echo LayoutHelper::render('components.swjprojects.block.project', array("payment" => $this->project->payment,"project"=>$this->project,"version"=>$this->project->version,"md"=>3)); ?>
		<div class="col-md-9">
            <?php echo LayoutHelper::render('components.swjprojects.block.buttons', array("project"=>$this->project,"mb"=>3)); ?>
			<?php if (empty($this->items)) : ?>
				<div class="alert alert-info">
					<span class="icon-info-circle" aria-hidden="true"></span><span
							class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
					<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
				</div>
			<?php else : ?>
				<div class="documentationList">
					<div class="items">
						<?php foreach ($this->items as $item) : ?>
							<div class="item-<?php echo $item->id; ?> card mb-3">
								<div class="card-body">
									<h5 class="card-title">
										<a href="<?php echo $item->link; ?>"><?php echo $item->title; ?></a>
									</h5>
									<?php if (!empty($item->introtext)): ?>
										<p><?php echo nl2br($item->introtext); ?></p>
									<?php endif; ?>
									<div class="text-end">
										<a href="<?php echo $item->link; ?>"
										   class="btn btn-outline-primary btn-sm ms-1 mb-1">
											<?php echo Text::_('COM_SWJPROJECTS_MORE'); ?>
										</a>
									</div>
								</div>
							</div>
						<?php endforeach ?>
					</div>
					<div class="pagination">
						<?php echo $this->pagination->getPagesLinks(); ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>