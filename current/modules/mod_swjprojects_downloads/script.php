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

use Joomla\CMS\Factory;
use Joomla\CMS\Version;

defined('_JEXEC') or die;


class mod_swjprojects_downloadsInstallerScript
{
    public function preflight($type, $parent)
    {
        if ($type == 'uninstall') {
            return true;
        }

        $app = Factory::getApplication();

        $jversion = new Version();
        if (!$jversion->isCompatible('3.0.0')) {
            $app->enqueueMessage('Please upgrade to at least Joomla! 3.0.0 before continuing!', 'error');

            return false;
        }

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('extension_id')
            ->from($db->qn('#__extensions'))
            ->where($db->qn('type') . ' = ' . $db->q('plugin'))
            ->where($db->qn('folder') . ' = ' . $db->q('system'))
            ->where($db->qn('element') . ' = ' . $db->q('swjpayment'));

        if (is_null($db->setQuery($query)->loadResult())) {
            $app->enqueueMessage('The module requires an installed and activated plugin swjpayment to work! Install please this before!', 'error');

            return false;
        }

        return true;
    }

    public function postflight($type, $parent)
    {
        if ($type == 'uninstall') {
            return true;
        }

        $jversion = new Version;
        ?>
        <style type="text/css">
            .version-history {
                margin: 0 0 2em 0;
                padding: 0;
                list-style-type: none;
            }

            .version-history > li {
                margin: 0 0 0.5em 0;
                padding: 0 0 0 4em;
                text-align: left;
                font-weight: normal;
            }

            .version-new,
            .version-fixed,
            .version-upgraded {
                float: left;
                font-size: 0.8em;
                margin-left: -4.9em;
                width: 4.5em;
                color: white;
                text-align: center;
                font-weight: bold;
                text-transform: uppercase;
                -webkit-border-radius: 4px;
                -moz-border-radius: 4px;
                border-radius: 4px;
            }

            .version-new {
                background: #7dc35b;
            }

            .version-fixed {
                background: #e9a130;
            }

            .version-upgraded {
                background: #61b3de;
            }
        </style>

        <h3>The module mod_swjprojets_downloads v1.0.0 Changelog</h3>
        <ul class="version-history">
            <li><span class="version-new">NEW</span> First version.</li>
        </ul>
        <?php if (0): ?>

        <a class="btn" href="#" target="_blank">Read the documentation</a>
        <a class="btn" href="#" target="_blank">Get Support!</a>
    <?php endif; ?>
        <div style="clear: both;"></div>
        <?php
    }

    public function install($parent)
    {
        $source = $parent->getParent()->getPath('source');
        $this->runSQL($source, "install.sql");
    }

    protected function runSQL($source, $file)
    {
        $db = Factory::getDbo();
        $driver = strtolower($db->name);
        if (strpos($driver, 'mysql') !== false) {
            $driver = 'mysql';
        } elseif ($driver == 'sqlsrv') {
            $driver = 'sqlazure';
        }

        //$sqlfile = $source . '/sql/' . $driver . '/' . $file;
        $sqlfile = __DIR__ . "/sql/mysql/" . $file;
        if (file_exists($sqlfile)) {
            $buffer = file_get_contents($sqlfile);
            if ($buffer !== false) {
                if (is_callable(array($db, 'splitSql'))) {
                    $queries = $db->splitSql($buffer);
                } else {
                    $queries = JInstallerHelper::splitSql($buffer);
                }

                foreach ($queries as $query) {
                    $query = trim($query);
                    if ($query != '' && $query[0] != '#') {
                        $db->setQuery($query);
                        if (!$db->execute()) {
                            JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));
                        }
                    }
                }
            }
        }
    }

    public function uninstall($parent)
    {
        $source = $parent->getParent()->getPath('source');
        $this->runSQL($source, "uninstall.sql");
    }
}
