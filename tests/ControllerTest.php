<?php

namespace MABI\Testing;

include_once __DIR__ . '/../DirectoryControllerLoader.php';
include_once __DIR__ . '/../GeneratedRESTModelControllerLoader.php';
include_once __DIR__ . '/../autodocs/MarkdownParser.php';
include_once __DIR__ . '/SampleAppTestCase.php';

class ControllerTest extends SampleAppTestCase {

  /**
   * @var \PHPUnit_Framework_MockObject_MockObject
   */
  protected $controllerMock;

  public function testDirectoryControllerLoader() {
    $this->setUpApp();

    $controllerLoader = new \MABI\DirectoryControllerLoader('TestApp/TestControllerDir', $this->app, 'mabiTesting');
    $controllers = $controllerLoader->getControllers();
    $this->assertNotEmpty($controllers);
    $this->assertInstanceOf('\mabiTesting\JustAController', $controllers[0]);
  }

  private function setUpControllerApp($env = array()) {
    $this->setUpApp($env);

    $dirControllerLoader = new \MABI\DirectoryControllerLoader('TestApp/TestControllerDir', $this->app, 'mabiTesting');
    $this->controllerMock = $this->getMock('\mabiTesting\JustAController', array(
        'post',
        'getTestFunc',
        'postTestFunc',
        'putTestFunc',
        'deleteTestFunc'
      ), array($this->app),
      'JustAController');

    $controllerLoader = new \MABI\ControllerLoader();
    $controllerLoader->setControllers(array($this->controllerMock));

    $this->app->setControllerLoaders(array($controllerLoader));
  }

  // note: All controller loaders tested together is tested in the AppTest

  /**
   * test that custom routes were generated properly
   */
  public function testRoutes() {
    // Test base post
    $this->setUpControllerApp(array('REQUEST_METHOD' => 'POST', 'PATH_INFO' => '/justa'));
    $this->controllerMock->expects($this->once())
      ->method('post')
      ->will($this->returnValue('test'));
    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertEquals('', $this->app->getResponse()->body());

    // Test custom get
    $this->setUpControllerApp(array('PATH_INFO' => '/justa/testfunc'));
    $this->controllerMock->expects($this->once())
      ->method('getTestFunc')
      ->will($this->returnValue('test'));
    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertEquals('', $this->app->getResponse()->body());

    // Test custom post
    $this->setUpControllerApp(array('REQUEST_METHOD' => 'POST', 'PATH_INFO' => '/justa/testfunc'));
    $this->controllerMock->expects($this->once())
      ->method('postTestFunc')
      ->will($this->returnValue('test'));
    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertEquals('', $this->app->getResponse()->body());

    // Test custom put
    $this->setUpControllerApp(array('REQUEST_METHOD' => 'PUT', 'PATH_INFO' => '/justa/testfunc'));
    $this->controllerMock->expects($this->once())
      ->method('putTestFunc')
      ->will($this->returnValue('test'));
    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertEquals('', $this->app->getResponse()->body());

    // Test custom delete
    $this->setUpControllerApp(array('REQUEST_METHOD' => 'DELETE', 'PATH_INFO' => '/justa/testfunc'));
    $this->controllerMock->expects($this->once())
      ->method('deleteTestFunc')
      ->will($this->returnValue('test'));
    $this->app->call();
    $this->assertEquals(200, $this->app->getResponse()->status());
    $this->assertEquals('', $this->app->getResponse()->body());
  }

  /**
   * test that middleware was added appropriately
   */
  function testMiddleware() {
    $this->setUpApp();

    $dirControllerLoader = new \MABI\DirectoryControllerLoader('TestApp/TestControllerDir', $this->app, 'mabiTesting');
    $this->app->setControllerLoaders(array($dirControllerLoader));
    $controllers = $dirControllerLoader->getControllers();
    foreach ($controllers as $controller) {
      if (get_class($controller) == 'mabiTesting\JustAController') {
        /**
         * @var $middlewares \MABI\Middleware[]
         */
        $middlewares = $controller->getMiddlewares();
        $this->assertInternalType('array', $middlewares);
        $this->assertNotEmpty($middlewares);
        $this->assertInstanceOf('MABI\Middleware\AnonymousIdentifier', $middlewares[0]);
      }
    }
  }

  /**
   * make sure document generator returns valid doc array with no errors/warnings
   */
  function testDocs() {
    $this->setUpControllerApp();
    $parser = new \MABI\Autodocs\MarkdownParser();
    $docsOutput = $this->controllerMock->getDocJSON($parser);
    $this->assertNotEmpty($docsOutput);
    $this->assertInternalType('array', $docsOutput);
  }
}