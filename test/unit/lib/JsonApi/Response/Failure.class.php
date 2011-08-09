<?php
/** Unit tests for JsonApi_Response_Failure and by extension,
 *    JsonApi_Response::factory().
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage test.lib.jsonapi.response
 */
class JsonApi_Response_FailureTest
  extends Test_Case_Unit_JsonApi_Response
{
  protected
    $_key,
    $_msg,
    $_errors;

  protected function _setUp(  )
  {
    parent::_setUp();

    $this->_key = 'foobar';
    $this->_msg = 'fizzbuzz';

    $this->_errors = array(
      $this->_key => $this->_msg
    );
  }

  public function testFailureFactory()
  {
    $response = $this->_failure($this->_errors);

    $this->assertInstanceOf(
      'JsonApi_Response_Failure',
      $response,
      'Expected well-formed failure response to have correct type.'
    );

    $this->assertEquals(
      $this->_msg,
      $response->errors[$this->_key],
      'Expected error message to be stored in the response detail.'
    );
  }

  public function testStatusValueMismatch()
  {
    $response = $this->_failure($this->_errors, self::STATUS_OK);

    $this->assertInstanceOf(
      'JsonApi_Response_Error',
      $response,
      'Expected error response when wrong status value returned in failure message.'
    );
  }

  /** Malformed responses get converted to JsonApi_Response_Error.
   *
   * @see JsonApi_Response_ErrorTest
   */
}