<?php
/**
 * @package   ImpressPages
 */

namespace Tests\Ip;

class RequestTest extends \PhpUnit\GeneralTestCase
{
    public function testGetAndPost()
    {
        \PhpUnit\Helper\TestEnvironment::initCode();

        \Ip\ServiceLocator::addRequest(new \Ip\Request());

        $request = new \Ip\Request();
        $request->setGet(array(
            'rise' => 'and shine',
            'look' => 'and smile',
        ));

        \Ip\ServiceLocator::addRequest($request);

        $this->assertEquals('and smile', ipRequest()->getQuery('look'));

        \Ip\ServiceLocator::removeRequest();

        $this->assertNull(ipRequest()->getQuery('look'));
    }

    public function testRelativePath()
    {
        \PhpUnit\Helper\TestEnvironment::initCode();

        $server = array(
            'HTTP_HOST' => 'local.ip4.x.org',
            'SERVER_NAME' => 'local.ip4.x.org',
            'SERVER_ADDR' => '127.0.0.1',
            'SERVER_PORT' => '80',
            'REMOTE_ADDR' => '127.0.0.1',
            'DOCUMENT_ROOT' => '/var/www/ip4.x',
            'REQUEST_SCHEME' => 'http',
            'CONTEXT_DOCUMENT_ROOT' => '/var/www/ip4.x',
            'SCRIPT_FILENAME' => '/var/www/ip4.x/index.php',
            'REDIRECT_URL' => '/admin/',
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => '',
            'REQUEST_URI' => '/admin/',
            'SCRIPT_NAME' => '/index.php',
            'PHP_SELF' => '/index.php',
        );

        $config = include TEST_FIXTURE_DIR . 'ip_config/default.php';
        $config['BASE_URL'] = '';
        $config['BASE_DIR'] = '';
        $ipConfig = new \Ip\Config($config, $server);
        \Ip\ServiceLocator::setConfig($ipConfig);

        $request = new \Ip\Request();
        $request->setServer($server);

        $this->assertEquals('admin/', $request->getRelativePath());
    }
}