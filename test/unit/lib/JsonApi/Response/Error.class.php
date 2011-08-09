<?php
/** Unit tests for JsonApi_Response_Error.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage test.lib.jsonapi.response
 */
class JsonApi_Response_ErrorTest
  extends Test_Case_Unit_JsonApi_Response
{
  public function testUnparseableResponse()
  {
    $response = JsonApi_Response::factory(new JsonApi_Http_Response(
      $this->_uri,
      self::HTTP_STATUS_OK,
      'This is not JSON.  This is only a tribute.'
    ));

    $this->assertInstanceOf(
      'JsonApi_Response_Error',
      $response,
      'Expected unparseable content to be encapsulated in an error object.'
    );

    /* Since we're here, let's also make sure we can convert the error response
     *  into an exception.
     */
    try
    {
      $response->throwException();
      $this->fail('Expected JsonApi_Response_RethrownException to be thrown.');
    }
    catch( JsonApi_Response_RethrownException $e )
    {
    }
  }

  public function testUnknownFormat()
  {
    $response = JsonApi_Response::factory(new JsonApi_Http_Response(
      $this->_uri,
      self::HTTP_STATUS_OK,
      json_encode(array('foo' => 'bar'))
    ));

    $this->assertInstanceOf(
      'JsonApi_Response_Error',
      $response,
      'Expected unkown JSON format to cause an error response.'
    );
  }

  public function testUnknownStatusValue()
  {
    $response = $this->_success(array('foo' => 'bar'), 'baz');

    $this->assertInstanceOf(
      'JsonApi_Response_Error',
      $response,
      'Expected unknown status value to cause an error response.'
    );
  }

  public function testBadHttpStatus()
  {
    $response = $this->_success(
      array('foo' => 'bar'),
      self::HTTP_STATUS_OK,
      401
    );

    $this->assertInstanceOf(
      'JsonApi_Response_Error',
      $response,
      'Expected bad HTTP status code to cause an error response.'
    );
  }
}