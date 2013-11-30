<?php
/**
 * @package ImpressPages

 *
 */
namespace Ip\Module\Content\Widget\IpForm;




class Controller extends \Ip\WidgetController{


    public function getTitle() {
        return __('Contact form', 'ipAdmin');
    }
    
    public function post ($instanceId, $data) {
        $postData = ipRequest()->getPost();

        $form = $this->createForm($instanceId, $data);
        $errors = $form->validate($postData);
        
        if ($errors) {
            $data = array(
                'status' => 'error',
                'errors' => $errors
            );
        } else {
            $this->sendEmail($form, $postData, $data);
            
            $data = array(
                'status' => 'success'
            );
        }

        // TODO use JsonRpc
        return new \Ip\Response\Json($data);
    }

    public function adminSnippets()
    {
        $snippets = array();

        $fieldObjects = Model::getAvailableFieldTypes();

        $fieldTypes = array ();
        foreach($fieldObjects as $fieldObject){
            $fieldTypes[] = array(
                'key' => $fieldObject->getKey(),
                'title' => $fieldObject->getTitle()
            );
        }
        usort($fieldTypes, array($this, 'sortFieldTypes'));
        $data['fieldTypes'] = $fieldTypes;

        $snippets[] = \Ip\View::create('snippet/popup.php', $data)->render();        //TODOX scandir Model::SNIPPET_DIR and return snippets as an array
        return $snippets;

    }

    public function sendEmail ($form, $postData, $data) {

        $contentData = array();

        $websiteName = ipGetOption('Config.websiteTitle');
        $websiteEmail = ipGetOption('Config.websiteEmail');


        $to = $from = $websiteEmail;
        $files = array();

        foreach($form->getFields() as $fieldKey => $field) {
            
            if ($field->getType() == \Ip\Form\Field::TYPE_REGULAR) {
                if (!isset($postData[$field->getName()])) {
                    $postData[$field->getName()] = null;
                }
                
                $title = $field->getLabel();
                $value = $field->getValueAsString($postData, $field->getName());
                $contentData[] = array(
                    'fieldClass' => get_class($field),
                    'title' => $title,
                    'value' => $value 
                );
            }

            if (get_class($field) == 'Ip\Form\Field\Email') {
                $userFrom = $field->getValueAsString($postData, $field->getName());
                if ($userFrom != '') {
                    $from = $userFrom;
                }
            }


            if (get_class($field) == 'Ip\Form\Field\File') {
                /**
                 * @var $uploadedFiles \Ip\Form\Field\Helper\UploadedFile[]
                 */
                $uploadedFiles = $field->getFiles($postData, $field->getName());
                foreach($uploadedFiles as $uploadedFile) {
                    $files[] = array(
                        'real_name' => $uploadedFile->getFile(),
                        'required_name' => $uploadedFile->getOriginalFileName()
                    );
                }
            }
        }
        $content = \Ip\View::create('helperView/email_content.php', array('values' => $contentData))->render();

        
        $emailData = array(
            'content' => $content,
            'name' => $websiteName,
            'email' => $websiteEmail
        );
        
        $email = \Ip\View::create('helperView/email.php', $emailData)->render();

        
        //get page where this widget sits :)
        $fullWidgetRecord = \Ip\Module\Content\Model::getWidgetFullRecord($postData['instanceId']);
        $pageTitle = '';
        if (isset($fullWidgetRecord['revisionId'])) {
            $revision = \Ip\Revision::getRevision($fullWidgetRecord['revisionId']);
            if (isset($revision['zoneName']) && $revision['pageId']) {
                $pageTitle = ipContent()->getZone($revision['zoneName'])->getPage($revision['pageId'])->getButtonTitle();
            }
        }
        
        $subject = $websiteName.': '.$pageTitle;

        $emailQueue = new \Ip\Module\Email\Module();
        $emailQueue->addEmail($from, '', $to, '',  $subject, $email, false, true, $files);

        $emailQueue->send();
        
    }
    
    
    public function managementHtml($instanceId, $data, $layout) {
        $fieldObjects = Model::getAvailableFieldTypes();
        
        $fieldTypes = array ();
        foreach($fieldObjects as $fieldObject){
            $fieldTypes[] = array(
                'key' => $fieldObject->getKey(),
                'title' => $fieldObject->getTitle()
            );
        }
        usort($fieldTypes, array($this, 'sortFieldTypes'));
        $data['fieldTypes'] = $fieldTypes;

        
        return parent::managementHtml($instanceId, $data, $layout);
    }
    
