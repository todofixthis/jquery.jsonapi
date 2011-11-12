<?php
/** Functional tests for /jsonapi_test/basic.
 *
 * @author Phoenix Zerin <phoenix@todofixthis.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage test.api
 */
class frontend_jsonapiTest_basicTest extends Test_Case_Functional
{
  protected
    $_application = 'frontend',
    $_url,
    $_params,

    $_result;

  protected function _setUp(  )
  {
    $this->_browser->usePlugin('JsonApiResponse');

    $this->_url     = '/jsonapi_test/basic';

    $this->_result  = 'fifteen';

    $this->_params  = array(
      'result'  => $this->_result
    );
  }

  public function testGetFails(  )
  {
    $this->_browser->get($this->_url);
    $this->assertStatusCode(404);
  }

  public function testSuccess(  )
  {
    $this->_browser->post($this->_url, $this->_params);
    $this->assertStatusCode(200);

    /* @var $result Test_Browser_Plugin_JsonApiResponse */
    $result = $this->_browser->getJsonApiResponse();

    $this->assertEquals(
      $this->_result,
      $result->result,
      'Expected mock api to return the result passed in.'
    );
  }

  public function testResultParamIsRequired(  )
  {
    $this->_browser->post($this->_url, array());
    $this->assertStatusCode(400);

    $result = $this->_browser->getJsonApiResponse();

    $this->assertEquals(
      array('result'),
      array_keys($result->errors),
      'Expected missing result to generate an error message.'
    );
  }
}