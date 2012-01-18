<?php
/** Provides base functionality for JsonApi response test cases.
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.test.case.unit.jsonapi
 */
abstract class Test_Case_Unit_JsonApi_Response extends Test_Case_Unit
{
  const
    HTTP_STATUS_OK    = JsonApi_Http_Response::STATUS_OK,
    HTTP_STATUS_FAIL  = JsonApi_Http_Response::STATUS_FAIL,

    STATUS_OK         = JsonApi_Response::STATUS_OK,
    STATUS_FAIL       = JsonApi_Response::STATUS_FAIL;

  protected
    $_plugin = 'sfJwtJsonApiPlugin';

  /** @var Zend_Uri_Http */
  protected $_uri;

  protected function _setUp(  )
  {
    $this->_uri = Zend_Uri::factory('http://localhost/jsonapi_test');
  }

  /** Generates a response with success values.
   *
   * @param string[]  $detail
   * @param int       $httpStatus
   *
   * @return JsonApi_Response
   */
  protected function _success(
    array $detail,
          $httpStatus = self::HTTP_STATUS_OK
  )
  {
    return $this->_genResponse($httpStatus, array(
      JsonApi_Response::KEY_STATUS  => self::STATUS_OK,
      JsonApi_Response::KEY_DETAIL  => $detail
    ));
  }

  /** Generates a response with failure values.
   *
   * @param string[]  $errors
   * @param int       $httpStatus
   *
   * @return JsonApi_Response
   */
  protected function _failure(
    array $errors,
          $httpStatus = self::HTTP_STATUS_FAIL
  )
  {
    return $this->_genResponse($httpStatus, array(
      JsonApi_Response::KEY_STATUS  => self::STATUS_FAIL,
      JsonApi_Response::KEY_DETAIL  => array(
        JsonApi_Response_Failure::KEY_ERRORS  => $errors
      )
    ));
  }

  /** Generates a response object.
   *
   * @param int   $httpStatus
   * @param array $content
   *
   * @return JsonApi_Response
   */
  protected function _genResponse( $httpStatus, array $content )
  {
    return JsonApi_Response::factory(new JsonApi_Http_Response(
      $this->_uri,
      $httpStatus,
      json_encode($content)
    ));
  }
}