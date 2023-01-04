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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$this->document->getWebAssetManager()
	->useScript('keepalive')
	->useScript('form.validate');
HTMLHelper::stylesheet('com_swjprojects/admin-j4.min.css', array('version' => 'auto', 'relative' => true));

?>
<form action="<?php echo Route::_('index.php?option=com_swjprojects&view=category&id=' . $this->item->id); ?>"
	  method="post" name="adminForm" id="item-form" class="form-validate translate-tabs" enctype="multipart/form-data">
	<div class="row title-alias form-vertical mb-3">
		<div class="col-12 col-md-6">
			<?php echo LayoutHelper::render('components.swjprojects.translate.field', array(
				'forms' => $this->translateForms, 'name' => 'title')); ?>
		</div>
		<div class="col-12 col-md-6">
			<?php echo $this->form->renderField('alias'); ?>
		</div>
	</div>
	<div class="main-card">
		<div class="row п-0">
			<div class="col-lg-8">
				<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JGLOBAL_FIELDSET_CONTENT')); ?>
				<fieldset class="form-vertical p-3">
					<?php echo LayoutHelper::render('components.swjprojects.translate.field', array(
						'forms' => $this->translateForms, 'name' => 'description')); ?>
				</fieldset>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'metadata', Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS')); ?>
				<fieldset>
					<?php echo LayoutHelper::render('components.swjprojects.translate.fieldset', array(
						'forms' => $this->translateForms, 'name' => 'metadata')); ?>
				</fieldset>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
			</div>
			<div class="col-lg-4">
				<div class="form-vertical p-3">
					<div class="options-form">
						<?php echo $this->form->renderFieldset('global'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="return" value="<?php echo Factory::getApplication()->input->getCmd('return'); ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>