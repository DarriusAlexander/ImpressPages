<?php
/**
 * @package ImpressPages
 *
 */
namespace Ip\Internal\System;


class AdminController extends \Ip\Controller
{


    public function index()
    {
        $model = Model::instance();
        ipAddJs('Ip/Internal/Grid/assets/grid.js');
        ipAddJs('Ip/Internal/Grid/assets/gridInit.js');

        $notes = array();

        if (isset($_SESSION['Ip']['notes']) && is_array($_SESSION['Ip']['notes'])) {
            $notes = $_SESSION['Ip']['notes'];
        }

        unset($_SESSION['Ip']['notes']);


        $enableUpdate = !defined('MULTISITE_WEBSITES_DIR'); //disable update in MultiSite installation

        $trash = array(
            'size' => \Ip\Internal\Pages\Service::trashSize(),
        );

        if ($trash['size']) {

            $form = new \Ip\Form();

            $field = new \Ip\Form\Field\Hidden();
            $field->setName('aa');
            $field->setValue('System.emptyTrash');
            $form->addField($field);

            $submit = new \Ip\Form\Field\Submit(array(
                'value' => __('Empty trash', 'Ip-admin')
            ));

            $form->addField($submit);

            $trash['form'] = $form;
        }

        $data = array(
            'notes' => $notes,
            'version' => \Ip\ServiceLocator::storage()->get('Ip', 'version'),
            'changedUrl' => $model->getOldUrl() != $model->getNewUrl(),
            'oldUrl' => $model->getOldUrl(),
            'newUrl' => $model->getNewUrl(),
            'migrationsAvailable' => \Ip\Internal\Update\Service::migrationsAvailable(),
            'migrationsUrl' => ipActionUrl(array('pa' => 'Update')),
            'trash' => $trash,
        );

        $content = ipView('view/index.php', $data)->render();

        if ($enableUpdate) {
            ipAddJs('Ip/Internal/System/assets/update.js');
        }
        ipAddJs('Ip/Internal/System/assets/migrations.js');

        return $content;
    }

    public function startUpdate()
    {
        try {
            \Ip\Internal\Update\Service::update();
        } catch (\Exception $e) {
            return new \Ip\Response\Json(array(
                'error' => $e->getMessage()
            ));

        }

        $_SESSION['Ip']['notes'][] = __('ImpressPages has been successfully updated.', 'Ip-admin');

        return new \Ip\Response\Json(array(
            'status' => 'success'
        ));

    }

    public function updateLinks()
    {
        Service::updateLinks();
        $_SESSION['Ip']['notes'][] = __('Links have been successfully updated.', 'Ip-admin');

        return new \Ip\Response\Redirect(ipActionUrl(array('aa' => 'System')));

    }

    protected function indexUrl()
    {
        return ipConfig()->baseUrl() . '?aa=System.index';
    }

    public function getIpNotifications()
    {

        $systemInfo = Model::getIpNotifications();


        if (isset($_REQUEST['afterLogin'])) { // request after login.
            if ($systemInfo == '') {
                $_SESSION['ipSystem']['show_system_message'] = false; //don't display system alert at the top.
                return;
            } else {
                $md5 = \Ip\ServiceLocator::storage()->get('Ip', 'lastSystemMessageShown');
                if ($systemInfo && (!$md5 || $md5 != md5(serialize($systemInfo)))) { //we have a new message
                    $newMessage = false;

                    foreach (json_decode($systemInfo) as $infoValue) {
                        if ($infoValue->type != 'status') {
                            $newMessage = true;
                        }
                    }

                    $_SESSION['ipSystem']['show_system_message'] = $newMessage; //display system alert
                } else { //this message was already seen.
                    $_SESSION['ipSystem']['show_system_message'] = false; //don't display system alert at the top.
                    return;
                }

            }
        } else { //administrator/system tab.
            \Ip\ServiceLocator::storage()->set('Ip', 'lastSystemMessageShown', md5(serialize($systemInfo)));
            $_SESSION['ipSystem']['show_system_message'] = false; //don't display system alert at the top.
        }

        return new \Ip\Response\Json($systemInfo);
    }

    public function emptyTrash()
    {
        \Ip\Internal\Pages\Service::emptyTrash();

        return new \Ip\Response\Json(array('redirectUrl' => ipActionUrl(array('aa' => 'System'))));
    }

    public function sendUsageStatisticsAjax()
    {
        ipRequest()->mustBePost();

        $usageStatistics = false;

        $data = ipRequest()->getPost('data');

        // Send stats just after admin login
        if (isset($_SESSION['module']['system']['adminJustLoggedIn'])) {
            $usageStatistics = array(
                'action' => 'Admin.login',
                'data' => array(
                    'admin' => ipAdminId()
                )
            );

            // Removing session variable to send these stats only once
            unset($_SESSION['module']['system']['adminJustLoggedIn']);
        }

        // if we have some kind of definition then we send data
        if ($usageStatistics !== false) {
            \Ip\Internal\System\Model::sendUsageStatistics($usageStatistics);
        }

        return \Ip\Response\JsonRpc::result('ok');
    }
}
