<?php
/** A response from the Json server indicating that one or more of the
 *   parameters it received were invalid or malformed.
 *
 * @package jwt
 * @subpackage lib
 */
class JsonApi_Response_Failure extends JsonApi_Response
{
  const
    ERR_BAD_PARAMETERS = 'Bad parameters: [%s]';

  /** Converts the response object into an exception and throws it.
   *
   * @param string $message Custom error message to specify (default is keys
   *  of $this->errors).
   *
   * @return void
   * @throws JsonApi_Response_Exception
   */
  public function throwException( $message = null )
  {
    $message =
      is_null($message)
        ? sprintf(
            self::ERR_BAD_PARAMETERS,
            implode(', ', array_keys($this->__get(self::KEY_ERRORS)))
          )
        : (string) $message;

    throw new JsonApi_Response_Exception($message, $this->getResponseObject());
  }

  /** Init the response object.
   *
   * @return void
   * @throws JsonApi_Response_Exception if the response is somehow malformed.
   */
  protected function _initialize(  )
  {
    $this->_importPropertiesFromResponse(JsonApi_Base::STATUS_ERROR);
  }
}