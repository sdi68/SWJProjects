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

// phpcs:disable PSR1.Files.SideEffects
use Joomla\CMS\Factory;
use Joomla\CMS\Version;

\defined('JPATH_PLATFORM') or die;

// phpcs:enable PSR1.Files.SideEffects

/**
 * The "about" field file
 * @package pkg_swjprojects_payments
 * @subpackage  mod_swjprojects_downloads
 * @version 1.0.0
 * @since 1.0.0
 *
 */
class JFormFieldAbout extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.0.0
     */
    protected $type = 'about';

    /**
     * URL страницы модуля
     * @var string
     * @since  1.0.0
     */
    protected $ext_page = '';

    /**
     * URL документации модуля
     * @var string
     * @since  1.0.0
     */
    protected $ext_doc = '';

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param string $name The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.2
     */
    public function __get($name)
    {
        if ($name === 'ext_page' || $name === 'ext_doc') {
            return $this->$name;
        }

        return parent::__get($name);
    }

    /**
     * Method to set certain otherwise inaccessible properties of the form field object.
     *
     * @param string $name The property name for which to set the value.
     * @param mixed $value The value of the property.
     *
     * @return  void
     *
     * @since   3.2
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'ext_page':
            case 'ext_doc':
                $this->$name = (string)$value;
                break;

            default:
                parent::__set($name, $value);
        }
    }


    /**
     * Method to attach a Form object to the field.
     *
     * @param \SimpleXMLElement $element The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param mixed $value The form field value to validate.
     * @param string $group The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   3.2
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        if ($return) {
            $this->ext_page = (string)$this->element['ext_page'];
            $this->ext_doc = (string)$this->element['ext_doc'];
        }

        return $return;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   1.0.0
     */
    protected function getInput()
    {
        /** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
        // Подключаем скрипты админки
        if ((new Version())->isCompatible('4.0')) {
            $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
            $wr = $wa->getRegistry();
            $wr->addRegistryFile('/media/mod_swjprojects_downloads/joomla.assets.json');
//            $wa->useScript('mod_swjprojects_downloads.back');
//            $wa->useStyle('mod_swjprojects_downloads.back');
            $wa->useStyle('mod_swjprojects_downloads.about');
        } else {
            $doc = JFactory::getDocument();
//            $doc->addScript('/media/mod_swjprojects_downloads/js/back.js');
//            $doc->addStyleSheet('/media/mod_swjprojects_downloads/css/back.css');
            $doc->addStyleSheet('/media/mod_swjprojects_downloads/css/about.css');
        }

        if (empty($this->ext_image) || !file_exists($this->ext_image)) {
            $this->ext_image = '/media/mod_swjprojects_downloads/img/logo.png';
        }

        $data = $this->form->getData();
        $module = $data->get('module');
        $doc = Factory::getApplication()->getDocument();

        $module_info = simplexml_load_file(JPATH_SITE . "/modules/" . $module . "/" . $module . ".xml");
        //var_dump($module_info);

        $html = "<div class = \"about-wrap\">";
        $html .= "<div class = \"about-img\">";
        $html .= ('<img src = "/media/mod_swjprojects_downloads/img/logo.png"/>');
        $html .= "</div>";
        $html .= "<div class = \"about-intro\">";
        $html .= "<div class = \"about-title\">";
        $html .= (JText::_(strtoupper($module) . "_DESCRIPTION") . '.<span> ' . JText::_("JVERSION") . ' ' . $module_info->version . '</span>');
        $html .= "</div>";
        $html .= "<div class = \"about-links\">";
        if (!empty($this->ext_page))
            $html .= ('<a href ="' . $this->ext_page . '">' . JText::_('SDI_ABOUT_FIELD_PAGE') . '</a>');
        if (!empty($this->ext_doc))
            $html .= ('<a href ="' . $this->ext_doc . '">' . JText::_('SDI_ABOUT_FIELD_DOC') . '</a>');
        $html .= "</div>";
        $html .= "<div class = \"about-copyright\">";
        $html .= $module_info->copyright;
        $html .= "</div>";
        $html .= "</div>";
        $html .= "</div>";

        return $html;
    }
}