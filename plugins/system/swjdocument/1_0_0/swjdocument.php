<?php
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

if (!class_exists('SWJPaymentPlugin')) {
    require_once JPATH_PLUGINS . '/system/swjpayment/classes/swjpayment_plugin.php';
}

if (!class_exists('SWJDocumentHelper')) {
    require_once dirname(__FILE__).'/swjdocument/helper.php';
}

class PlgSystemSWJDocument extends SWJPaymentPlugin {

    /**
     * Имя компонента SWJProjects
     * @since 1.0.0
     */
    private const _SWJPROJECTS = "com_swjprojects";

    /**
     * Контекст формы отображения документа
     * @since 1.0.0
     */
    private const _DOCUMENT_CONTEXT = "com_swjprojects.document";

    public function __construct(&$subject, array $config = array())
    {
        parent::__construct($subject, $config);
        $this->_plugin_path = __DIR__;
    }


    public function onShowPDF(string $context, object $item, string &$html):bool
    {
        switch (true) {
            case $this->_checkContext($context, self::_DOCUMENT_CONTEXT):
                $vars = new stdClass();
                $pdf = SWJDocumentHelper::getPDF((string)$item->id);
                // получаем относительный путь
                $pdf = str_replace(JPATH_BASE,'',$pdf);
                // Нет pdf документа
                if(empty($pdf))
                    return true;
                $vars->doc_path = $pdf;
                $html .= $this->_buildLayout($vars, 'pdf');
                break;
            default:
                return false;
        }
        return true;
    }

    public final function onContentPrepareForm(JForm $form, $data): bool
    {
        // Check we are manipulating a valid form.
        $name = $form->getName();
        $jInput = $this->app->input;
        $option = $jInput->get("option","");
        $view = $jInput->get("view","");
        $layout = $jInput->get("layout","");
        $content = $option.'.'.$view.'.'.$layout;
        $component = str_replace("'", '', $jInput->getString('component', ''));
        // загружаем язык плагина
        $this->_loadExtraLanguageFiles();
        switch(true) {
            case $this->_checkConfigForm($name, $component):
                // форма настроек com_swjprojects
                // Формируем форму настроек
                $xmlElement = new SimpleXMLElement(JPATH_PLUGINS . '/system/swjdocument/forms/settings.xml', null, $data_is_url = true);
                $xmlElement->fieldset->addAttribute('name', 'settings_' . $this->_name);
                $xmlElement->fieldset->addAttribute('label', JText::_(mb_strtoupper('plg_' . $this->_type . '_' . $this->_name) . '_SETTINGS_FIELDSET_LABEL'));

                foreach ($xmlElement->fieldset->children() as $field) {
                    $field['name'] = $this->_changeFieldName($field['name']);
                }
                $form->setField($xmlElement, '', true, $this->_name . '_settings');
                break;
            case $this->_checkContext($content,self::_DOCUMENT_CONTEXT.".edit"):
                // Форма редактирования документа
                $xmlElement = new SimpleXMLElement(JPATH_PLUGINS . '/system/swjdocument/forms/pdf.xml', null, $data_is_url = true);
                $form->setField($xmlElement, '', true, 'global');
                break;
        }
        return true;
    }

    public final function onContentPrepareData(string $context, $data): bool{
        // загружаем язык плагина
        $this->_loadExtraLanguageFiles();
        if (is_array($data)) {
            $data = (object) $data;
        }
        if(is_null($data->id) || $data->id == 0)
            return true;

        switch(true) {
            case $this->_checkContext($context, self::_DOCUMENT_CONTEXT):
                $this->_logging(array('onContentPrepareData started...'));
                $pdf = SWJDocumentHelper::getPDF($data->id);
                $this->_logging(array(sprintf('onContentPrepareData set selected_pdf = %s',$pdf)));
                $data->selected_pdf = $pdf;
                return true;
            default:
                return true;
        }
        return true;
    }

    public function onContentAfterSave($context, $table, $isNew, $data): bool
    {
        // загружаем язык плагина
        $this->_loadExtraLanguageFiles();
        switch(true) {
            case $this->_checkContext($context,self::_DOCUMENT_CONTEXT):
                $this->_logging(array('onContentAfterSave started...'));
                $jInput = $this->app->input;

                // Формируем Id редактируемого/создаваемого документа
                $doc_id = (!empty($data['id']) && $data['id'] > 0)  ? $data['id'] : $table->getId();

                $jform = $jInput->get('jform',false);
                $delete_pdf = false;
                if($jform) {
                    $delete_pdf = isset($jform['delete_pdf']);
                }
                if($delete_pdf) {
                    // Удаляем файл
                    SWJDocumentHelper::deletePDF($doc_id);
                    return true;
                }
                if (isset($_FILES['jform'])) {
                    if(is_file($_FILES['jform']['tmp_name']['pdf'])) {
                        if(isset($_FILES['jform']['type']['pdf'])) {
                            $msg = Text::sprintf('PLG_SYSTEM_SWJDOCUMENT_ADD_PDF',$_FILES['jform']['tmp_name']['pdf']);
                            $this->_logging(array($msg));
                            $pdf_file = SWJDocumentHelper::savePDFFile($this->component_params,$doc_id);
                            if($pdf_file) {
                                $pdf_data = array(
                                    'doc_id' => $doc_id,
                                    'pdf' => $pdf_file,
                                    'pdf_create_date' => Factory::getDate()->toSql()
                                );
                                $this->_logging(array('onContentAfterSave. Saving pdf_data', $pdf_data));
                                if(SWJDocumentHelper::savePDF($pdf_data)) {
                                    $this->_logging(array('onContentAfterSave. Saving pdf_data successfully'));
                                } else {
                                    $this->_logging(array('onContentAfterSave. Saving pdf_data with error'));
                                }
                                return true;
                            }
                            $this->_logging(array('onContentAfterSave. Error by saving pdf file'));
                            return true;
                        } else {
                            $msg = Text::_('PLG_SYSTEM_SWJDOCUMENT_ERROR_NO_PDF');
                        }
                    } else {
                        $msg = Text::_('PLG_SYSTEM_SWJDOCUMENT_EMPTY_FILE');
                    }
                    $this->_logging(array('onContentAfterSave',$msg));
                }
                break;
            default:
                return true;
        }
        return true;
    }

    public function onContentAfterDelete(string $context, Table $table): bool {
        // загружаем язык плагина
        $this->_loadExtraLanguageFiles();
        switch(true) {
            case $this->_checkContext($context, self::_DOCUMENT_CONTEXT):
                // Удаляем файл и запись в БД
                $this->_logging(array('onContentAfterDelete started...'));
                $id = $table->getId();
                if(SWJDocumentHelper::deletePDF($id)) {
                    $this->_logging(array(sprintf('onContentAfterDelete id= %s deleted successfully',$id)));
                } else {
                    $this->_logging(array(sprintf('onContentAfterDelete id= %s deleted with error',$id)));
                }
                return true;
            default:
                return true;
        }
        return true;
    }

    /**
     * Проверяет соответствие контекста требуемому
     * @param $context
     * @param $needle
     * @return bool
     * @since 1.0.0.
     */
    private function _checkContext($context, $needle)
    {
        return $context == $needle;
    }
}