<?php
/**
 * @package ImpressPages
 *
 */

namespace Ip\Internal\Pages;





class Helper
{

    public static function languageList()
    {
        $answer = array();
        $languages = ipContent()->getLanguages();
        foreach($languages as $language)
        {
            $answer[] = array(
                'id' => $language->getId(),
                'title' => $language->getTitle(),
                'abbreviation' => $language->getAbbreviation()
            );
        }
        return $answer;
    }

    public static function zoneList()
    {
        $answer = array();
        $zones = ipContent()->getZones();
        foreach($zones as $zone)
        {
            $answer[] = array(
                'name' => $zone->getName(),
                'title' => $zone->getTitle()
            );
        }
        return $answer;
    }

    public static function pagePropertiesForm($zoneName, $pageId)
    {
        $zone = ipContent()->getZone($zoneName);
        $page = $zone->getPage($pageId);

        $form = new \Ip\Form();

        $field = new \Ip\Form\Field\Hidden(
            array(
                'name' => 'aa',
                'defaultValue' => 'Pages.updatePage'
            ));
        $form->addField($field);


        $field = new \Ip\Form\Field\Hidden(
            array(
                'name' => 'pageId',
                'defaultValue' => $pageId
            ));
        $form->addField($field);


        $field = new \Ip\Form\Field\Hidden(
            array(
                'name' => 'zoneName',
                'defaultValue' => $zoneName
            ));
        $form->addField($field);

        $field = new \Ip\Form\Field\Text(
            array(
                'name' => 'navigationTitle',
                'label' => __('Navigation title', 'ipAdmin', false),
                'defaultValue' => $page->getNavigationTitle()
            ));
        $form->addField($field);

        $field = new \Ip\Form\Field\Text(
            array(
                'name' => 'pageTitle',
                'label' => __('Page title', 'ipAdmin', false),
                'defaultValue' => $page->getPageTitle()
            ));
        $form->addField($field);

        $field = new \Ip\Form\Field\Text(
            array(
                'name' => 'keywords',
                'label' => __('Keywords', 'ipAdmin', false),
                'defaultValue' => $page->getKeywords()
            ));
        $form->addField($field);

        $field = new \Ip\Form\Field\Textarea(
            array(
                'name' => 'description',
                'label' => __('Description', 'ipAdmin', false),
                'defaultValue' => $page->getDescription()
            ));
        $form->addField($field);

        $field = new \Ip\Form\Field\Text(
            array(
                'name' => 'url',
                'label' => __('Url', 'ipAdmin', false),
                'defaultValue' => $page->getUrl()
            ));
        $form->addField($field);


        $field = new \Ip\Form\Field\Checkbox(
            array(
                'name' => 'visible',
                'label' => __('Visible', 'ipAdmin', false),
                'value' => 1,
                'defaultValue' => $page->isVisible()
            ));
        $form->addField($field);


        $layouts = \Ip\Internal\Design\Service::getLayouts();
        $options = array();
        foreach($layouts as $layout) {
            $options[] = array ($layout, $layout);
        }

        $curLayout = \Ip\Internal\ContentDb::getPageLayout(
            $zone->getAssociatedModule(),
            $page->getId()
        );
        if (!$curLayout) {
            $curLayout = $zone->getLayout();
        }
        $field = new \Ip\Form\Field\Select(
            array(
                'name' => 'layout',
                'label' => __('Layout', 'ipAdmin', false),
                'values' => $options,
                'defaultValue' => $curLayout
            ));
        $form->addField($field);


        $field = new \Ip\Form\Field\Text(
            array(
                'name' => 'createdOn',
                'label' => __('Created on', 'ipAdmin', false),
                'defaultValue' => date('Y-m-d', strtotime($page->getCreatedOn()))
            ));
        $form->addField($field);

        $field = new \Ip\Form\Field\Text(
            array(
                'name' => 'lastModified',
                'label' => __('Update on', 'ipAdmin', false),
                'defaultValue' => date('Y-m-d', strtotime($page->getLastModified()))
            ));
        $form->addField($field);

        $field = new \Ip\Form\Field\Submit(
            array(
                'name' => 'submit',
                'defaultValue' => __('Save', 'ipAdmin', false)
            ));
        $form->addField($field);

        return $form;
    }

    public static function addform()
    {
        $form = new \Ip\Form();

        $field = new \Ip\Form\Field\Text(
            array(
                'name' => 'title',
                'label' => __('Title', 'ipAdmin', false)
            ));
        $form->addField($field);

        $field = new \Ip\Form\Field\Checkbox(
            array(
                'name' => 'visible',
                'label' => __('Visible', 'ipAdmin', false),
                'defaultValue' => ipGetOption('Pages.hideNewPages', 1)
            ));
        $form->addField($field);

        return $form;
    }

}