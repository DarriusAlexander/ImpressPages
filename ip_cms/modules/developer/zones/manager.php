<?php
/**
 * @package ImpressPages
 *
 *
 */

namespace Modules\developer\zones;




require_once \Ip\Config::libraryFile('php/standard_module/std_mod.php');
require_once (__DIR__.'/db.php');


class ZonesArea extends \Ip\Lib\StdMod\Area{
    function after_insert($id){

        Db::afterInsert($id);

    }



    function removeFromAssociatedZones($currentValue, $deletedZone){
        $associatedZones = explode("\n", $currentValue);
        $newStr = '';
        foreach($associatedZones as $key => $value){
            if($this->getModuleKey($value) != $deletedZone){
                if($newStr != '')
                $newStr .= "\n";
                $newStr .= $value;
            }
        }
        return $newStr;
    }


    function update_associated_zones($currentValue, $deletedZone, $newName){
        $associatedZones = explode("\n", $currentValue);
        $newStr = '';
        foreach($associatedZones as $key => $value){
            if($this->getModuleKey($value) != $deletedZone){
                if($newStr != '')
                $newStr .= "\n";
                $newStr .= $value;
            }else{
                if($newStr != '')
                $newStr .= "\n";
                $newStr .= $this->makeZoneStr($newName, $this->get_module_depth($value));
            }
        }
        return $newStr;
    }


    function makeZoneStr($zoneName, $depth = null){
        if($depth !== null)
        return $zoneName.'['.$depth.']';
        else
        return $zoneName;
    }

    function getModuleKey($str){
        $begin = strrpos($str, '[');
        $end =  strrpos($str, ']');
        if($begin !== false && $end === strlen($str) - 1)
        return substr($str, 0, $begin);
        else
        return $str;
    }

    function get_module_depth($str){
        $begin = strrpos($str, '[');
        $end =  strrpos($str, ']');
        if($begin !== false && $end === strlen($str) - 1)
        return substr($str, $begin + 1, - 1);
        else
        return null;

    }

    function before_delete($id){
        global $parametersMod;
        global $site;
        $site->dispatchEvent('developer', 'zones', 'zone_deleted', array('zone_id'=>$id));

        $zone = Db::getZone($id);
        //if($zone['associated_group'] == 'standard' && $zone['associated_module'] == 'content_management'){
        $associatedZonesStr = $this->removeFromAssociatedZones($parametersMod->getValue('standard', 'menu_management', 'options', 'associated_zones'), $zone['name']);
        $parametersMod->setValue('standard', 'menu_management', 'options', 'associated_zones', $associatedZonesStr);

        $associatedZonesStr = $this->removeFromAssociatedZones($parametersMod->getValue('administrator', 'search', 'options', 'searchable_zones'), $zone['name']);
        $parametersMod->setValue('administrator', 'search', 'options', 'searchable_zones', $associatedZonesStr);

        $associatedZonesStr = $this->removeFromAssociatedZones($parametersMod->getValue('administrator', 'sitemap', 'options', 'associated_zones'), $zone['name']);
        $parametersMod->setValue('administrator', 'sitemap', 'options', 'associated_zones', $associatedZonesStr);
        //}
        $newZonesStr = $this->removeFromAssociatedZones($parametersMod->getValue('standard', 'configuration', 'advanced_options', 'xml_sitemap_associated_zones'), $zone['name']);
        $parametersMod->setValue('standard', 'configuration', 'advanced_options', 'xml_sitemap_associated_zones',  $newZonesStr);


        Db::deleteParameters($id);

    }




    function before_update($id){
        global $parametersMod;
        $this->tmp_zone = Db::getZone($id);
    }

    function after_update($id){
        global $parametersMod;
        $zone = Db::getZone($id);


        $newZonesStr = $this->update_associated_zones($parametersMod->getValue('standard', 'menu_management', 'options', 'associated_zones'), $this->tmp_zone['name'], $zone['name']);
        $parametersMod->setValue('standard', 'menu_management', 'options', 'associated_zones', $newZonesStr);

        $newZonesStr = $this->update_associated_zones($parametersMod->getValue('administrator', 'search', 'options', 'searchable_zones'), $this->tmp_zone['name'], $zone['name']);
        $parametersMod->setValue('administrator', 'search', 'options', 'searchable_zones', $newZonesStr);

        $newZonesStr = $this->update_associated_zones($parametersMod->getValue('administrator', 'sitemap', 'options', 'associated_zones'), $this->tmp_zone['name'], $zone['name']);
        $parametersMod->setValue('administrator', 'sitemap', 'options', 'associated_zones', $newZonesStr);

        $newZonesStr = $this->update_associated_zones($parametersMod->getValue('standard', 'configuration', 'advanced_options', 'xml_sitemap_associated_zones'), $this->tmp_zone['name'], $zone['name']);
        $parametersMod->setValue('standard', 'configuration', 'advanced_options', 'xml_sitemap_associated_zones', $newZonesStr);


    }

}

class Manager{
    var $standardModule;
    function __construct(){
        global $parametersMod;
         
         


        $elements = array();

        $element = new \Library\Php\StandardModule\element_text("text");
        $element->name = $parametersMod->getValue('developer', 'zones','admin_translations','name');
        $element->db_field = "translation";
        $element->showOnList = true;
        $element->required = true;
        $element->sortable = false;
        $elements[] = $element;

        $element = new \Library\Php\StandardModule\element_text("text");
        $element->name = $parametersMod->getValue('developer', 'zones','admin_translations','key');
        $element->db_field = "name";
        $element->showOnList = true;
        $element->required = true;
        $element->sortable = false;
        $elements[] = $element;

         
        $element = new \Library\Php\StandardModule\element_select("select");
        $element->set_name($parametersMod->getValue('developer', 'zones','admin_translations','template'));
        $element->set_db_field("template");
        $element->required = false;


        $templates = Db::getAvailableTemplates();
        sort($templates);
        $values = array();
        $values[] = array("", "");
        foreach($templates as $key => $template){
            $value = array();
            $value[] = $template;
            $value[] = $template;
            $values[] = $value;
        }
         
        $element->set_values($values);
         


        $element->set_show_on_list(true);
        $elements[] = $element;
         


         

        $element = new \Library\Php\StandardModule\element_text("text");
        $element->name = $parametersMod->getValue('developer', 'zones','admin_translations','associated_group');
        $element->db_field = "associated_group";
        $element->showOnList = true;
        $element->default_value = 'standard';
        $element->sortable = false;
        $elements[] = $element;

        $element = new \Library\Php\StandardModule\element_text("text");
        $element->name = $parametersMod->getValue('developer', 'zones','admin_translations','associated_module');
        $element->db_field = "associated_module";
        $element->showOnList = true;
        $element->default_value = 'content_management';
        $element->sortable = false;
        $elements[] = $element;


        $area0 = new ZonesArea();
        $area0->db_table = "zone";
        $area0->name = $parametersMod->getValue('developer', 'zones','admin_translations','zones');
        $area0->db_key = "id";
        $area0->elements = $elements;
        $area0->sort_field = "row_number";
        $area0->order_by = "row_number";
        $area0->new_record_position = "bottom";
        $area0->sortable = true;


         
        $this->standardModule = new \Ip\Lib\StdMod\StandardModule($area0);
    }
    function manage(){
        return $this->standardModule->manage();
         
    }


}
