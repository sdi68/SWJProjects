<?php
/*
 * @package    SW JProjects Payment
 * @subpackage module mod_swjprojects_downloads
 * @version    1.0.0
 * @author     Econsult lab - https://econsultlab.ru
 * @copyright  Copyright (c) 2022 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

/**
 * @var object $module
 * @var Joomla\Registry\ $params
 * @var array $list
 */
$style = $params->get('moduleclass_sfx', '');

?>
<div id="mod_swjprojects_downloads_<?php echo $module->id; ?>" class="download-list <?php echo $style; ?>">
    <?php if (isset($list['error'])): ?>
        <div class="alert alert-danger"><?php echo $list['error']->getMessage(); ?></div>
    <?php else: ?>
        <?php if (count($list)): ?>
            <table class="table table-striped">
                <thead>
                <tr>
                    <td>Заголовок</td>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <th width="20px">Заказ</th>
                    <th width="20px">Дата</th>
                    <th width="20px">Статус</th>
                    <th>Оплата</th>
                    <th>Компонент</th>
                    <th>Скачать</th>
                </tr>
                <?php
                foreach ($list as $row => $item) {
                    require ModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default') . '-row');
                }
                ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-warning"><?php echo Text::_('MOD_SWJPROJECTS_DOWNLOADS_EMPTY_LIST'); ?></div>
    <?php endif; ?>
<?php endif; ?>
</div>