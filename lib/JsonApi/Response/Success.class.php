<?php
/** A successful response from the Json server.
 *
 * @package jwt
 * @subpackage lib
 */
class JsonApi_Response_Success extends JsonApi_Response
{
  /** Init the response object.
   *
   * @return void
   * @throws JsonApi_Response_Exception if the response is somehow malformed.
   */
  protected function _initialize(  )
  {
    $this->_importPropertiesFromResponse(JsonApi_Base::STATUS_OK);
  }
}