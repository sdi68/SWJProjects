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
HTMLHelper::script('com_swjprojects/site.min.js', array('version' => 'auto', 'relative' => true));
?>
<div id="SWJProjects" class="version">
    <?php echo LayoutHelper::render('components.swjprojects.block.title', array("project"=>$this->project,"category"=>$this->category,"title" =>mb_strtolower(Text::_('COM_SWJPROJECTS_VERSION')).' '.$this->version->version->version,"mb"=>3)); ?>
	<div class="row mb-3">
        <?php echo LayoutHelper::render('components.swjprojects.block.project', array("payment" => $this->version->payment,"project"=>$this->project,"version" => $this->version, "md"=>3)); ?>
		<div class="col-md-9">
            <?php echo LayoutHelper::render('components.swjprojects.block.buttons', array("project"=>$this->project,"mb"=>3)); ?>
			<div class="card">
				<div class="changelog card-body">
					<h2 class="h3"><?php echo Text::_('COM_SWJPROJECTS_VERSION_CHANGELOG') . ': '; ?></h2>
					<div class="items">
						<?php
						$i = 0;
						foreach ($this->version->changelog as $item):
							if (empty($item['title']) && empty($item['description'])) continue;
							if ($i > 0) echo '<hr>';
							$i++;
							?>
							<div class="item">
								<?php if (!empty($item['title'])): ?>
									<div class="h5"><?php echo $item['title']; ?></div>
								<?php endif; ?>
								<?php if (!empty($item['description'])): ?>
									<div class="description"><?php echo $item['description']; ?></div>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>