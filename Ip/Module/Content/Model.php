<?php
/**
 * @package ImpressPages

 *
 */
namespace Ip\Module\Content;



class Model{
    static private $widgetObjects = null;
    const DEFAULT_LAYOUT = 'default';
    const WIDGET_DIR = 'Widget';

    public static function generateBlock($blockName, $revisionId, $managementState, $exampleContent = '') {
        $widgets = self::getBlockWidgetRecords($blockName, $revisionId);

        $widgetsHtml = array();
        foreach ($widgets as $widget) {
            try {
                $widgetsHtml[] = self::_generateWidgetPreview($widget, $managementState);
            } catch (Exception $e) {
                if ($e->getCode() == Exception::UNKNOWN_WIDGET) {
                    $viewData = array (
                   'widgetRecord' => $widget,
                   'managementState' => $managementState
                    );
                    $widgetsHtml[] = \Ip\View::create('view/unknown_widget.php', $viewData)->render();
                } else {
                    throw new Exception('Error when generating widget preview', null, $e);
                }
            }
        }

        $data = array (
            'widgetsHtml' => $widgetsHtml,
            'blockName' => $blockName,    		
            'revisionId' => $revisionId,
            'managementState' => $managementState,
            'exampleContent' => $exampleContent
        );
        $answer = \Ip\View::create('view/block.php', $data)->render();
        return $answer;
    }

    public static function initManagementData() {

        $tmpWidgets = Model::getAvailableWidgetObjects();
        $tmpWidgets = Model::sortWidgets($tmpWidgets);
        $widgets = array();
        foreach($tmpWidgets as $key => $widget) {
            if (!$widget->getUnderTheHood()) {
                $widgets[$key] = $widget;
            }
        }

        $revisions = \Ip\Revision::getPageRevisions(ipGetCurrentZone()->getName(), ipGetCurrentPage()->getId());

        $managementUrls = array();
        foreach($revisions as $revision) {
            $managementUrls[] = ipGetCurrentPage()->getLink().'&cms_revision='.$revision['revisionId'];
        }

        $revision = \Ip\ServiceLocator::getContent()->getRevision();

        $manageableRevision = isset($revisions[0]['revisionId']) && ($revisions[0]['revisionId'] == $revision['revisionId']);

        $page = ipGetCurrentPage();

        $data = array (
            'widgets' => $widgets,
            'page' => $page,
            'revisions' => $revisions,
            'currentRevision' => $revision,
            'managementUrls' => $managementUrls,
            'manageableRevision' => $manageableRevision
        );

        $controlPanelHtml = \Ip\View::create('view/control_panel.php', $data)->render();

        $widgetControlsHtml = \Ip\View::create('view/widget_controls.php', $data)->render();

        $saveProgressHtml = \Ip\View::create('view/save_progress.php', $data)->render();
        $data = array (
            'status' => 'success',
            'controlPanelHtml' => $controlPanelHtml,
            'widgetControlsHtml' => $widgetControlsHtml,
            'saveProgressHtml' => $saveProgressHtml,
            'manageableRevision' => $manageableRevision
        );

        return $data;
    }

    public static function sortWidgets($widgets) {
        $priorities = self::_getPriorities();
        $sortedWidgets = array();
        $unsortedWidgets = array();
        foreach ($widgets as $widgetKey => $widget) {
            if (isset($priorities[$widget->getName()])) {
                $position = $priorities[$widget->getName()];
                $sortedWidgets[(int)$position] = $widget;
            } else {
                $unsortedWidgets[] = $widget;
            }
        }
        ksort($sortedWidgets);
        $answer = array();
        foreach ($sortedWidgets as $widgetKey => $widget) {
            $answer[$widget->getName()] = $widget;
        }

        foreach ($unsortedWidgets as $widgetKey => $widget) {
            $answer[$widget->getName()] = $widget;
        }

        return $answer;
    }

    private static function _getPriorities() {
        $sql = "
        SELECT
            *
        FROM
            `".DB_PREF."m_developer_widget_sort`
        WHERE
            1
        ORDER BY
            `priority` asc
        ";
        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs) {
            throw new Exception('Can\'t add widget '.$sql.' '.ip_deprecated_mysql_error(), Exception::DB);
        }

