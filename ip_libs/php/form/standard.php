<?php
/**
 * @package ImpressPages
 *
 *
 */

namespace Library\Php\Form;


if (!defined('CMS')) exit;

/** @private */
require_once \Ip\Config::libraryFile('php/form/hn_captcha/hn_captcha.class.php');
/** @private */
require_once (__DIR__.'/templates/standard.php');

/**
 * Class to generate common forms.
 * @package Library
 */
class Standard{
    /** private */
    var $errors;
    /** @var array fields in the form*/
    var $fields;
    /** @var array of hidden fields in the form*/
    var $hiddenFields;
    /** @var template generation object*/
    var $templateObject;
    /**
     * @var array $fields fields in the form
     */
    function __construct($fields, $templateObject=null){
        $this->fields = array();
        $this->hiddenFields = array();
        foreach($fields as $key => $field){
            if (get_class($field) != 'Library\Php\Form\FieldHidden') {
                $this->fields[] = $field;
            } else {
                $this->hiddenFields[] = $field;
            }
        }
        if($templateObject)
        $this->templateObject = $templateObject;
        else
        $this->templateObject = new Templates\Standard();
    }

    /**
     * @param string $button submit button caption
     * @param string $action url where the form should be submited.
     * @return string form HTML
     */
    function generateForm($button, $action='', $uniqueName=''){
        global $libPhpFormStandardCounter;

        if($libPhpFormStandardCounter)
        $libPhpFormStandardCounter++;
        else
        $libPhpFormStandardCounter = 1;

        $answer = '';

        $uniqueName = "lib_php_form_standard_".$libPhpFormStandardCounter."_".$uniqueName;

        $resetStr = '';
        foreach($this->fields as $key => $field){
            $resetStr .= '
        '.$uniqueName.'_reset(\''.$field->name.'\');
      ';
        }




        $hiddenFields = '';
        foreach($this->hiddenFields as $key => $field)
        if (get_class($field) == 'Library\Php\Form\FieldHidden') {
            $hiddenFields .= $field->genHtml('', $uniqueName.'_field_'.$field->name);
        }

        $answer .= '
<form onsubmit="LibDefault.formBeforePost(this, \''.$uniqueName.'\')" id="'.$uniqueName.'" enctype="multipart/form-data" method="post" action="'.$action.'">
  '.$this->templateObject->generateForm($button, $action, $uniqueName, $this->fields).'

  <div>
'.$hiddenFields.'
    <input type="hidden" name="spec_security_code" value="'.md5(date("Y-m-d")).'" />
    <input type="hidden" name="spec_rand_name" value="'.$uniqueName.'" />
    <div class="clear"></div>
  </div>        
</form>      

';

        return $answer;
    }

    /**
     * @param string $table database table name
     * @param array $additionalValues array of additional values, that should be writen to database.
     */
    function writeToDatabase($table, $additionalValues = null){
        if(sizeof($this->fields > 0) && !$this->getErrors()){
            $sql = 'insert into '.$table.' set ';
            $first = true;
            foreach($this->fields as $key => $field){
                if($field->dbField){
                    if(!$first)
                    $sql .= ', ';
                    $sql .= "`".$field->dbField."` = '".mysql_real_escape_string($field->postedValue())."' ";
                    $first = false;
                }
            }

            foreach($this->hiddenFields as $key => $field){
                if($field->dbField){
                    if(!$first)
                    $sql .= ', ';
                    $sql .= "`".$field->dbField."` = '".mysql_real_escape_string($field->postedValue())."' ";
                    $first = false;
                }
            }


            if($additionalValues)
            foreach($additionalValues as $key => $additionalValue){
                if(!$first)
                $sql .= ', ';


                $sql .= "`".mysql_real_escape_string($key)."` = '".mysql_real_escape_string($additionalValue)."' ";
                $first = false;
            }


            if(!$first){ //if exist fields
                $rs = mysql_query($sql);
                if(!$rs){
                    trigger_error($sql." ".mysql_error());
                    return false;
                }else{
                    return mysql_insert_id();
                }
            }
        }
    }

    /**
     * @param string $table database table name
     * @param int $id_field id of field, that should be updated
     * @param array $additionalValues array of additional values, that should be written to database
     */
    function updateDatabase($table, $id_field, $id, $additionalValues = null){
        if(sizeof($this->fields > 0) && !$this->getErrors()){
            $sql = 'update '.$table.' set ';

            $first = true;
            foreach($this->fields as $key => $field){
                if($field->dbField){
                    if(!$first)
                    $sql .= ', ';
                    $sql .= "`".$field->dbField."` = '".mysql_real_escape_string($field->postedValue())."' ";
                    $first = false;
                }
            }

            foreach($this->hiddenFields as $key => $field){
                if($field->dbField){
                    if(!$first)
                    $sql .= ', ';
                    $sql .= "`".$field->dbField."` = '".mysql_real_escape_string($field->postedValue())."' ";
                    $first = false;
                }
            }

            if($additionalValues)
            foreach($additionalValues as $key => $additionalValue){
                if(!$first)
                $sql .= ', ';

                $sql .= "`".mysql_real_escape_string($key)."` = '".mysql_real_escape_string($additionalValue)."' ";
                $first = false;
            }


            $sql .= " where `".$id_field."` = '".mysql_real_escape_string($id)."' ";

            if(!$first){
                $rs = mysql_query($sql);
                if(!$rs)
                trigger_error($sql." ".mysql_error());
            }
        }
    }


    /**
     *  @return array errors with key's as fields names
     */
    function getErrors(){
        if($this->errors == null){
            $this->errors = array();
            foreach($this->fields as $key => $field){
                $error = $field->getError();
                if($error && $error !== true)
                $this->errors[$field->name] = $error;
                if ($error && $error === true)
                $this->errors[$field->name] = '';
            }
        }

        return $this->errors;

    }
    /**
     * Simple way to detect spam. It is possible way to write speciffic software to hack this protection.
     * @return bool true if security code validation is failed and false othervise
     */
    function detectSpam(){
        $answer = false;
        if(!isset($_POST['spec_security_code']) || $_POST['spec_security_code'] != md5(date("Y-m-d")) && $_POST['spec_security_code'] != md5(date('Y-m-d', strtotime(date("Y-m-d")." -1 day"))))
        $answer = true;
        return $answer;
    }

    /**
     * Here is shoosen post method instead of ajax. So, this function generates javascript code, that loaded into iframe, marks incorrect fields.
     * @param array errors in submited form. This array can be returned by function $this->getErrors() and updated or appended by specific code.
     * @return string html/javascript
     */
    function generateErrorAnswer($errors, $globalError = null){
        $answer = "
<html>
  <head>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=".\Ip\Config::getRaw('CHARSET')."\" />
  </head>
  <body>
    <script type=\"text/javascript\">
      //<![CDATA[
      var errors = new Array();
      var new_fields = new Array();
      var fields = new Array();
      ";

        foreach($errors as $key => $error){

            $answer .= "
       var error = ['".addslashes($key)."', '".addslashes($error)."'];
       errors.push(error);
       ";
        }

        foreach($this->fields as $key => $field){
            if($field->renewRequired())
            $answer .= "
       var new_field = ['".addslashes($field->name)."', '".
            str_replace("\r", "", str_replace("\n", "' + \n '", str_replace("'", "\\'", $field->genHtml('', $_REQUEST['spec_rand_name'].'_field_'.$field->name))))
            ."'];
       new_fields.push(new_field);
        ";
        }

        foreach($this->fields as $key => $field){
            $answer .= "
       fields.push('".addslashes($field->name)."');
        ";
        }


        if($globalError !== null){
            $answer .= " var global_error =  '".str_replace("\r", "", str_replace("\n", "' + \n '", str_replace("'", "\\'",$globalError)))."'; ";
        }

        $answer .=  "
      //]]>
    </script>
  </body>
</html>
    ";
        return $answer;
    }

}








