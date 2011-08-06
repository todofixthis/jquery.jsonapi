<?php
/** An Exception indicating that something is wrong with the response we
 *   received from the Json server.
 *
 * @package jwt
 * @subpackage lib
 */
class JsonApi_Response_Exception extends JsonApi_Exception
{
  const
    PREVIEW_LENGTH = 80;

  protected
    $_response;

  /** Init the class instance.
   *
   * @param string                  $message
   * @param JsonApi_Http_Response $Response
   *
   * @return void
   */
  public function __construct( $message, JsonApi_Http_Response $Response )
  {
    $this->_response = $Response;

    /* Symfony's error handler does not output any information about the
     *  Exception except for its message, so we will try to make the message as
     *  helpful as possible.
     */
    parent::__construct(
      sprintf(
        '[%d] API Server returned error:  %s from %s ("%s")',
          $Response->getStatus(),
          $message,
          $Response->getUri(),
          UtilityClass::truncate($Response->getContent(), self::PREVIEW_LENGTH)
      ),
      $Response->getStatus()
    );
  }

  /** Accessor for $_response.
   *
   * @return JsonApi_Http_Response
   */
  public function getResponseObject(  )
  {
    return $this->_response;
  }

  /** Returns an array with additional information about the exception.
   *
   * @return array
   */
  public function toArray(  )
  {
    return array(
      'response' => $this->getResponseObject()
    );
  }

  /** Return errors from a throwException() call on WidgetApi_Response_Failure.
   *
   * @return array
   */
  public function getErrors(  )
  {
    $decoded = json_decode($this->getResponseObject()->getContent());

    return
      ($decoded and ! empty($decoded->{JsonApi_Response::KEY_ERRORS}))
        ? (array) $decoded->{JsonApi_Response::KEY_ERRORS}
        : array();
  }
}