<?php
/**
 * @package   ImpressPages
 */

namespace Ip\Internal\Grid\Model;


class Config
{
    protected $config = null;

    /**
     * @var Field[]
     */
    protected $fieldObjects = null;

    public function __construct($config)
    {
        $this->config = $config;

        if (empty($this->config['table'])) {
            throw new \Ip\Exception('\'table\' configuration value missing.');
        }

        if (empty($this->config['fields'])) {
            $this->config['fields'] = $this->getTableFields($this->config['table']);
        }

        if (empty($this->config['idField'])) {
            $this->config['idField'] = 'id';
        }

        if (empty($this->config['pageSize'])) {
            $this->config['pageSize'] = 10;
        }

        if (empty($this->config['pagerSize'])) {
            $this->config['pagerSize'] = 10;
        }


        foreach ($this->config['fields'] as &$field) {
            if (empty($field['type'])) {
                $field['type'] = 'Text';
            }
        }
    }


    public function pageVariableName()
    {
        if (!empty($this->config['pageVariableName'])) {
            return $this->config['pageVariableName'];
        }
        return 'page';
    }

    /**
     * Get sql part to be used in where clause
     * @return string
     */
    public function filter()
    {
        if (!empty($this->config['filter'])) {
            return $this->config['filter'];
        }
        return '1';
    }

    public function deleteWarning()
    {
        if (!empty($this->config['deleteWarning'])) {
            return $this->config['deleteWarning'];
        }
        return __('Are you sure you want to delete?', 'Ip-admin', FALSE);
    }

    public function actions()
    {
        if (!empty($this->config['actions'])) {
            return $this->config['actions'];
        }
        return array();
    }

    public function beforeDelete()
    {
        if (empty($this->config['beforeDelete'])) {
            return FALSE;
        }
        return $this->config['beforeDelete'];
    }

    public function afterDelete()
    {
        if (empty($this->config['afterDelete'])) {
            return FALSE;
        }
        return $this->config['afterDelete'];
    }

    public function beforeUpdate()
    {
        if (empty($this->config['beforeUpdate'])) {
            return FALSE;
        }
        return $this->config['beforeUpdate'];
    }

    public function afterUpdate()
    {
        if (empty($this->config['afterUpdate'])) {
            return FALSE;
        }
        return $this->config['afterUpdate'];
    }


    public function beforeCreate()
    {
        if (empty($this->config['beforeCreate'])) {
            return FALSE;
        }
        return $this->config['beforeCreate'];
    }

    public function afterCreate()
    {
        if (empty($this->config['afterCreate'])) {
            return FALSE;
        }
        return $this->config['afterCreate'];
    }


    public function beforeMove()
    {
        if (empty($this->config['beforeMove'])) {
            return FALSE;
        }
        return $this->config['beforeMove'];
    }

    public function afterMove()
    {
        if (empty($this->config['afterMove'])) {
            return FALSE;
        }
        return $this->config['afterMove'];
    }

    public function preventAction()
    {
        if (empty($this->config['preventAction'])) {
            return FALSE;
        }
        return $this->config['preventAction'];
    }


    /**
     * @param $field
     * @return \Ip\Internal\Grid\Model\Field
     */
    public function fieldObject($field)
    {
        if (empty($field['type'])) {
            $field['type'] = 'Text';
        }
        $class = '\\Ip\\Internal\\Grid\\Model\\Field\\' . $field['type'];
        if (!class_exists($class)) {
            if (class_exists($field['type'])) {
                $class = $field['type']; //type is full class name
            } else {
                throw new \Ip\Exception('Class doesn\'t exist "' . esc($field['type']) . '"');
            }

        }
        $fieldObject = new $class($field, $this->config);
        return $fieldObject;
    }

    public function fields()
    {
        return $this->config['fields'];
    }

    public function allowCreate()
    {
        return !array_key_exists('allowCreate', $this->config) || $this->config['allowCreate'];
    }

    public function allowSearch()
    {
        return !array_key_exists('allowSearch', $this->config) || $this->config['allowSearch'];
    }

    public function allowUpdate()
    {
        return !array_key_exists('allowUpdate', $this->config) || $this->config['allowUpdate'];
    }

    public function allowSort()
    {
        if (!empty($this->config['sortField'])) {
            if (isset($this->config['allowSort'])) {
                return $this->config['allowSort'];
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function allowDelete()
    {
        return !array_key_exists('allowDelete', $this->config) || $this->config['allowDelete'];
    }

    public function pageSize()
    {
        return $this->config['pageSize'];
    }

    public function pagerSize()
    {
        return $this->config['pagerSize'];
    }

    public function idField()
    {
        return $this->config['idField'];
    }

    public function tableName()
    {
        return ipTable(str_replace("`", "", $this->config['table']));
    }

    public function joinQuery()
    {
        if (empty($this->config['joinQuery'])) {
            return false;
        }
        return trim($this->config['joinQuery'], '`');
    }

    public function rawTableName()
    {
        return $this->config['table'];
    }

    public function sortField()
    {
        if (empty($this->config['sortField'])) {
            return false;
        }
        return trim($this->config['sortField'], '`');
    }

    public function sortDirection()
    {
        if (empty($this->config['sortDirection'])) {
            return false;
        }
        if ($this->config['sortDirection'] == 'desc') {
            return 'desc';
        } else {
            return 'asc';
        }

    }

    public function createPosition()
    {
        if (!empty($this->config['createPosition']) && $this->config['createPosition'] == 'bottom') {
            return 'bottom';
        }
        return 'top';

    }

    public function getTitle()
    {
        if (empty($this->config['title'])) {
            return '';
        }
        return $this->config['title'];
    }


    protected function getTableFields($tableName)
    {
        $sql = "SHOW COLUMNS FROM " . $this->tableName() . " " . $this->config->joinQuery() . " ";

        $fields = ipDb()->fetchColumn($sql);

        $result = array();
        foreach ($fields as $fieldName) {
            $result[] = array(
                'label' => $fieldName,
                'field' => $fieldName
            );
        }

        return $result;
    }

    public function layout()
    {
        if (empty($this->config['layout'])) {
            return 'Ip/Internal/Grid/view/layout.php';
        }
        return $this->config['layout'];
    }

    public function updateFilter()
    {
        if (empty($this->config['updateFilter'])) {
            return false;
        }
        return $this->config['updateFilter'];

    }
}
