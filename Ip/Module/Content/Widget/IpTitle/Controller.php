<?php
/**
 * @package ImpressPages

 *
 */
namespace Ip\Module\Content\Widget\IpTitle;




class Controller extends \Ip\WidgetController{

    public function getTitle() {
        return __('Title', 'ipAdmin', false);
    }


    public function getActionButtons()
    {
        return array(
            array (
                'label' => __('H1', 'ipAdmin'),
                'class' => 'ipsH1'
            ),
            array (
                'label' => __('H2', 'ipAdmin'),
                'class' => 'ipsH2'
            ),
            array (
                'label' => __('H3', 'ipAdmin'),
                'class' => 'ipsH3'
            ),
            array (
                'label' => __('Options', 'ipAdmin'),
                'class' => 'ipsOptions'
            )
        );
    }

    public function adminSnippets()
    {
        $snippets[] = \Ip\View::create('snippet/controls.php')->render();
        return $snippets;
    }

    public function previewHtml($instanceId, $data, $layout)
    {
        if (empty($data['level']) || (int)$data['level'] < 1) {
            $data['level'] = 1;
        }
        return parent::previewHtml($instanceId, $data, $layout);
    }

}