<?php
/**
 * @package ImpressPages
 *
 *
 */

namespace Ip\Internal\Languages;


/**
 * class to ouput the languages
 * @package ImpressPages
 */
class Model{

    public static function addLanguage($title, $abbreviation, $code, $url, $visible, $textDirection, $position)
    {
        $priority = self::getPositionPriority($position);

        $params = array (
            'd_long' => $title,
            'd_short' => $abbreviation,
            'code' => $code,
            'url' => $url,
            'url' => $visible,
            'text_direction' => $textDirection,
            'row_number' => $priority
        );
        $languageId = ipDb()->insert('language', $params);

        Db::createRootZoneElement($languageId);

        return $languageId;
    }

    private static function getPositionPriority($position)
    {
        if ($position === null) {
            $position = 100000000; //large large number
        }

        $languages = self::getLanguages();

        if ($position === 0) {
            return $languages[0]['row_number'] - 100;
        }

        if (isset($languages[$position - 1])) {
            if (isset($languages[$position])) {
                return ($languages[$position - 1]['row_number'] + $languages[$position]['row_number']) / 2;
            } else {
                $languages[$position]['row_number'] + 100;
            }
        } else {
            $languages[count($languages) - 1]['row_number'] + 100;
        }
    }


    /**
     *
     * @return array all website languages
     */
    private static function getLanguages() {
        $table = ipTable('language');
        $sql = "
        SELECT
            *
        FROM
            $table
        WHERE
            1
        ORDER BY
            `row_number`";

        return ipDb()->fetchAll($sql);
    }



    //TODOX move to Ip module
    public static function generateLanguageList(){
        if(!ipGetOption('Config.multilingual')) {
            return '';
        }
         
        return \Ip\View::create('view/list.php', self::getViewData());
    }


    //TODOX move to IP module
    private static function getViewData() {
        $languages = array();

        foreach (ipContent()->getLanguages() as $language) {
            if (!$language->isVisible()) {
                continue;
            }
        
            $tmpData = array();
            $tmpData['shortTitle'] = $language->getAbbreviation();
            $tmpData['longTitle'] = $language->getTitle();
            $tmpData['visible'] = $language->isVisible();
            $tmpData['current'] = $language->getCurrent();
            $tmpData['url'] = \Ip\Internal\Deprecated\Url::generate($language->getId());
            $languages[] = $tmpData;
        }
        $data = array (
            'languages' => $languages
        );
        return $data;
    }

}