<?php
/**
 * @package ImpressPages

 *
 */
namespace Ip\Module\Content\Widget;


class IpFaq extends \Ip\Module\Content\Widget{


    public function getTitle() {
        global $parametersMod;
        return $parametersMod->getValue('standard', 'content_management', 'widget_faq', 'widget_title');
    }

}