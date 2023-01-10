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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;


if (!class_exists('SWJPaymentStatuses')) {
    require_once JPATH_PLUGINS . '/system/swjpayment/classes/SWJPaymentStatuses.php';
}

if (!class_exists('SWJPaymentHelper')) {
    require_once JPATH_PLUGINS . '/system/swjpayment/helper.php';
}

/**
 * Абстрактный класс плагинов пакета
 * @package pkg_swjprojects_payments
 * @subpackage  system/swjpayment
 * @version 1.0.0
 * @since 1.0.0
 */
abstract class SWJPaymentPlugin extends CMSPlugin
{
    /**
     * Имя компонента
     * @since 1.0.0
     */
    private const _COMPONENT_NAME = "com_swjprojects";

    /**
     * @var \Joomla\CMS\Application\CMSApplication|null
     * @since 1.0.0
     */
    protected $app;

    /**
     * Путь до файла плагина коннектора
     * Должна быть инициализирована в самом коннекторе
     * @var string
     * @since 1.0.0
     */
    protected $_plugin_path = "";

    /**
     * Флаг разрешения логирования работы плагина
     * @var bool
     * @since 1.0.0
     */
    protected $enabled_log = false;

    /**
     * @var JRegistry|Registry
     * @since 1.0.0
     */
    protected $component_params;

    /**
     * Текущий пользователь
     * @var User|null
     * @since 1.0.0
     */
    protected $current_user = null;

    /**
     * Конструктор
     * @param $subject
     * @param $config
     * @throws Exception
     * @since 1.0.0
     */
    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);
        $this->app = Factory::getApplication();
        $this->component_params = JComponentHelper::getParams(self::_COMPONENT_NAME);
        //$this->enabled_log = (bool)$this->getPluginParam('logging', false);
        // Единая настройка для всех плагинов
        $this->enabled_log = (bool)$this->component_params->get('swjpayment_logging', false);
        $this->_plugin_path = dirname(__DIR__);
        $this->current_user = SWJPaymentHelper::getCurrentUser();
    }

    /**
     * Логирование работы плагина
     *
     * @param array $data
     *
     * @return void
     * @since 1.0.0
     */
    protected function _logging(array $data): void
    {
        SWJPaymentHelper::Storelog($this->_name, $data, $this->enabled_log);
    }

    /**
     * Проверяет контекст, находимся ли в форме настроек компонента или нет
     *
     * @param string $context Контекст формы
     * @param string $component Имя компонента
     *
     * @return bool
     * @since 1.0.0
     */
    protected final function _checkConfigForm(string $context, string $component): bool
    {
        return $context == 'com_config.component' && $component == self::_COMPONENT_NAME;
    }

    /**
     * Изменяет имя поля в форме настроек в соответствии с плагином коннектора
     *
     * @param string $name Исходное имя поля
     * @param string $plugin_name Имя платежного плагина
     * @return string
     *
     * @since 1.0.0.
     */
    protected final function _changeFieldName(string $name, string $plugin_name = ''): string
    {
        if (empty($plugin_name))
            return $this->_name . '_' . $name;
        else
            return $plugin_name . '_' . $name;
    }

    /**
     * Получает значение настройки конкретного коннектора
     *
     * @param string $param_name Имя параметра
     * @param mixed $default Значение по-умолчанию
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    protected final function getPluginParam(string $param_name, $default)
    {
        return $this->component_params->get($this->_changeFieldName($param_name), $default);
    }

    /**
     * Подгружает к основной форме поля из дополнительного xml файла
     * Структура дополнительного файла должна быть такой
     * <fieldset>
     * <field></field>
     * </fieldset>
     * Подгружает к настройкам специфические поля плагинов-коннекторов
     *
     * @param SimpleXMLElement $xml Основная форма
     * @param SimpleXMLElement $extraXML Дополнительная форма
     *
     * @return bool
     *
     * @since 1.0.0
     */
    protected final function _mergeXMLExtraFieldset(SimpleXMLElement &$xml, SimpleXMLElement $extraXML): bool
    {
        foreach ($extraXML->children() as $fld) {
            $new = $xml->fieldset->addChild('field', $fld);
            foreach ($fld->attributes() as $attr) {
                $attr_name = $attr->getName();
                if ($attr_name == 'name') {
                    $attr = $this->_changeFieldName($attr);
                }
                $new->addAttribute($attr_name, $attr);
            }
        }

        return true;
    }

    /**
     *
     * Загружает языковые файлы коннектора
     * @return void
     * @since 1.0.1
     */
    protected final function _loadExtraLanguageFiles(): void
    {
        $lang = Factory::getLanguage();
        $extension = 'plg_' . $this->_type . '_' . $this->_name;
        $base_dir = JPATH_ADMINISTRATOR;
        // язык плагина
        $lang->load($extension, $base_dir);
        // язык компонента
        $extension = self::_COMPONENT_NAME;
        $lang->load($extension, $base_dir);
    }

    /**
     * Build Layout path
     *
     * @param string $layout Layout name
     *
     * @return   string  Layout Path
     * @throws Exception
     * @since   1.0.0
     *
     */
    protected final function _buildLayoutPath(string $layout): string
    {
        $app = Factory::getApplication();

        $core_file = $this->_plugin_path . '/' . $this->_name . '/tmpl/' . $layout . '.php';
        $override = JPATH_BASE . '/' . 'templates' . '/' . $app->getTemplate() . '/html/plugins/' .
            $this->_type . '/' . $this->_name . '/' . $layout . '.php';

        if (JFile::exists($override)) {
            return $override;
        } else {
            return $core_file;
        }
    }

    /**
     * Builds the layout to be shown, along with hidden fields.
     *
     * @param object $vars Data from component
     * @param string $layout Layout name
     *
     * @return   string  Layout Path
     * @throws Exception
     * @since   1.0.0
     *
     */
    protected final function _buildLayout(object $vars, string $layout = 'default'): string
    {
        // Load the layout & push variables
        ob_start();
        $layout = $this->_buildLayoutPath($layout);

        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wr = $wa->getRegistry();
        $wr->addRegistryFile('/media/plg_system_swjpayment/joomla.assets.json');
        $wa->useScript('plg_system_swjpayment.swjpayment');
        $wa->useStyle('plg_system_swjpayment.swjpayment');

        // Подключаем общий шаблон вывода выбора типа оплаты
        if (isset($vars->common_layout) && !empty($vars->common_layout)) {
            include $vars->common_layout;
        }
        include $layout;
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
}