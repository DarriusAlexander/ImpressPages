<?php
/**
 * @package ImpressPages

 *
 */

namespace Ip;

/**
 *
 * View class
 *
 */
class Revision{
    
    public static function getLastRevision($zoneName, $pageId) {
        //ordering by id is required because sometimes two revisions might be created at excatly the same time
        $sql = "
            SELECT * FROM `".DB_PREF."revision`
            WHERE
                `zoneName` = '".ip_deprecated_mysql_real_escape_string($zoneName)."' AND
                `pageId` = '".(int)$pageId."'
            ORDER BY `created` DESC, `revisionId` DESC
            LIMIT 1
        ";    

        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new CoreException('Can\'t find last revision '.$sql.' '.ip_deprecated_mysql_error(), CoreException::DB);
        }

        if ($lock = ip_deprecated_mysql_fetch_assoc($rs)) {
            return $lock;
        } else {
            $revisionId = self::createRevision($zoneName, $pageId, 1);
            return self::getRevision($revisionId);
        }

    }

    public static function getPublishedRevision($zoneName, $pageId) {
        //ordering by id is required because sometimes two revisions might be created at excatly the same time
        $sql = "
            SELECT * FROM `".DB_PREF."revision`
            WHERE
                `zoneName` = '".ip_deprecated_mysql_real_escape_string($zoneName)."' AND
                `pageId` = '".(int)$pageId."' AND
                `published`
            ORDER BY `created` DESC, `revisionId` DESC
            LIMIT 1
        ";    

        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new CoreException('Can\'t find last revision '.$sql.' '.ip_deprecated_mysql_error(), CoreException::DB);
        }

        if ($lock = ip_deprecated_mysql_fetch_assoc($rs)) {
            return $lock;
        } else {
            $revisionId = self::createRevision($zoneName, $pageId, 1);
            return self::getRevision($revisionId);
        }

    }

    public static function getRevision($revisionId) {
        $sql = "
            SELECT * FROM `".DB_PREF."revision`
            WHERE `revisionId` = ".(int)$revisionId."
        ";    

        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new CoreException('Can\'t find revision '.$sql.' '.ip_deprecated_mysql_error(), CoreException::DB);
        }

        if ($lock = ip_deprecated_mysql_fetch_assoc($rs)) {
            return $lock;
        } else {
            return false;
        }

    }


    public static function createRevision ($zoneName, $pageId, $published) {
        $sql = "
            INSERT INTO `".DB_PREF."revision`
            SET
                `zoneName` = '".ip_deprecated_mysql_real_escape_string($zoneName)."',
                `pageId` = '".(int)$pageId."',
                `published` = ".(int)$published.",
                `created` = ".time()."
        ";   

        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new CoreException('Can\'t create new revision '.$sql.' '.ip_deprecated_mysql_error(), CoreException::DB);
        }

        $revisionId = ip_deprecated_mysql_insert_id();

        $eventData = array(
            'revisionId' => $revisionId
        );
        \Ip\ServiceLocator::getDispatcher()->notify(new \Ip\Event(null, 'site.createdRevision', $eventData));



        return $revisionId;
    }

    public static function publishRevision ($revisionId) {
        $revision = self::getRevision($revisionId);
        if (!$revision) {
            return false;
        }

         
        $sql = "
            UPDATE `".DB_PREF."revision`
            SET
                `published` = (revisionId = '".(int)$revisionId."')
            WHERE
                `zoneName` = '".ip_deprecated_mysql_real_escape_string($revision['zoneName'])."'
                AND
                `pageId` = '".(int)$revision['pageId']."'
        ";   

        $rs = ip_deprecated_mysql_query($sql);

        if (!$rs) {
            throw new CoreException("Can't publish revision " . $sql . ' '. ip_deprecated_mysql_error(), CoreException::DB);
        }
        
        $eventData = array(
            'revisionId' => $revisionId,
        );
        \Ip\ServiceLocator::getDispatcher()->notify(new \Ip\Event(null, 'site.publishRevision', $eventData));
        

    }

    public static function duplicateRevision ($oldRevisionId, $zoneName = null, $pageId = null, $published = null) {

        $oldRevision = self::getRevision($oldRevisionId);
        
        if (!$oldRevision) {
            throw new \Ip\CoreException("Can't find old revision: ".$oldRevisionId, \Ip\CoreException::REVISION);
        }
        
        if ($zoneName !== null) {
            $oldRevision['zoneName'] = $zoneName;
        }
        if ($pageId !== null) {
            $oldRevision['pageId'] = $pageId;
        }
        
        $newRevisionId = self::createRevision($oldRevision['zoneName'], $oldRevision['pageId'], 0);

        if ($published !== null) {
            self::publishRevision($newRevisionId);
        }
        
        
        $eventData = array(
            'newRevisionId' => $newRevisionId,
            'basedOn' => $oldRevisionId 
        );
        \Ip\ServiceLocator::getDispatcher()->notify(new \Ip\Event(null, 'site.duplicatedRevision', $eventData));

        return $newRevisionId;
    }


    public static function getPageRevisions($zoneName, $pageId) {
        $sql = "
            SELECT * FROM `".DB_PREF."revision`
            WHERE `pageId` = ".(int)$pageId." AND `zoneName` = '".ip_deprecated_mysql_real_escape_string($zoneName)."'
            ORDER BY `created` DESC, `revisionId` DESC
        ";    

        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new CoreException('Can\'t get page revisions '.$sql.' '.ip_deprecated_mysql_error(), CoreException::DB);
        }

        $answer = array();
        while ($lock = ip_deprecated_mysql_fetch_assoc($rs)) {
            $answer[] = $lock;
        }
        return $answer;

    }

    /**
     * 
     * Delete all not published revisions that are older than X days. 
     * @param int $days
     */
    public static function removeOldRevisions($days) {

        $sqlWhere = "`created` < ".(time() - $days * 24 * 60 * 60)." AND NOT `published`";
        $sql = "
            SELECT * FROM `".DB_PREF."revision`
            WHERE ".$sqlWhere."
        ";

        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new CoreException('Can\'t find old revisions '.$sql.' '.ip_deprecated_mysql_error(), CoreException::DB);
        }

        while ($lock = ip_deprecated_mysql_fetch_assoc($rs)) {
            $eventData = array(
                'revisionId' => $lock['revisionId'],
            );
            \Ip\ServiceLocator::getDispatcher()->notify(new \Ip\Event(null, 'site.removeRevision', $eventData));
        }

        $sql = "
            DELETE FROM `".DB_PREF."revision`
            WHERE ".$sqlWhere."
        ";    

        $rs = ip_deprecated_mysql_query($sql);
        if (!$rs){
            throw new CoreException('Can\'t delete old revisions '.$sql.' '.ip_deprecated_mysql_error(), CoreException::DB);
        }
    }

}