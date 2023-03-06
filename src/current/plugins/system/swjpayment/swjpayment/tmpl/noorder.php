<?php
/*
 * @package    SW JProjects Component
 * @subpackage    system/SWJPayment plugin
 * @version    1.0.0
 * @author Econsult Lab.
 * @copyright  Copyright (c) 2023 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Layout variables
 * @var stdClass $vars Данные для отображения
 */

$version_string = "";
foreach ($vars->versions as $version)
{
	$text           = $version->tag->title . ' ' . $version->major . '.' . $version->minor . '.' . $version->micro;
	$version_string .= sprintf("<a href = \"%s\" target=\"_blank\">%s</a></br>\n", $version->download_link, $text);
}
?>

<div><?php echo $version_string; ?></div>
