<?php
/**
 * @package ImpressPages
 *
 *
 */
namespace Ip\Module\Config;




class AdminController extends \Ip\Controller{

    public function index()
    {


        ipAddJavascript(ipFileUrl('Ip/Module/Config/assets/config.js'));

        $form = Forms::getForm();
        $data = array (
            'form' => $form
        );
        return \Ip\View::create('view/configWindow.php', $data)->render();

    }


    public function saveValue()
    {
        $request = \Ip\ServiceLocator::request();

        $request->mustBePost();

        $post = $request->getPost();
        if (empty($post['fieldName'])) {
            throw new \Exception('Missing required parameter');
        }
        $fieldName = $post['fieldName'];
        if (!isset($post['value'])) {
            throw new \Exception('Missing required parameter');
        }
        $value = $post['value'];

        if (!in_array($fieldName, array('automaticCron', 'cronPassword', 'keepOldRevision', 'websiteTitle', 'websiteEmail'))) {
            throw new \Exception('Unknown config value');
        }

        $emailValidator = new \Ip\Form\Validator\Email();
        $error = $emailValidator->validate(array('value' => $value), 'value', \Ip\Form::ENVIRONMENT_ADMIN);
        if ($fieldName === 'websiteEmail' && $error !== false) {
            return $this->returnError($error);
        }

        $numberValidator = new \Ip\Form\Validator\Number();
        $error = $numberValidator->validate(array('value' => $value), 'value', \Ip\Form::ENVIRONMENT_ADMIN);
        if ($fieldName === 'keepOldRevision' && ($error !== false || $value == '')) { //if user enters some text, browser sends empty message and $error becomes false. We have to check that.
            return $this->returnError($numberValidator->validate(array('value' => 'for sure incorrect value'), 'value', \Ip\Form::ENVIRONMENT_ADMIN)); //this is to get original Number error message instead of hardcoding text once again
        }


        if (in_array($fieldName, array('websiteTitle', 'websiteEmail'))) {
            if (!isset($post['languageId'])) {
                throw new \Exception('Missing required parameter');
            }
            $languageId = $post['languageId'];
            ipSetOptionLang('Config.' . $fieldName, $value, $languageId);
        } else {
            ipSetOption('Config.' . $fieldName, $value);
        }


        return new \Ip\Response\Json(array(1));

    }

    private function returnError($errorMessage)
    {
        $data = array(
            'error' => $errorMessage
        );
        return new \Ip\Response\Json($data);
    }
}