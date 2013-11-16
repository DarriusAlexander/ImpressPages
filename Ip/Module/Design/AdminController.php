<?php
/**
 * @package ImpressPages
 *
 */

namespace Ip\Module\Design;




use Ip\ServiceLocator;
use Ip\Response\JsonRpc;

class AdminController extends \Ip\Controller
{


    public function index()
    {

        ipAddCss(\Ip\Config::coreModuleUrl('Assets/assets/css/bootstrap/bootstrap.css'));
        ipAddJavascript(\Ip\Config::coreModuleUrl('Assets/assets/css/bootstrap/bootstrap.js'));
        ipAddJavascript(\Ip\Config::libraryUrl('js/jquery-ui/jquery-ui.js'));
        ipAddCss(\Ip\Config::libraryUrl('js/jquery-ui/jquery-ui.css'));
        ipAddCss(\Ip\Config::libraryUrl('fonts/font-awesome/font-awesome.css'));
        ipAddJavascript(\Ip\Config::libraryUrl('js/easyXDM/easyXDM.min.js'));
        ipAddJavascript(\Ip\Config::coreModuleUrl('Design/public/options.js'));
        ipAddJavascript(\Ip\Config::coreModuleUrl('Design/public/market.js'));
        ipAddJavascript(\Ip\Config::coreModuleUrl('Design/public/design.js'));
        ipAddJavascript(\Ip\Config::coreModuleUrl('Design/public/pluginInstall.js'));
        ipAddCss(\Ip\Config::coreModuleUrl('Design/public/design.css'));
        ipAddJavascript(\Ip\Config::coreModuleUrl('System/public/market.js'));



        $model = Model::instance();

        $themes = $model->getAvailableThemes();

        $model = Model::instance();
        $theme = $model->getTheme(\Ip\Config::theme());
        $options = $theme->getOptionsAsArray();

        if (!defined('BACKEND')) {
            define('BACKEND', 1);
        }

        $helper = Helper::instance();
        $contentManagementModule = \Ip\Internal\Deprecated\Db::getModule(null, 'standard', 'content_management');
        $contentManagementUrl = $helper->generateAdminUrl($contentManagementModule['id']);

        $themePlugins = $model->getThemePlugins();
        $notInstalledPlugins = array();

        //filter plugins that are already installed
        foreach ($themePlugins as $key => $plugin) {
            // TODOX Plugin dir
//            if (!is_dir(BASE_DIR . PLUGIN_DIR . $plugin->getModuleGroupKey() . '/' . $plugin->getModuleKey())) { //if plugin has been already installed
//                $notInstalledPlugins[] = $plugin;
//            }
        }


        if (isset($_SESSION['module']['design']['pluginNote'])) {
            $pluginNote = $_SESSION['module']['design']['pluginNote'];
            unset($_SESSION['module']['design']['pluginNote']);
        } else {
            $pluginNote = '';
        }

        $data = array(
            'pluginNote' => $pluginNote,
            'theme' => $model->getTheme(\Ip\Config::theme()),
            'plugins' => $notInstalledPlugins,
            'availableThemes' => $themes,
            'marketUrl' => $model->getMarketUrl(),
            'showConfiguration' => !empty($options),
            'contentManagementUrl' => $contentManagementUrl,
            'contentManagementText' => $contentManagementModule['m_translation']
        );
        $contentView = \Ip\View::create('view/index.php', $data);

        return $contentView->render();
    }

    public function installPlugin()
    {
        $parametersMod = \Ip\ServiceLocator::getParametersMod();
        \Ip\Request::mustBePost();
        $postData = \Ip\Request::getPost();

        if (empty($postData['params']['pluginGroup']) || empty($postData['params']['pluginName'])) {
            throw new \Exception("Missing required parameters");
        }
        $pluginGroup = $postData['params']['pluginGroup'];
        $pluginName = $postData['params']['pluginName'];

        $model = Model::instance();
        try {
            $model->installThemePlugin($pluginGroup, $pluginName);

            $_SESSION['module']['design']['pluginNote'] = __('Plugin has been successfully installed. Please refresh the browser.', 'ipAdmin');

            return JsonRpc::result(1);
        } catch (\Exception $e) {
            return JsonRpc::error($e->getMessage(), $e->getCode());
        }

    }


