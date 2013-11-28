<?php

/**
 * @package   ImpressPages
 *
 *
 */


namespace Ip\Module\System;


class Module
{
    /**
     * @param string $oldUrl
     * @return bool true on success
     */
    public function updateRobotsTxt($oldUrl)
    {
        $robotsFile = 'robots.txt';
        if ($oldUrl != ipConfig()->baseUrl('') && file_exists($robotsFile)) { //update robots.txt file.
            $data = file($robotsFile, FILE_IGNORE_NEW_LINES);
            $newData = '';
            foreach ($data as $dataKey => $dataVal) {
                $tmpVal = $dataVal;
                $tmpVal = trim($tmpVal);

                $tmpVal = preg_replace('/^Sitemap:(.*)/', 'Sitemap: ' . ipConfig()->baseUrl('sitemap.php'), $tmpVal);
                $newData .= $tmpVal . "\n";
            }
            if (is_writable($robotsFile)) {
                file_put_contents($robotsFile, $newData);
                return true;
            } else {
                return false;
            }
        }
        return true;
    }


    public function clearCache($cachedUrl)
    {
        \Ip\Internal\DbSystem::setSystemVariable('cached_base_url', ipConfig()->baseUrl('')); // update system variable

        $cacheVersion = \Ip\Internal\DbSystem::getSystemVariable('cache_version');
        \Ip\Internal\DbSystem::setSystemVariable('cache_version', $cacheVersion + 1);

        // TODO move somewhere
        if (ipConfig()->baseUrl('') != $cachedUrl) {
            ipDispatcher()->notify('site.urlChanged', array('oldUrl' => $cachedUrl, 'newUrl' => ipconfig()->baseUrl('')));
        }
        ipDispatcher()->notify('site.clearCache');
    }

    public function getSystemInfo()
    {

        $answer = '';

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, \Ip\Module\System\Model::instance()->getImpressPagesAPIUrl());
            curl_setopt($ch, CURLOPT_POST, 1);

            $postFields = 'module_name=communication&module_group=service&action=getInfo&version=1&afterLogin=';
            $postFields .= '&systemVersion=' . \Ip\Internal\DbSystem::getSystemVariable('version');

            //TODOX refactor
//            $groups = \Modules\developer\modules\Db::getGroups();
//            foreach ($groups as $groupKey => $group) {
//                $modules = \Modules\developer\modules\Db::getModules($group['id']);
//                foreach ($modules as $moduleKey => $module) {
//                    $postFields .= '&modules[' . $group['name'] . '][' . $module['name'] . ']=' . $module['version'];
//                }
//            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_REFERER, ipConfig()->baseUrl(''));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            $answer = curl_exec($ch);

            if (json_decode($answer) === null) { //json decode error
                return '';
            }


        }

        return $answer;
    }


    public function getUpdateInfo()
    {
        if (!function_exists('curl_init')) {
            return false;
        }

        $ch = curl_init();

        $curVersion = \Ip\Internal\DbSystem::getSystemVariable('version');

        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 1800, // set this to 30 min so we dont timeout
            CURLOPT_URL => \Ip\Module\System\Model::instance()->getImpressPagesAPIUrl(),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'module_group=service&module_name=communication&action=getUpdateInfo&curVersion='.$curVersion
        );

        curl_setopt_array($ch, $options);

        $jsonAnswer = curl_exec($ch);

        $answer = json_decode($jsonAnswer, true);

        if ($answer === null || !isset($answer['status']) || $answer['status'] != 'success') {
            return false;
        }

        return $answer;
    }

}

