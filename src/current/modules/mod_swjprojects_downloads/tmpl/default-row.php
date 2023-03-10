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

use Joomla\CMS\Button\PublishedButton;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Helper\ModuleHelper;

/**
 * @var array $item
 * @var int $row
 * @var ModuleHelper $module
 */
$extra = "";
$status_style = '';
if ($item['key']->extra){
    $extra = $item['key']->extra;
    if(property_exists($extra,'payment_status') && !is_null($extra->payment_status)) {
        $status_style = match ($extra->payment_status) {
            SWJPaymentStatuses::SWJPAYMENT_STATUS_CONFIRMED => 'alert-success',
            SWJPaymentStatuses::SWJPAYMENT_STATUS_PENDING => 'alert-warning',
            SWJPaymentStatuses::SWJPAYMENT_STATUS_CANCELED => 'alert-danger',
            default => 'alert-info',
        };
    } else {
        $status_style = 'alert-danger';
        $extra->payment_status = "";
    }
}

?>
<tr id = "row-<?php echo $row?>">
    <td data-item="order" data-id="<?php echo $item['key']->id; ?>"><?php echo empty($item['key']->order) ? '--': $item['key']->order; ?></td>
    <td data-item="date_start"><?php echo (new Date($item['key']->date_start))->format('d.m.Y'); ?></td>
    <td data-item="state"><?php
        echo (new PublishedButton)->render((int)$item['key']->state, $row, [
            'task_prefix' => 'keys.',
            'disabled' => true,
            'id' => 'state-' . $item['key']->id
        ]);
        ?>
    </td>
    <td data-item="payment_status"><?php
    if($extra) {

        echo '<span class = "'.$status_style.'">'. ($extra->payment_status_title ?? SWJPaymentStatuses::getEnumNameText(SWJPaymentStatuses::SWJPAYMENT_STATUS_PENDING)).'</span>';
        if($extra->payment_status == SWJPaymentStatuses::SWJPAYMENT_STATUS_PENDING) {
            ?>
            <button type = "submit" class = "btn btn-info btn-refresh" onclick = "getPaymentStatus(this,<?php echo $row.','.$module->id; ?>); return false;">
                <span class="icon-refresh" aria-hidden="true"></span>
            </button>
            <?php
        }
    } else {
        echo '--';
    }
    ?>
    </td>
    <td data-item="payment_status"><?php echo $item['key']->projects->title; ?></td>
<?php
$version_string = "";
foreach ($item['versions'] as $version) {
    $text = $version->tag->title.' '.$version->major.'.'.$version->minor.'.'.$version->micro;
    if($item['key']->state) {
        $version_string .= sprintf("<a href = \"%s\" target=\"_blank\">%s</a></br>\n", $version->download_link, $text);
    } else {
        $version_string .= sprintf("<span>%s</span></br>\n", $text);
    }
}
?>
    <td data-item="versions"><?php echo $version_string; ?></td>
</tr>
