<?php
/** Unit tests for JsonApi_Response_Success and by extension,
 *    JsonApi_Response::factory().
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage test.lib.jsonapi.response
 */
class JsonApi_Response_SuccessTest extends Test_Case_Unit_JsonApi_Response
{
  protected
    $_key,
    $_val,
    $_detail;

  protected function _setUp(  )
  {
    parent::_setUp();

    $this->_key = 'foo';
    $this->_val = 'bar';

    $this->_detail = array(
      $this->_key => $this->_val
    );
  }

  public function testSuccessFactory()
  {
    $response = $this->_success($this->_detail);

    $this->assertInstanceOf(
      'JsonApi_Response_Success',
      $response,
      'Expected well-formed success response to have correct type.'
    );

    $this->assertEquals(
      $this->_val,
      $response->{$this->_key},
      'Expected response parameter to be stored in the response detail.'
    );
  }

  public function testStatusMismatch()
  {
    $response = $this->_success($this->_detail, self::STATUS_FAIL);

    $this->assertInstanceOf(
      'JsonApi_Response_Error',
      $response,
      'Expected error response when status value in response is not "ok".'
    );
  }

  /** Malformed responses get converted to JsonApi_Response_Error.
   *
   * @see JsonApi_Response_ErrorTest
   */
}