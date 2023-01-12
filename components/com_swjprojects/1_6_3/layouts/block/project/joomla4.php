<?php

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;
extract($displayData);
/**
 * Layout variables
 * -----------------
 *
 * @var  stdClass  $project  Project.
 * @var  Joomla\Registry\Registry  $payment  Item.
 * @var  stdClass $version    Version
 * @var  int  $md  Column count for block (default 3).
 *
 */
?>

<div class="col-md-<?php echo $md ?? 3; ?> project-info">
    <div class="card">
        <?php if ($icon = $project->images->get('icon')): ?>
            <a href="<?php echo $project->link; ?>">
                <?php echo HTMLHelper::image($icon, $project->title, array('class' => 'card-img-top')); ?>
            </a>
        <?php endif; ?>
        <div class="card-body">
            <ul class="list-unstyled small">
                <li>
                    <strong><?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD_TYPE'); ?>: </strong>
                    <?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD_TYPE_' . $project->download_type); ?>
                </li>
                <?php if ($project->download_type === 'paid' && $payment->get('price')): ?>
                    <li>
                        <strong><?php echo Text::_('COM_SWJPROJECTS_PRICE'); ?>: </strong>
                        <span class="text-success"><?php echo $payment->get('price'); ?></span>
                    </li>
                <?php endif; ?>
                <?php if ($version): ?>
                    <li>
                        <strong><?php echo Text::_('COM_SWJPROJECTS_VERSION'); ?>: </strong>
                        <a href="<?php echo $version->link; ?>">
                            <?php echo $version->version; ?>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if (method_exists($project,'downloads') && $project->downloads): ?>
                    <li>
                        <strong><?php echo Text::_('COM_SWJPROJECTS_STATISTICS_DOWNLOADS'); ?>: </strong>
                        <?php echo $project->downloads; ?>
                    </li>
                <?php endif; ?>
            </ul>
            <div class="text-center">
                <?php if (($project->download_type === 'paid' && $payment->get('link'))): ?>
                    <a href="<?php echo $payment->get('link'); ?>"
                       class="btn btn-success col-12">
                        <?php echo Text::_('COM_SWJPROJECTS_BUY'); ?>
                    </a>
                <?php elseif ($project->download_type === 'free'): ?>
                    <a href="<?php echo $project->download; ?>" class="btn btn-primary col-12"
                       target="_blank">
                        <?php echo Text::_('COM_SWJPROJECTS_DOWNLOAD'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
