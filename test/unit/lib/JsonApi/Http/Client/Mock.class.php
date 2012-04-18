<?php
/** Unit tests for JsonApi_Http_Client_Mock.
 *
 * @author Phoenix Zerin <phoenix@todofixthis.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage test.lib.jsonapi.http.client
 */
class JsonApi_Http_Client_MockTest extends Test_Case_Unit
{
  protected
    $_plugin = 'sfJwtJsonApiPlugin';

  /** @var JsonApi_Http_Client_Mock */
  protected $_client;
  protected
    $_hostname,

    $_path,
    $_params,
    $_content,

    $_errStatus;

  protected function _setUp(  )
  {
    $this->_hostname = 'example.com';

    $this->_client = new JsonApi_Http_Client_Mock($this->_hostname);

    $this->_path    = '/test/hello';
    $this->_params  = array('format'  => 'text/plain');
    $this->_content = 'Hello, World!';

    $this->_errStatus = 503;
  }

  public function testSuccessfulFetch()
  {
    $response = $this->_doSeed()->_doRequest();

    $this->assertInstanceOf(
      'JsonApi_Http_Response',
      $response,
      'Expected correct response type.'
    );

    $this->assertEquals(
      $this->_content,
      $response->getContent(),
      'Expected content to be retrieved correctly.'
    );

    $this->assertEquals(
      JsonApi_Http_Response::STATUS_OK,
      $response->getStatus(),
      'Expected status code to be retrieved correctly.'
    );

    $this->assertEquals(
      $this->_client->getUri($this->_path, $this->_params),
      $response->getUri(),
      'Expected URI to be retrieved correctly.'
    );

    $this->assertEquals(
      $response->getContent(),
      $this->_doRequest('post')->getContent(),
      'Expected content to be seeded for any method by default.'
    );
  }

  public function testFetchUnseededUri()
  {
    $response = $this->_doRequest();

    $this->assertInstanceOf(
      'JsonApi_Http_Response',
      $response,
      'Expected response to always have correct type, even for 404s.'
    );

    $this->assertEquals(
      $response->getStatus(true),
      $response->getContent(),
      'Expected response content to be status text for 404 errors.'
    );

    $this->assertEquals(
      JsonApi_Http_Client_Mock::STATUS_NOT_FOUND,
      $response->getStatus(),
      'Expected response to have correct HTTP status code.'
    );

    $this->assertEquals(
      $this->_client->getUri($this->_path, $this->_params),
      $response->getUri(),
      'Expected URI to be returned with 404 response.'
    );
  }

  public function testSeedForSpecificMethod()
  {
    $this->_doSeed(false, 'post');

    $response = $this->_doRequest('get');
    $this->assertEquals(
      JsonApi_Http_Client_Mock::STATUS_NOT_FOUND,
      $response->getStatus(),
      'Expected 404 when trying to fetch URI with wrong method.'
    );

    $response = $this->_doRequest('post');
    $this->assertEquals(
      JsonApi_Http_Response::STATUS_OK,
      $response->getStatus(),
      'Expected response to have correct HTTP status code.'
    );
  }

  public function testSeedCustomStatus()
  {
    $response = $this->_doSeed(null, 'get', $this->_errStatus)->_doRequest();

    $this->assertEquals(
      $this->_errStatus,
      $response->getStatus(),
      'Expected seeded HTTP status code to be retrieved.'
    );

    $this->assertEquals(
      $response->getStatus(true),
      $response->getContent(),
      'Expected response content to be status text if null content seeded.'
    );
  }

  public function testSeedCustomStatusEmptyContent()
  {
    $response = $this->_doSeed('', 'get', $this->_errStatus)->_doRequest();

    $this->assertEquals(
      '',
      $response->getContent(),
      'Expected response to have empty content if it was seeded explicitly.'
    );
  }

  public function testSeedCustomStatusWithNonEmptyContent()
  {
    $response =
      $this
        ->_doSeed($this->_content, 'get', $this->_errStatus)
        ->_doRequest();

    $this->assertEquals(
      $this->_content,
      $response->getContent(),
      'Expected response to contain seeded content regardless of status code.'
    );
  }

  public function testNonStringContentIsAutomaticallyJsonEncoded()
  {
    $content = array('foo' => 'bar');

    $response = $this->_doSeed($content)->_doRequest();

    $this->assertEquals(
      json_encode($content),
      $response->getContent(),
      'Expected non-string values to be auto-json_encode()d when seeded.'
    );
  }

  public function testInternalParamsAreIgnored()
  {
    $this->_params['__jsonapi_random'] = uniqid('?', true);
    $this->_doSeed();

    $this->_params['__jsonapi_random'] = uniqid('!', true);
    $response = $this->_doRequest();

    $this->assertEquals(
      JsonApi_Http_Response::STATUS_OK,
      $response->getStatus(),
      'Expected internal parameters to be ignored when looking up URI.'
    );
  }

  public function testSeedSuccess(  )
  {
    $likes  = 42;

    $this->_client->seedSuccess(
      $this->_path,
      $this->_params,
      array('likes' => $likes)
    );

    $response = $this->_doRequest()->makeJsonApiResponse();

    $this->assertTrue($response->isSuccess(),
      'Expected success repsonse to be seeded automatically.'
    );

    /** @noinspection PhpUndefinedFieldInspection */
    $this->assertEquals($likes, $response->likes,
      'Expected response detail to be seeded correctly.'
    );
  }

  public function testSeedFailure(  )
  {
    $errors = array('producer' => 'I gotta have more cowbell baby!');

    $this->_client->seedFailure(
      $this->_path,
      $this->_params,
      $errors
    );

    $response = $this->_doRequest()->makeJsonApiResponse();

    $this->assertFalse($response->isSuccess(),
      'Expected failure repsonse to be seeded automatically.'
    );

    /** @noinspection PhpUndefinedFieldInspection */
    $this->assertEquals($errors, $response->errors,
      'Expected response detail to be seeded correctly.'
    );
  }

  /** Execute a request and return the response.
   *
   * @param string      $meth
   * @param array|bool  $params Customize parameters.
   *
   * If you need to customize the path as well, you might as well interface with
   *  $this->_client directly
   *
   * @return JsonApi_Http_Response
   */
  protected function _doRequest( $meth = 'get', $params = false )
  {
    return $this->_client->$meth(
      $this->_path,
      ($params === false ? $this->_params : $params)
    );
  }

  /** Seeds the client with content.
   *
   * @param bool|string $content
   * @param string      $method
   * @param int         $status
   *
   * @return $this
   */
  protected function _doSeed(
    $content  = false,
    $method   = JsonApi_Http_Client_Mock::METHOD_ANY,
    $status   = JsonApi_Http_Response::STATUS_OK
  )
  {
    $this->_client->seed(
      $this->_path,
      $this->_params,
      ($content === false ? $this->_content : $content),
      $method,
      $status
    );

    return $this;
  }
}