    public function previewHtml($instanceId, $data, $layout) {

        $data['form'] = $this->createForm($instanceId, $data);
        
        if (!isset($data['success'])) {
            $data['success'] = '';
        }
        
        return parent::previewHtml($instanceId, $data, $layout);
    }
    
    
    public function dataForJs($data) {
        //collect available field types
        $fieldTypeObjects = Model::getAvailableFieldTypes();
        
        $fieldTypes = array ();
        foreach($fieldTypeObjects as $typeObject){
            $fieldTypes[$typeObject->getKey()] = array(
                'key' => $typeObject->getKey(),
                'title' => $typeObject->getTitle(),
                'optionsInitFunction' => $typeObject->getJsOptionsInitFunction(),
                'optionsSaveFunction' => $typeObject->getJsOptionsSaveFunction(),
                'optionsHtml' => $typeObject->getJsOptionsHtml()
            );
        }
        $data['fieldTypes'] = $fieldTypes;
        
        if (empty($data['fields'])) {
            $data['fields'] = array();
            $data['fields'][] = array (
                'type' => 'IpText',
                'label' => '',
                'options' => array()
            );
        }
        
        
        
        return $data;
    }    
    
    /**
     * 
     * 
     * @param int $instanceId
     * @param array $data
     * @return \Ip\Form
     */
    private function createForm($instanceId, $data) {
        $form = new \Ip\Form();
        
        if (empty($data['fields']) || !is_array($data['fields'])) {
            $data['fields'] = array();
        }        
        foreach ($data['fields'] as $fieldKey => $field) {
            if (!isset($field['type']) || !isset($field['label'])) {
                continue;
            }
            if (!isset($field['options'])) {
                $field['options'] = array();
            }
            if (!isset($field['options']) || !is_array($field['options'])) {
                $field['options'] = array();
            }
            if (!isset($field['required'])) {
                $field['required'] = false;
            }
            $fieldType = Model::getFieldType($field['type']);
            if ($fieldType) {
                $fieldData = array (
                    'label' => $field['label'],
                    'name' => 'ipForm_field_'.$fieldKey,
                    'required' => $field['required'],
                    'options' => $field['options']
                );
                
                try {
                    $newField = $fieldType->createField($fieldData);
                    $form->addField($newField);
                } catch (\Ip\Module\Content\Exception $e) {
                    ipLog()->error('IpFormWidget.failedAddField: Widget failed to add field.', array('widget' => 'IpForm', 'exception' => $e, 'fieldData' => $fieldData));
                }
                
            }
        }
        
        

        //special variable to post to widget controller
        $field = new \Ip\Form\Field\Hidden(
        array(
        'name' => 'sa',
        'defaultValue' => 'Content.widgetPost'
        ));
        $form->addField($field);
        
        $field = new \Ip\Form\Field\Hidden(
        array(
        'name' => 'instanceId',
        'defaultValue' => $instanceId
        ));
        $form->addField($field);

        //antispam
        $field = new \Ip\Form\Field\Antispam(
        array(
        'name' => 'checkField'
        ));
        $form->addField($field);
        
        //submit
        $field = new \Ip\Form\Field\Submit(
        array(
        	'defaultValue' => __('Content.widget_contact_form.send', 'ipPublic', false)
        ));
        $form->addField($field);
        
    

        return $form;
    }
    
    protected function sortFieldTypes($a, $b) {
        return strcasecmp($a['title'], $b['title']);
    }
}