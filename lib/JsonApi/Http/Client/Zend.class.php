<?php
/** A wrapper for Zend_Http_Client for use by JsonApi.
 *
 * @package jwt
 * @subpackage lib.JsonApi
 *
 * @todo Add observer so that we can inject logging.
 */
class JsonApi_Http_Client_Zend extends JsonApi_Http_Client
{
  private
    $_config;

  /** Init the class instance.
   *
   * @param string  $hostname
   * @param array   $config   Configuration for Zend_Http_Client instances.
   *
   * @return void
   */
  public function __construct( $hostname = null, array $config = array() )
  {
    parent::__construct($hostname);

    $this->_config = $config;
  }

  /** Send a request to the server.
   *
   * @param string  $method
   * @param string  $path
   * @param array   $params
   * @param array   $config Override Zend_Http_Client config for this request.
   *
   * @return string server response.
   */
  public function fetch( $method, $path, array $params = array(), array $config = array() )
  {
    $Client = new Zend_Http_Client(
      $this->getUri($path),
      array_merge($this->_config, $config)
    );

    $method = strtoupper($method);

    if( $params )
    {
      $meth = 'setParameter' . ucfirst(strtolower($method));

      foreach( $params as $key => $val )
      {
        $Client->$meth($key, $val);
      }
    }

    /* Create the full URI including params this time. */
    $Uri = $this->getUri($path, $params);

    try
    {
      $Response = $Client->request($method);
    }
    catch( Exception $e )
    {
      /* Add more information to the exception message. */
      throw new JsonApi_Http_Client_Exception(
        sprintf(
          'Got %s when requesting %s via %s:  "%s"',
            get_class($e),
            (string) $Uri,
            $method,
            $e->getMessage()
        ),
        $e->getCode()
      );
    }

    return new JsonApi_Http_Response(
      $Uri,
      $Response->getStatus(),
      $Response->getBody()
    );
  }
}
