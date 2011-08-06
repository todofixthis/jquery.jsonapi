<?php
/** A response from the Json server that we can't make heads or tails of.
 *
 * @package jwt
 * @subpackage lib
 */
class JsonApi_Response_Error extends JsonApi_Response
{
  /** Init the response object.
   *
   * @return void
   * @throws JsonApi_Response_Exception
   */
  protected function _initialize(  )
  {
    $Response = $this->getResponseObject();
    $this->getPropertiesObject()->add($this->_genExceptionResponse(
      sprintf(
        'Got unexpected HTTP status %d ("%s")',
          $Response->getStatus(),
          $Response->getStatus(true)
      )
    ));
  }
}