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
<div id="SWJProjects" class="document">
    <?php echo LayoutHelper::render('components.swjprojects.block.title', array("project"=>$this->project,"category"=>$this->category,"title" =>Text::_('COM_SWJPROJECTS_DOCUMENTATION'),"mb"=>3)); ?>
	<div class="row mb-3">
        <?php echo LayoutHelper::render('components.swjprojects.block.project', array("payment" => $this->item->payment,"project"=>$this->project,"version"=>$this->project->version,"md"=>3)); ?>
		<div class="col-md-9">
            <?php echo LayoutHelper::render('components.swjprojects.block.buttons', array("project"=>$this->project,"mb"=>3)); ?>
			<div class="cart">
				<div class="cart-body">
					<h1 class="h2">
						<?php echo $this->item->title; ?>
					</h1>
                    <?php if(empty($this->item->alterDocument)): ?>
					<?php if (!empty($this->item->introtext)): ?>
						<p><?php echo nl2br($this->item->introtext); ?></p>
					<?php endif; ?>
					<?php if (!empty($this->item->fulltext)): ?>
						<div><?php echo $this->item->fulltext; ?></div>
					<?php endif; ?>
                    <?php else: ?>
                        <?php echo $this->item->alterDocument; ?>
                    <?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>