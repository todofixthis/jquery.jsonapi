<?php
/** Base class for JsonApi HTTP clients.
 *
 * @package jwt
 * @subpackage lib.JsonApi
 */
abstract class JsonApi_Http_Client
{
  protected
    $_hostname;

  /** Send a request to the server.
   *
   * @param string $method
   * @param string $path
   * @param array  $params
   *
   * @return string server response.
   */
  abstract public function fetch( $method, $path, array $params = array() );

  /** Init the class instance.
   *
   * @param string $hostname
   *
   * @return void
   */
  public function __construct( $hostname = null )
  {
    $this->setHostname($hostname);
  }

  /** Accessor for $_hostname.
   *
   * @return string
   */
  public function getHostname(  )
  {
    return $this->_hostname;
  }

  /** Modifier for $_hostname.
   *
   * @param string $hostname
   *
   * @return JsonApi_Http_Client $this
   */
  public function setHostname( $hostname )
  {
    $this->_hostname = (string) $hostname;
    return $this;
  }

  /** Send a get request.
   *
   * @param string $path
   * @param array  $params
   *
   * @return string server response.
   */
  public function get( $path, array $params = array() )
  {
    return $this->fetch('get', $path, $params);
  }

  /** Send a post request.
   *
   * @param string $path
   * @param array  $params
   *
   * @return string server response.
   */
  public function post( $path, array $params = array() )
  {
    return $this->fetch('post', $path, $params);
  }

  /** Generate the URI to send the request to.
   *
   * @param string $hostname
   * @param string $path
   * @param array  $params
   *
   * @return Zend_Uri_Http
   */
  public function getUri( $path, array $params = array() )
  {
    if( ! $hostname = $this->getHostname() )
    {
      throw new InvalidArgumentException(
        'Specify a target hostname for this client before sending requests.'
      );
    }

    $Uri = Zend_Uri::factory('http://' . $hostname);

    $Uri->setPath($path);
    $Uri->setQuery($params);

    return $Uri;
  }
}