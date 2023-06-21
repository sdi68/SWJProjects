<?php
/*
 * @package    SWJProjects Component
 * @subpackage    com_swjprojects
 * @version    2.0.1
 * @author Econsult Lab.
 * @based on   SW JProjects Septdir Workshop - www.septdir.com
 * @copyright  Copyright (c) 2023 Econsult Lab. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://econsultlab.ru
 */

defined('_JEXEC') or die;

use ECLabs\Library\ECLAuthorisation;
use ECLabs\Library\ECLUpdateInfoStatus;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

require_once JPATH_LIBRARIES.'/eclabs/classes/autoload.php';

JLoader::register('SWJProjectsHelperKeys', JPATH_SITE . '/components/com_swjprojects/helpers/keys.php');


class SWJProjectsViewToken extends HtmlView
{
    public 	function display($tpl = null) {
        $app = Factory::getApplication();
        $params = $app->input->get('params', '', 'raw');
        $json = array();
        $params = ECLAuthorisation::decodeAuthorisationParams($params);
		if(!empty($params['user_data']['token']))
		{
			$tmp['token'] = $params['user_data']['token'];
			SWJProjectsHelperTokens::getProjectId($params['element'], $tmp);

			if(SWJProjectsHelperKeys::checkKey($tmp['project_id'],$tmp['token'])){
				SWJProjectsHelperTokens::getLastVersion($params['element'], $tmp);
				$json = $tmp;
			} else {
				SWJProjectsHelperTokens::setError(ECLUpdateInfoStatus::ECLUPDATEINFO_STATUS_ERROR_AUTHORIZATION, $json);
			}
		}
		else
		{
			$user_id = ECLAuthorisation::checkAuthorise($params);
			if ($user_id)
			{
				SWJProjectsHelperTokens::getToken($user_id, $params['element'], $json);
				SWJProjectsHelperTokens::getLastVersion($params['element'], $json);
				SWJProjectsHelperTokens::getProjectId($params['element'], $json);
			}
			else
			{
				SWJProjectsHelperTokens::setError(ECLUpdateInfoStatus::ECLUPDATEINFO_STATUS_ERROR_AUTHORIZATION, $json);
			}
		}
        $json['element'] = $params['element'];

        ob_end_clean();
        $app->clearHeaders();
        $app->setHeader('Content-Type','application/json', true);
        $app->sendHeaders();
        echo json_encode($json,JSON_UNESCAPED_UNICODE);
        $app->close();
    }

}