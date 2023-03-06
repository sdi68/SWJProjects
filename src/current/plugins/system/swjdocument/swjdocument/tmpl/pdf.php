<?php
/*
 * @package    SW JProjects Component
 * @subpackage    system/SWJDocument plugin
 * @version    1.0.0
 * @author Econsult Lab.
 * @copyright  Copyright (c) 2023 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

/**
 * @var Object $vars
 */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wr = $wa->getRegistry();
$wr->addRegistryFile('/media/plg_system_swjdocument/joomla.assets.json');
$wa->useScript('plg_system_swjdocument.pdf');
$wa->useStyle('plg_system_swjdocument.swjdocument');

$filename = $vars->doc_path;
?>
<iframe src="/media/plg_system_swjdocument/js/pdfjs-3.1.81/web/viewer.html?file=<?php echo $filename; ?>"
        class="pdf-frame"><?php echo Text::_('PLG_SYSTEM_SWJDOCUMENT_NOTHING_TO_SHOW'); ?></iframe>