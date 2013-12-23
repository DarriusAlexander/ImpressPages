<?php
/**
 * @package ImpressPages
 *
 */
namespace Ip\Internal\Languages;



class Service
{
    const TEXT_DIRECTION_LTR = 'ltr';
    const TEXT_DIRECTION_RTL = 'rtl';

    public static function addLanguage($title, $abbreviation, $code, $url, $visible, $textDirection, $position = null)
    {
        $languageId = Model::addLanguage($title, $abbreviation, $code, $url, $visible, $textDirection, $position);
        return $languageId;
    }


}