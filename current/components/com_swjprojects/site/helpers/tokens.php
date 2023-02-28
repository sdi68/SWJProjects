<?php
defined('_JEXEC') or die;

use ECLabs\Library\ECLUpdateInfoStatus;
use Joomla\CMS\Factory;

require_once JPATH_LIBRARIES.'/eclabs/classes/autoload.php';

class SWJProjectsHelperTokens
{
    public static function getToken(int $user_id, string $element,&$json): bool
    {
        $id = self::_getProjectId($element);
        if ($id) {
            $dbo = Factory::getDbo();
            $nullDate = $dbo->quote($dbo->getNullDate());
            $nowDate  = $dbo->quote(JFactory::getDate()->toSql());
            $query = $dbo->getQuery(true);
            $query->select($dbo->quoteName('key'))
                ->from($dbo->quoteName('#__swjprojects_keys'))
                ->where($dbo->quoteName('user') . ' = ' . $dbo->quote($user_id))
                ->where('(' .
                    $dbo->quoteName('projects') . ' LIKE ' . $dbo->quote($id . ',%') . ' OR ' .
                    $dbo->quoteName('projects') . ' LIKE ' . $dbo->quote('%,' . $id . ',%') . ' OR ' .
                    $dbo->quoteName('projects') . ' LIKE ' . $dbo->quote('%,' . $id) .
                    ')'
                )
                ->where('state = 1')
                ->where('(date_start = ' . $nullDate . ' OR date_start <= ' . $nowDate . ')')
                ->where('(date_end = ' . $nullDate . ' OR date_end >= ' . $nowDate . ')')
                ->where('(' . $dbo->quoteName('limit') . ' = 0 OR limit_count > 0)');;
            $dbo->setQuery($query);
            $key = $dbo->loadResult();
            if($key) {
                $json['token'] = $key;
                return true;
            } else {
                // Нет активных ключей
                //self::setError('ECLABS_CHECK_APP_ERROR_USER_HAS_NOT_KEY',$json);
                self::setError(ECLUpdateInfoStatus::ECLUPDATEINFO_STATUS_ERROR_MISSING_TOKEN,$json);
            }
        } else {
            // Нет такого проекта
            //self::setError('ECLABS_CHECK_APP_ERROR_ENOUGH_PROJECT',$json);
            self::setError(ECLUpdateInfoStatus::ECLUPDATEINFO_STATUS_ERROR_MISSING_EXTENSION,$json);
        }
        return false;
    }

    public static function getLastVersion($element ,&$json):bool {
        $id = self::_getProjectId($element);
        if($id) {
            $dbo = Factory::getDbo();
            $query = $dbo->getQuery(true);
            $query->select($dbo->quoteName('major'))
                ->select($dbo->quoteName('minor'))
                ->select($dbo->quoteName('micro'))
                ->from($dbo->quoteName('#__swjprojects_versions'))
                ->where($dbo->quoteName('project_id') . ' = ' . $dbo->quote($id))
                ->where($dbo->quoteName('state').' = 1');
            $dbo->setQuery($query);
            $versions = $dbo->loadAssocList();
            if($versions) {
                $max = 0;
                $last = '';
                foreach ($versions as $version) {
                    if((int)($version['major'].$version['minor'].$version['micro']) > $max ) {
                        $max =  (int)($version['major'].$version['minor'].$version['micro']);
                        $last =  $version['major'].'.'.$version['minor'].'.'.$version['micro'];
                    }
                }
                $json['last_version'] = $last;
                return true;
            } else {
                // Нет активных версий
                //self::setError('ECLABS_CHECK_APP_ERROR_ENOUGH_VERSIONS',$json);
                self::setError(ECLUpdateInfoStatus::ECLUPDATEINFO_STATUS_ERROR_MISSING_VERSION,$json);
            }
        } else {
            // Нет такого проекта
            //self::setError('ECLABS_CHECK_APP_ERROR_ENOUGH_PROJECT',$json);
            self::setError(ECLUpdateInfoStatus::ECLUPDATEINFO_STATUS_ERROR_MISSING_EXTENSION,$json);
        }
        return false;
    }

    private static function _getProjectId(string $element) {
        $dbo = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select($dbo->quoteName('id'))
            ->from($dbo->quoteName('#__swjprojects_projects'))
            ->where($dbo->quoteName('element') . ' LIKE ' . $dbo->quote($element));
        $dbo->setQuery($query);
        return $dbo->loadResult();
    }

    public static function getProjectId(string $element,&$json):bool {
        $id = self::_getProjectId($element);
        if($id) {
            $json['project_id'] = $id;
            return true;
        } else {
            // Ошибка. Нет проекта
            self::setError(ECLUpdateInfoStatus::ECLUPDATEINFO_STATUS_ERROR_MISSING_EXTENSION,$json);
        }
    }

    public static function setError(string $error, &$json): void
    {
        //$json['error'] = $error;
        $json['error'] = array(
            'code' => $error,
            'message' => ECLUpdateInfoStatus::getEnumNameText($error)
        );
    }
}
