<?php
/**
 * @package ImpressPages
 *
 */

namespace Ip;


class Form
{
    const METHOD_POST = 'post';
    const METHOD_GET = 'get';
    const ENVIRONMENT_ADMIN = 'admin';
    const ENVIRONMENT_PUBLIC = 'public';

    /**
     * @var $pages Page[]
     */
    protected $pages;
    protected $method;
    protected $action;
    protected $attributes;
    protected $classes;
    protected $environment;

    public function __construct()
    {
        $this->fieldsets = array();
        $this->method = self::METHOD_POST;
        $this->action = \Ip\Internal\UrlHelper::getCurrentUrl();
        $this->pages = array();
        $this->attributes = array();
        $this->classes = array('ipModuleForm' => 1, 'ipsModuleForm' => 1);
        if (ipRequest()->getControllerType() == \Ip\Request::CONTROLLER_TYPE_ADMIN) {
            $this->setEnvironment(self::ENVIRONMENT_ADMIN);
        } else {
            $this->setEnvironment(self::ENVIRONMENT_PUBLIC);
        }


        $this->addCsrfCheck();
    }

    /**
     * Set form environment. Depending on that public or admin translations and layout will be chosen.
     * ImpressPages tries to detect environment automatically based on current controller. You can set manually the right mode if needed.
     * @param $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Add securityToken field
     */
    public function addCsrfCheck()
    {
        $tokenField = new \Ip\Form\Field\Csrf();
        $tokenField->setName('securityToken');
        $this->addField($tokenField);
    }

    /**
     * Remove securityToken field
     */
    public function removeCsrfCheck()
    {
        $this->removeField('securityToken');
    }

    /**
     * Remove field from form
     * @param string $fieldName
     * @return int removed fields count
     */
    public function removeField($fieldName)
    {
        $count = 0;
        foreach ($this->pages as $key => $page) {
            $count += $page->removeField($fieldName);
        }
        return $count;
    }

    /**
     *
     * Check if data passes form validation rules
     * @param array $data - post data from user or other source.
     * @return array errors. Array key - error field name. Value - error message. Empty array means there are no errors.
     */
    public function validate($data)
    {
        $fields = $this->getFields();
        $errors = array();
        foreach ($fields as $field) {
            $error = $field->validate($data, $field->getName());
            if ($error !== false) {
                $errors[$field->getValidationInputName()] = $error;
            }
        }
        return $errors;
    }

    /**
     *
     * Filter data array. Return only those records that are expected according to form field names.
     * @param array $data
     * @return array
     */
    public function filterValues($data)
    {
        $answer = array();
        $fields = $this->getFields();
        foreach ($fields as $field) {
            if (isset($data[$field->getName()])) {
                $answer[$field->getName()] = $data[$field->getName()];
            }
        }
        return $answer;
    }

    /**
     * @param \Ip\Form\Page $page
     */
    public function addPage(\Ip\Form\Page $page)
    {
        $this->pages[] = $page;
    }

    /**
     * @param \Ip\Form\Fieldset $fieldset
     */
    public function addFieldset(\Ip\Form\Fieldset $fieldset)
    {
        if (count($this->pages) == 0) {
            $this->addPage(new Page());
        }
        end($this->pages)->addFieldset($fieldset);
    }

    /**
     *
     * Add field to last fielset. Create fieldset if does not exist.
     * @param Field $field
     */
    public function addField(Form\Field $field)
    {
        if (count($this->pages) == 0) {
            $this->addPage(new \Ip\Form\Page($this));
        }
        end($this->pages)->addField($field);
    }

    /**
     * Return all pages
     * @return \Ip\Form\Page[]
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     *
     * Set post method.
     * @param string $method Use \Ip\Form::METHOD_POST or \Ip\Form::METHOD_GET
     * @throws Exception
     */
    public function setMethod($method)
    {
        switch ($method) {
            case self::METHOD_POST:
            case self::METHOD_GET:
                $this->method = $method;
                break;
            default:
                throw new Exception ('Unknown method "' . $method . '"', Exception::INCORRECT_METHOD_TYPE);
        }
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }


    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param View $view
     * @return string
     */
    public function render(\Ip\View $view = null)
    {
        if (!$view) {
            if ($this->getEnvironment() == self::ENVIRONMENT_ADMIN) {
                $view = \Ip\View::create('Form/adminView/form.php');
            } else {
                $view = \Ip\View::create('Form/publicView/form.php');
            }
        }
        $view->setVariables(
            array(
                'form' => $this,
                'environment' => $this->getEnvironment()
            )
        );

        $fields = $this->getFields();
        foreach($fields as $field) {
            $field->setEnvironment($this->getEnvironment());
        }

        return $view->render();
    }


    /**
     * @return \Ip\Form\Fieldset[]
     */
    public function getFieldsets()
    {
        $pages = $this->getPages();
        $fieldsets = array();
        foreach ($pages as $page) {
            $fieldsets = array_merge($fieldsets, $page->getFieldsets());
        }
        return $fieldsets;
    }

    /**
     * @return \Ip\Form\Field[]
     */
    public function getFields()
    {
        $pages = $this->getPages();
        $fields = array();
        foreach ($pages as $page) {
            $fields = array_merge($fields, $page->getFields());
        }
        return $fields;
    }