        $answer = array();

        while ($lock = ip_deprecated_mysql_fetch_assoc($rs)) {
            $answer[$lock['widgetName']] = $lock['priority'];
        }
        return $answer;
    }

    public static function generateWidgetPreviewFromStaticData($widgetName, $data, $layout = null) {
        if ($layout == null) {
            $layout = self::DEFAULT_LAYOUT;
        }
        $widgetObject = self::getWidgetObject($widgetName);
        if (!$widgetObject) {
            $backtrace = debug_backtrace();
            if(isset($backtrace[0]['file']) && $backtrace[0]['line']) {
                $source = ' (Error source: '.$backtrace[0]['file'].' line: '.$backtrace[0]['line'].' ) ';
            } else {
                $source = '';
            }

            throw new Exception('Widget ' . $widgetName . ' does not exist. '.$source, Exception::UNKNOWN_WIDGET);
        }
        
        $previewHtml = $widgetObject->previewHtml(null, $data, $layout);
        
        $widgetRecord = array (
            'widgetId' => null,
            'name' => $widgetName,
            'layout' => $layout,
            'data' => $data,
            'created' => time(),
            'recreated' => time(),
            'predecessor' => null,
        
            'instanceId' => null,
            'revisionId' => null,
            'position' => null,
            'blockName' => null,
            'visible' => 1,
            'deleted' => null
        );
        return self::_generateWidgetPreview($widgetRecord, FALSE);
        /*
        $data = array (
            'html' => $previewHtml,
            'widgetRecord' => $data, //static data used instead of widget record from the database
            'managementState' => FALSE
        );
        $answer = \Ip\View::create('view/widget_preview.php', $data)->render();
        return $answer;*/
    }


    public static function generateWidgetPreview($instanceId, $managementState) {
        $widgetRecord = self::getWidgetFullRecord($instanceId);
        return self::_generateWidgetPreview($widgetRecord, $managementState);
    }

    private static function _generateWidgetPreview($widgetRecord, $managementState) {
        //check if we don't need to recreate the widget
        $themeChanged = \Ip\Internal\DbSystem::getSystemVariable('theme_changed');
        if ($themeChanged > $widgetRecord['recreated']) {
            $widgetData = $widgetRecord['data'];
            if (!is_array($widgetData)) {
                $widgetData = array();
            }
            $widgetObject = self::getWidgetObject($widgetRecord['name']);
            if (!$widgetObject) {
                throw new Exception('Widget does not exist. Widget name: '.$widgetRecord['name'], Exception::UNKNOWN_WIDGET);
            }

            $newData = $widgetObject->recreate($widgetRecord['instanceId'], $widgetData);
            self::updateWidget($widgetRecord['widgetId'], array('recreated' => time(), 'data' =>  $newData));
            $widgetRecord = self::getWidgetFullRecord($widgetRecord['instanceId']);
        }
        
        
        
        $widgetData = $widgetRecord['data'];
        if (!is_array($widgetData)) {
            $widgetData = array();
        }

        
        $widgetObject = self::getWidgetObject($widgetRecord['name']);
        
        if (!$widgetObject) {
            throw new Exception('Widget does not exist. Widget name: '.$widgetRecord['name'], Exception::UNKNOWN_WIDGET);
        }

        $previewHtml = $widgetObject->previewHtml($widgetRecord['instanceId'], $widgetData, $widgetRecord['layout']);

        if ($managementState) {
            $previewHtml = preg_replace("/".str_replace(array('/', ':'), array('\\/', '\\:'), ipGetConfig()->baseUrl(''))."([^\\\"\\'\>\<\?]*)?\?([^\\\"]*)(?=\\\")/", '$0&cms_action=manage', $previewHtml);
            $previewHtml = preg_replace("/".str_replace(array('/', ':'), array('\\/', '\\:'), ipGetConfig()->baseUrl(''))."([^\\\"\\'\>\<\?]*)?(?=\\\")/", '$0?cms_action=manage', $previewHtml);
        }
        
        $data = array (
            'html' => $previewHtml,
            'widgetRecord' => $widgetRecord,
            'managementState' => $managementState
        );
        $answer = \Ip\View::create('view/widget_preview.php', $data)->render();
        return $answer;
    }

    public static function generateWidgetManagement($instanceId) {
        $widgetRecord = self::getWidgetFullRecord($instanceId);
        return self::_generateWidgetManagement($widgetRecord);
    }

    private static function _generateWidgetManagement($widgetRecord) {
        $widgetData = $widgetRecord['data'];

        if (!is_array($widgetData)) {
            $widgetData = array();
        }

        $widgetObject = self::getWidgetObject($widgetRecord['name']);

        if (!$widgetObject) {
            throw new Exception('Widget does not exist. Widget name: '.$widgetRecord['name'], Exception::DB);
        }

        $managementHtml = $widgetObject->managementHtml($widgetRecord['instanceId'], $widgetData, $widgetRecord['layout']);
        $widgetRecord['data'] = $widgetObject->dataForJs($widgetRecord['data']); 
        $data = array (
            'managementHtml' => $managementHtml,
            'widgetRecord' => $widgetRecord,
            'layouts' => $widgetObject->getLayouts(),
            'widgetTitle' => $widgetObject->getTitle()
        );
        $answer = \Ip\View::create('view/widget_management.php', $data)->render();

        return $answer;
    }


    public static function getBlockWidgetRecords($blockName, $revisionId){
        $sql = "
            SELECT * 
            FROM
                `".DB_PREF."m_content_management_widget_instance` i,
                `".DB_PREF."m_content_management_widget` w
            WHERE
                i.deleted is NULL AND
                i.widgetId = w.widgetId AND
                i.blockName = '".ip_deprecated_mysql_real_escape_string($blockName)."' AND
                i.revisionId = ".(int)$revisionId."
            ORDER BY `position` ASC
        ";
        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new Exception('Can\'t get widgets '.$sql.' '.ip_deprecated_mysql_error(), Exception::DB);
        }

        $answer = array();

        while ($lock = ip_deprecated_mysql_fetch_assoc($rs)) {
            $lock['data'] = json_decode($lock['data'], true);
            $answer[] = $lock;
        }

        return $answer;
    }




    public static function duplicateRevision($oldRevisionId, $newRevisionId) {
        $sql = "
            SELECT * 
            FROM
                `".DB_PREF."m_content_management_widget_instance` i
            WHERE
                i.revisionId = ".(int)$oldRevisionId." AND
                i.deleted IS NULL
            ORDER BY `position` ASC
        ";    

        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new Exception('Can\'t get revision data '.$sql.' '.ip_deprecated_mysql_error(), Exception::DB);
        }

        while ($lock = ip_deprecated_mysql_fetch_assoc($rs)) {

            $dataSql = '';

            foreach ($lock as $key => $value) {
                if ($key != 'revisionId' && $key != 'instanceId' ) {
                    if ($dataSql != '') {
                        $dataSql .= ', ';
                    }
                    if ($value !== null) {
                        $dataSql .= " `".$key."` = '".ip_deprecated_mysql_real_escape_string($value)."' ";
                    } else {
                        $dataSql .= " `".$key."` = NULL ";
                    }

                }
            }

            $insertSql = "
                INSERT INTO
                    `".DB_PREF."m_content_management_widget_instance`
                SET
                    ".$dataSql.",
                    `revisionId` = ".(int)$newRevisionId."                     
                    
            ";    

            $insertRs = ip_deprecated_mysql_query($insertSql);
            if (!$insertRs){
                throw new Exception('Can\'t get revision data '.$insertSql.' '.ip_deprecated_mysql_error(), Exception::DB);
            }
        }

    }

    public static function getAvailableWidgetObjects() {

        if (self::$widgetObjects !== null) {
            return self::$widgetObjects;
        }

        self::$widgetObjects = ipDispatcher()->filter('contentManagement.collectWidgets', array());

        return self::$widgetObjects;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $widgetName
     * @return \Ip\Module\Content\Widget
     */
    public static function getWidgetObject($widgetName) {
        $widgetObjects = self::getAvailableWidgetObjects();

        if (isset($widgetObjects[$widgetName])) {
            return $widgetObjects[$widgetName];
        } else {
            return false;
        }

    }

    public static function getWidgetRecord($widgetId) {
        $sql = "
            SELECT * FROM `".DB_PREF."m_content_management_widget`
            WHERE `widgetId` = ".(int)$widgetId."
        ";    

        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new Exception('Can\'t find widget '.$sql.' '.ip_deprecated_mysql_error(), Exception::DB);
        }

        if ($lock = ip_deprecated_mysql_fetch_assoc($rs)) {
            $lock['data'] = json_decode($lock['data'], true);
            return $lock;
        } else {
            return false;
        }
    }


    /**
     *
     * getWidgetFullRecord differ from getWidgetRecord by including the information from m_content_management_widget_instance table.
     * @param int $instanceId
     * @throws Exception
     */
    public static function getWidgetFullRecord($instanceId) {
        $sql = "
            SELECT * FROM
                `".DB_PREF."m_content_management_widget_instance` i,
                `".DB_PREF."m_content_management_widget` w
            WHERE
                i.`instanceId` = ".(int)$instanceId." AND
                i.widgetId = w.widgetId 
        ";    
        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new Exception('Can\'t find widget '.$sql.' '.ip_deprecated_mysql_error(), Exception::DB);
        }

        if ($lock = ip_deprecated_mysql_fetch_assoc($rs)) {
            $lock['data'] = json_decode($lock['data'], true);
            return $lock;
        } else {
            return false;
        }
    }
    
    
    public static function getRevisions($zoneName, $pageId) {
        $sql = "
            SELECT * FROM
                `".DB_PREF."revision` 
            WHERE
                `zoneName` = '".ip_deprecated_mysql_real_escape_string($zoneName)."'
                AND
                `pageId` = ".(int)$pageId."
        ";
        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new Exception('Can\'t get revisions '.$sql.' '.ip_deprecated_mysql_error(), Exception::DB);
        }
        
        $answer = array();

        while ($lock = ip_deprecated_mysql_fetch_assoc($rs)) {
            $answer[] = $lock;
        }

        return $answer;
    }
    

    
    public static function updatePageRevisionsZone($pageId, $oldZoneName, $newZoneName) {
        $sql = "
            UPDATE
                `".DB_PREF."revision`
            SET
                 `zoneName` = '".ip_deprecated_mysql_real_escape_string($newZoneName)."'
            WHERE
                `zoneName` = '".ip_deprecated_mysql_real_escape_string($oldZoneName)."'
                AND
                `pageId` = ".(int)$pageId."
        ";
        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new Exception('Can\'t udpate revisions '.$sql.' '.ip_deprecated_mysql_error(), Exception::DB);
        }
        return ip_deprecated_mysql_affected_rows();
    }
    

    /**
     *
     * Find position of widget in current block
     * @param int $instanceId
     * @return int position of widget or null if widget does not exist
     */
    public static function getInstancePosition($instanceId) {
        $record = Model::getWidgetFullRecord($instanceId);

        $sql = "
            SELECT count(instanceId) as position FROM
                `".DB_PREF."m_content_management_widget_instance` 
            WHERE
                `revisionId` = ".$record['revisionId']." AND
                `blockName` = '".ip_deprecated_mysql_real_escape_string($record['blockName'])."' AND
                `position` < ".$record['position']." AND
                `deleted` IS NULL  
        ";    
        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new Exception('Can\'t find widget '.$sql.' '.ip_deprecated_mysql_error(), Exception::DB);
        }

        if ($lock = ip_deprecated_mysql_fetch_assoc($rs)) {
            return $lock['position'];
        } else {
            return false;
        }

    }

    /**
     *
     * Return float number that will position widget in requested position
     * @param int $instnaceId
     * @param string $blockName
     * @param int $newPosition Real position of widget starting with 0
     */
    private static function _calcWidgetPositionNumber($revisionId, $instanceId, $newBlockName, $newPosition) {
        $allWidgets = self::getBlockWidgetRecords($newBlockName, $revisionId);

        $widgets = array();

        foreach ($allWidgets as $widgetKey => $instance) {
            if ($instanceId === null || $instance['instanaceId'] != $instanceId) {
                $widgets[] = $instance;
            }
        }

        if (count($widgets) == 0) {
            $positionNumber = 0;
        } else {
            if ($newPosition <= 0) {
                $positionNumber = $widgets[0]['position'] - 40;
            } else {
                if ($newPosition >= count($widgets)) {
                    $positionNumber = $widgets[count($widgets) - 1]['position'] + 40;
                } else {
                    $positionNumber = ($widgets[$newPosition - 1]['position'] + $widgets[$newPosition]['position']) / 2;
                }
            }
        }
        return $positionNumber;
    }

    /**
     *
     * Enter description here ...
     * @param int $revisionId
     * @param int $position Real position of widget starting with 0
     * @param string $blockName
     * @param string $widgetName
     * @param string $layout
     * @throws Exception
     */
    public static function createWidget($widgetName, $data, $layout, $predecessor) {
        if ($layout == null) {
            $layout = self::DEFAULT_LAYOUT;
        }
        
        if ($predecessor === null) {
            $predecessorSql = ' NULL ';
        } else {
            $predecessorSql = (int)$predecessor;
        }
        
        $sql = "
          insert into
              ".DB_PREF."m_content_management_widget
          set
              `name` = '".ip_deprecated_mysql_real_escape_string($widgetName)."',
              `layout` = '".ip_deprecated_mysql_real_escape_string($layout)."',
              `created` = ".time().",
              `recreated` = ".time().",
              `data` = '".ip_deprecated_mysql_real_escape_string(json_encode(\Ip\Internal\Text\Utf8::checkEncoding($data)))."',
              `predecessor` = ".$predecessorSql."
              ";

        $rs = ip_deprecated_mysql_query($sql);

        if (!$rs) {
            throw new Exception('Can\'t create new widget '.$sql.' '.ip_deprecated_mysql_error(), Exception::DB);
        }

        $widgetId = ip_deprecated_mysql_insert_id();

        return $widgetId;

    }


    public static function updateWidget($widgetId, $data) {

        $dataSql = '';

        foreach ($data as $key => $value) {
            if ($dataSql != '') {
                $dataSql .= ', ';
            }

            if ($key == 'data') {
                $dataSql .= " `".$key."` = '".ip_deprecated_mysql_real_escape_string(json_encode(\Ip\Internal\Text\Utf8::checkEncoding($value)))."' ";
            } else {
                $dataSql .= " `".$key."` = '".ip_deprecated_mysql_real_escape_string($value)."' ";
            }
        }


        $sql = "
            UPDATE `".DB_PREF."m_content_management_widget`
            SET
                ".$dataSql."
            WHERE `widgetId` = ".(int)$widgetId."
        ";    

        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new Exception('Can\'t update widget '.$sql.' '.ip_deprecated_mysql_error());
        }

        return true;
    }

    public static function updateInstance($instanceId, $data) {

        $dataSql = '';

        foreach ($data as $key => $value) {
            if ($dataSql != '') {
                $dataSql .= ', ';
            }
            $dataSql .= " `".$key."` = '".ip_deprecated_mysql_real_escape_string($value)."' ";
        }


        $sql = "
            UPDATE `".DB_PREF."m_content_management_widget_instance`
            SET
                ".$dataSql."
            WHERE `instanceId` = ".(int)$widgetId."
        ";    

        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new Exception('Can\'t update instance '.$sql.' '.ip_deprecated_mysql_error(), Exception::DB);
        }

        return true;
    }

    /**
     * Returns possible layout pages.
     * blank.php and files starting with underscore (for example, _layout.php) are considered hidden.
     *
     * @param string $theme
     * @param bool $includeHidden true - returns all layouts, false - only public layouts
     * @return array layouts (e.g. ['main.php', 'blank.php'])
     */
    public static function getThemeLayouts($theme = null, $includeHidden = false) {
        $themeDir = ipGetConfig()->themeFile('', $theme);

        $files = scandir($themeDir);
        $layouts = array();

        foreach ($files as $filename) {
            if ('php' == strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
                if ($includeHidden) {
                    $layouts[]= $filename;
                } elseif ($filename != 'blank.php' && $filename[0] != '_') {
                    $layouts[]= $filename;
                }
            }
        }

        return $layouts;
    }

    public static function addInstance($widgetId, $revisionId, $blockName, $position, $visible) {

        $positionNumber = Model::_calcWidgetPositionNumber($revisionId, null, $blockName, $position);

        $sql = "
            INSERT INTO `".DB_PREF."m_content_management_widget_instance`
            SET
                `widgetId` = ".(int)$widgetId.",
                `revisionId` = ".(int)$revisionId.",
                `blockName` = '".ip_deprecated_mysql_real_escape_string($blockName)."',
                `position` = '".$positionNumber."', 
                `visible` = ".(int)$visible.",
                `created` = ".(int)time().",
                `deleted` = NULL 
                
        ";    

        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new Exception('Can\'t create instance '.$sql.' '.ip_deprecated_mysql_error(), Exception::DB);
        }

        return ip_deprecated_mysql_insert_id();
    }



    /**
     * 
     * Mark instance as deleted. Instance will be remove completely, when revision will be deleted.
     * @param int $instanceId
     */
    public static function deleteInstance($instanceId) {
        $sql = "
            UPDATE `".DB_PREF."m_content_management_widget_instance`
            SET
                `deleted` = ".(int)time()."
            WHERE
                `instanceId` = ".(int)$instanceId."
        ";    

        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new Exception('Can\'t delete instance '.$sql.' '.ip_deprecated_mysql_error(), Exception::DB);
        }

        return true;
    }
    
    public static function removeRevision($revisionId) {
        $sql = "
            DELETE FROM
                `".DB_PREF."m_content_management_widget_instance` 
            WHERE
                `revisionId` = ".(int)$revisionId."
        ";
        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new Exception('Can\'t remove revision widgets instances '.$sql.' '.ip_deprecated_mysql_error(), Exception::DB);
        }
        
        $sql = "
            DELETE FROM
                `".DB_PREF."revision` 
            WHERE
                `revisionId` = ".(int)$revisionId."
        ";
        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new Exception('Can\'t remove revision '.$sql.' '.ip_deprecated_mysql_error(), Exception::DB);
        }        
    }

    
    public static function removePageRevisions($zoneName, $pageId) {
        $revisions = self::getRevisions($zoneName, $pageId);
        foreach($revisions as $revisionKey => $revision) {
            self::removeRevision($revision['revisionId']);
        } 
        
        self::deleteUnusedWidgets();
    }
    
    

    /**
     * 
     * Each widget might be used many times. That is controlled using instanaces. This method destroys all widgets that has no instances.
     * @throws Exception
     */
    public static function deleteUnusedWidgets() {
    

        $sql = "
            SELECT
                w.widgetId 
            FROM
                `".DB_PREF."m_content_management_widget` w
            LEFT JOIN
                `".DB_PREF."m_content_management_widget_instance` i
            ON
                i.widgetId = w.widgetId
            WHERE
                i.instanceId IS NULL
          
        ";
        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new Exception('Can\'t get unused widgets '.$sql.' '.ip_deprecated_mysql_error(), Exception::DB);
        }
        while ($lock = ip_deprecated_mysql_fetch_assoc($rs)) {
            self::deleteWidget($lock['widgetId']);
        }
    }
    
    /**
     * 
     * Completely remove widget.
     * @param int $widgetId
     */
    public static function deleteWidget($widgetId){
        $widgetRecord = self::getWidgetRecord($widgetId);
        $widgetObject = self::getWidgetObject($widgetRecord['name']);
        
        if ($widgetObject) {
            $widgetObject->delete($widgetId, $widgetRecord['data']);
        }
        
        $sql = "
          DELETE FROM
              `".DB_PREF."m_content_management_widget`
          WHERE
              `widgetId` = ".(int)$widgetId."
        ";
        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new Exception('Can\'t delete widget '.$sql.' '.ip_deprecated_mysql_error(), Exception::DB);
        }
    }

    
    public static function clearCache($revisionId) {

        $revision = \Ip\Revision::getRevision($revisionId);
        $pageContent = Model::generateBlock('main', $revisionId, FALSE);
        
        $html2text = new \Ip\Internal\Text\Html2Text();
        $html2text->set_html($pageContent);
        $pageContentText = $html2text->get_text();
        
        $params = array (
            'cached_html' => $pageContent,
            'cached_text' => $pageContentText
        );
        \Ip\Module\Pages\Db::updatePage($revision['zoneName'], $revision['pageId'], $params);
    }




}