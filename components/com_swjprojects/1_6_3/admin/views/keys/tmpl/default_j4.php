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

use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

/**
 * @var array $items
 */

HTMLHelper::stylesheet('com_swjprojects/admin-j4.min.css', array('key' => 'auto', 'relative' => true));

$user      = Factory::getUser();
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

/** @var SWJProjectsModelKeys $keys */
$keys = $this->_models['keys'];

$columns = 9 + count($keys->extra_headers);

?>
<form action="<?php echo Route::_('index.php?option=com_swjprojects&view=keys'); ?>" method="post"
	  name="adminForm" id="adminForm" class="clearfix">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span><span
								class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
					<table id="keysList" class="table itemList">
						<thead>
						<tr>
							<td class="w-1 text-center">
								<?php echo HTMLHelper::_('grid.checkall'); ?>
							</td>
							<th scope="col" class="w-1 text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS',
									'k.state', $listDirn, $listOrder); ?>
							</th>
							<th scope="col">
								<?php echo Text::_('COM_SWJPROJECTS_KEY'); ?>
							</th>
                            <?php foreach ($keys->extra_headers as $th):?>
                            <th scope="col">
                                <?php echo $this->escape($th); ?>
                            </th>
                            <?php endforeach; ?>
							<th scope="col" class="w-10 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_SWJPROJECTS_ORDER',
									'k.order', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell">
								<?php echo Text::_('COM_SWJPROJECTS_USER'); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell">
								<?php echo Text::_('COM_SWJPROJECTS_PROJECTS'); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_SWJPROJECTS_DATE_START',
									'k.date_start', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-10 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_SWJPROJECTS_DATE_END',
									'k.date_end', $listDirn, $listOrder); ?>
							</th>
							<th scope="col" class="w-5 d-none d-md-table-cell">
								<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID',
									'k.id', $listDirn, $listOrder); ?>
							</th>
						</tr>
						</thead>
						<tfoot>
						<tr>
							<td colspan="<?php echo $columns; ?>" class="text-end">
								<?php echo $this->pagination->getResultsCounter(); ?>
							</td>
						</tr>
						</tfoot>
						<tbody>
						<?php foreach ($this->items as $i => $item) :
							$canEdit = $user->authorise('core.edit', 'com_swjprojects.key.' . $item->id);
							$canChange = $user->authorise('core.edit.state', 'com_swjprojects.key.' . $item->id);
							$link = ($canEdit) ? Route::_('index.php?option=com_swjprojects&task=key.edit&id='
								. $item->id) : '';
							?>
							<tr class="row<?php echo $i % 2; ?>" data-draggable-group="1">
								<td class="text-center">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->key); ?>
								</td>
								<td class="text-center">
									<?php echo (new PublishedButton)->render((int) $item->state, $i, [
										'task_prefix' => 'keys.',
										'disabled'    => !$canChange,
										'id'          => 'state-' . $item->id
									]); ?>
								</td>
								<td>
									<a <?php echo ($link) ? 'href="' . $link . '"' : 'disable'; ?>>
										<strong><?php echo $this->escape($item->key); ?></strong>
									</a>
								</td>
                                <?php
                                if(isset($item->extra) && count($item->extra)) {
								foreach($item->extra as $extra){
                                    if(is_array($extra)) {
                                           ?>
                                <td class = "extra">
                                    <div class = "<?php echo $extra['class']??'alert alert-info'; ?>"><?php echo $extra['td']?? Text::_('JUNDEFINED'); ?></div>
                                </td>
                                          <?php
                                    }
								}
								}
                                ?>
								<td class="d-none d-md-table-cell">
									<?php echo $item->order; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php if (!empty($item->user)) echo $item->user->name . ' (' . $item->user->email . ')'; ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo implode(', ', ArrayHelper::getColumn($item->projects, 'title')); ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo HTMLHelper::_('date', $item->date_start, Text::_('DATE_FORMAT_LC5')); ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo ($item->date_end > 0) ?
										HTMLHelper::_('date', $item->date_end, Text::_('DATE_FORMAT_LC5'))
										: Text::_('JNEVER'); ?>
								</td>
								<td class="d-none d-md-table-cell"><?php echo $item->id; ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<?php echo $this->pagination->getListFooter(); ?>
				<?php endif; ?>
				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>