    /**
     * @param $name
     * @return \Ip\Form\Field
     */
    public function getField($name)
    {
        $allFields = $this->getFields();
        foreach ($allFields as $key => $field) {
            if ($field->getName() == $name) {
                return $field;
            }
        }
        return false;
    }

    /**
     *
     * Add attribute to the form
     * @param string $name
     * @param string $value
     */
    public function addAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function removeAttribute($name)
    {
        unset($this->attributes[$name]);
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttributesStr()
    {
        $answer = '';
        foreach ($this->getAttributes() as $attributeKey => $attributeValue) {
            $answer .= ' ' . htmlspecialchars($attributeKey) . '="' . htmlspecialchars($attributeValue) . '"';
        }
        return $answer;
    }

    /**
     *
     * Add CSS class to the form
     * @param string $cssClass
     */
    public function addClass($cssClass)
    {
        $this->classes[$cssClass] = 1;
    }

    public function removeClass($cssClass)
    {
        unset($this->classes[$cssClass]);
    }

    public function getClasses()
    {
        return array_keys($this->classes);
    }

    public function getClassesStr()
    {
        $answer = '';
        foreach ($this->getClasses() as $class) {
            $answer .= ' ' . $class;
        }
        return 'class="' . $answer . '"';
    }

    /**
     *
     * Store form data to the database
     * Keep notice, that this method does not do the validation of data.
     * So please validate submited data before writing to the database.*
     * @param string $table where data should be stored
     * @param array $data posted or in the other way collected data
     * @param array $additionalData additional data that hasn't been posted, but is required to be inserted
     * @return bool|int
     * @throws \Exception
     */
    public function writeToDatabase($table, $data, $additionalData)
    {
        $errors = $this->validate($data);
        if (!empty($errors)) {
            throw new \Exception("Data does not validate");
        }


        if (count($this->getFields()) == 0 && count($additionalValues) == 0) {
            return false;
        }

        $sql = 'INSERT INTO `' . ip_deprecated_mysql_real_escape_string($table) . '` SET ';
        $first = true;
        foreach ($this->getFields() as $field) {
            if ($field->getDbField()) {
                if (!$first) {
                    $sql .= ', ';
                }
                if (!isset($data[$field->getDbField()])) {
                    $sqlValue = 'NULL';
                } else {
                    $sqlValue = "'" . ip_deprecated_mysql_real_escape_string($data[$field->getDbField()]) . "'";
                }
                $sql .= "`" . ip_deprecated_mysql_real_escape_string($field->getDbField()) . "` = " . $sqlValue . " ";
                $first = false;
            }
        }


        if ($additionalData) {
            foreach ($additionalData as $key => $additionalValue) {
                if (!$first) {
                    $sql .= ', ';
                }
                if ($additionalValue === null) {
                    $sqlValue = 'NULL';
                } else {
                    $sqlValue = "'" . ip_deprecated_mysql_real_escape_string($additionalValue) . "'";
                }
                $sql .= "`" . ip_deprecated_mysql_real_escape_string($key) . "` = " . $sqlValue . " ";
                $first = false;

            }
        }


        if ($first) { //if exist fields
            return false;
        }

        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs) {
            throw new \Exception($sql . " " . ip_deprecated_mysql_error());
        } else {
            return ip_deprecated_mysql_insert_id();
        }

    }


    /**
     *
     * Update record in the database.
     * Keep notice, that this method does not do the validation of data.
     * So please validate submited data before writing to the database.
     *
     * @param string $table table name
     * @param string $id_field primary key field in the table
     * @param mixed $id id of record that needs to be updated
     * @param array $data posted data
     * @param array $additionalData additional data you would like to store in the same row
     * @return bool
     * @throws \Exception
     */
    public function updateDatabase($table, $id_field, $id, $data, $additionalData = array())
    {

        if (count($this->getFields()) == 0 && count($additionalData) == 0) {
            return false;
        }


        $sql = 'UPDATE `' . ip_deprecated_mysql_real_escape_string($table) . '` SET ';

        $first = true;
        foreach ($this->getFields() as $field) {
            if ($field->getDbField() && isset($data[$field->getDbField()])) {
                if (!$first) {
                    $sql .= ', ';
                }
                $sql .= "`" . ip_deprecated_mysql_real_escape_string(
                        $field->getDbField()
                    ) . "` = '" . ip_deprecated_mysql_real_escape_string($data[$field->getDbField()]) . "' ";
                $first = false;
            }
        }


        if ($additionalData) {
            foreach ($additionalData as $key => $additionalValue) {
                if (!$first) {
                    $sql .= ', ';
                }
                if ($additionalValue === null) {
                    $sqlValue = 'NULL';
                } else {
                    $sqlValue = "'" . ip_deprecated_mysql_real_escape_string($additionalValue) . "'";
                }
                $sql .= "`" . ip_deprecated_mysql_real_escape_string($key) . "` = " . $sqlValue . " ";
                $first = false;

            }
        }

        $sql .= " WHERE `" . ip_deprecated_mysql_real_escape_string(
                $id_field
            ) . "` = '" . ip_deprecated_mysql_real_escape_string($id) . "' ";

        if ($first) {
            return false;
        }

        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs) {
            throw new \Exception($sql . " " . ip_deprecated_mysql_error());
        }
    }

    public function __toString()
    {
        return $this->render();
    }


}