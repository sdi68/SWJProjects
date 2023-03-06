<?php
defined('_JEXEC') or die;

use ECLabs\Library\ECLAuthorisation;
use ECLabs\Library\ECLUpdateInfoStatus;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

require_once JPATH_LIBRARIES.'/eclabs/classes/autoload.php';

class SWJProjectsViewToken extends HtmlView
{
    public 	function display($tpl = null) {
        $app = Factory::getApplication();
        $params = $app->input->get('params', '', 'raw');
        $json = array();
        $params = ECLAuthorisation::decodeAuthorisationParams($params);
        $user_id = ECLAuthorisation::checkAuthorise($params);
        if($user_id) {
            SWJProjectsHelperTokens::getToken($user_id,$params['element'],$json);
            SWJProjectsHelperTokens::getLastVersion($params['element'],$json);
            SWJProjectsHelperTokens::getProjectId($params['element'],$json);
        } else {
            //SWJProjectsHelperTokens::setError( 'ECLABS_CHECK_APP_ERROR_WRONG_USER',$json);
            SWJProjectsHelperTokens::setError( ECLUpdateInfoStatus::ECLUPDATEINFO_STATUS_ERROR_AUTHORIZATION,$json);
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