    public function downloadThemes()
    {
        $parametersMod = \Ip\ServiceLocator::getParametersMod();

        \Ip\Request::mustBePost();
        $themes = \Ip\Request::getPost('themes');

        if (!is_writable(\Ip\Config::getCore('THEME_DIR'))) {
            return JsonRpc::error(__('Directory is not writable. Please check your email and install the theme manually.', 'ipAdmin'), 777);
        }

        try {
            if (!is_array($themes)) {
                return JsonRpc::error(__('Download failed: invalid parameters', 'ipAdmin'), 101);
            }

            if (function_exists('set_time_limit')) {
                set_time_limit(count($themes) * 180 + 30);
            }

            $themeDownloader = new ThemeDownloader();

            foreach ($themes as $theme) {
                if (!empty($theme['url']) && !empty($theme['name']) && !empty($theme['signature'])) {
                    $themeDownloader->downloadTheme($theme['name'], $theme['url'], $theme['signature']);
                }
            }
        } catch (\Ip\CoreException $e) {
            return JsonRpc::error($e->getMessage(), $e->getCode());
        } catch (\Exception $e) {
            return JsonRpc::error(__('Unknown error. Please see logs.', 'ipAdmin'), 987);
        }

        return JsonRpc::result(array('themes' => $themes));
    }

    /**
     * @throws \Ip\CoreException
     */
    public function installTheme()
    {
        $request = ServiceLocator::getRequest();
        $request->mustBePost();

        $themeName = $request->getPost('themeName');
        if (empty($themeName)) {
            throw new \Ip\CoreException('Invalid arguments.');
        }

        $model = Model::instance();

        try {
            $model->installTheme($themeName);
        } catch (\Ip\CoreException $e) {
            return JsonRpc::error($e->getMessage());
        }

        // TODOX jsonrpc
        return new \Ip\Response\Json(array('status' => 'success'));
    }

    public function updateConfig()
    {
        \Ip\Request::mustBePost();

        $configModel = ConfigModel::instance();

        $form = $configModel->getThemeConfigForm(\Ip\Config::theme());

        $post = \Ip\Request::getPost();

        $errors = $form->validate($post);

        if ($errors) {
            $data = array(
                'status' => 'error',
                'errors' => $errors
            );
        } else {
            $configModel = ConfigModel::instance();
            $model = Model::instance();
            $theme = $model->getTheme(\Ip\Config::theme());
            if (!$theme) {
                throw new \Ip\CoreException("Theme doesn't exist");
            }

            $options = $theme->getOptionsAsArray();

            foreach($options as $option) {
                if (empty($option['name'])) {
                    continue;
                }

                $field = $form->getField($option['name']);
                if (!$field) {
                    continue;
                }

                switch($option['type']) {
                    case 'check':
                        $value = $field->isChecked($post, $option['name']);
                        break;
                    default:
                        $value = $field->getValueAsString($post, $option['name']);
                }
                $configModel->setConfigValue(\Ip\Config::theme(), $option['name'], $value);
            }

            $lessCompiler = LessCompiler::instance();
            $lessCompiler->rebuild(\Ip\Config::theme());

        }




    }


    /**
     * Compile LESS CSS in real time and output the content
     */
    public function realTimeLess()
    {

        $file = \Ip\Request::getRequest('file');
        if (empty($file)) {
            throw new \Ip\CoreException("Required parameter missing");
        }

        $file = basename($file);

        $lessCompiler = LessCompiler::instance();
        $css = $lessCompiler->compileFile(\Ip\Config::theme(), $file);

        return new \Ip\Response($css, 'Content-type: text/css');
    }
}