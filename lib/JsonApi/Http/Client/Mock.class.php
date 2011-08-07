<?php
/**
 * Copyright (c) 2011 J. Walter Thompson dba JWT
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/** A mock HTTP client, used to simulate server responses for testing.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi.http.client
 */
class JsonApi_Http_Client_Mock extends JsonApi_Http_Client
{
  const
    METHOD_ANY        = '*',
    STATUS_NOT_FOUND  = 404;

  protected
    $_content = array();

  /** Seeds the client with a simulated server response.
   *
   * @param string $path
   * @param array  $params
   * @param string $content
   *  If null (empty string doesn't count) HTTP status message will be used
   *    instead.
   *  If a string value is provided, it will be seeded unmodified.
   *  Any other value will be json_encode()'d before it is seeded.
   * @param string $method
   * @param int    $status
   *
   * @return JsonApi_HttpClient_Mock $this
   */
  public function seed(
          $path,
    array $params,
          $content,
          $method   = self::METHOD_ANY,
          $status   = JsonApi_Http_Response::STATUS_OK
  )
  {
    $this->_content[$method][$this->_genContentKey($path, $params)] = array(
      is_null($content)
        ? Zend_Http_Response::responseCodeAsText($status)
        : is_string($content) ? $content : json_encode($content),
      (int) $status
    );

    return $this;
  }

  /** Send a request to the server.
   *
   * @param string $method
   * @param string $path
   * @param array  $params
   *
   * @return string server response.
   */
  public function fetch( $method, $path, array $params = array() )
  {
    $key = $this->_genContentKey($path, $params);

    if( isset($this->_content[$method][$key]) )
    {
      list($content, $status) = $this->_content[$method][$key];
    }
    elseif( isset($this->_content[self::METHOD_ANY][$key]) )
    {
      list($content, $status) = $this->_content[self::METHOD_ANY][$key];
    }
    else
    {
      $status  = self::STATUS_NOT_FOUND;
      $content = Zend_Http_Response::responseCodeAsText(self::STATUS_NOT_FOUND);
    }

    return new JsonApi_Http_Response(
      $this->getUri($path, $params),
      $status,
      $content
    );
  }

  /** Returns all seeded URLs and their content.
   *
   * @param string $method
   *
   * @return array(string(uri) => string)|null
   */
  public function getAll( $method = self::METHOD_ANY )
  {
    return (isset($this->_content[$method]) ? $this->_content[$method] : null);
  }

  /** Generates a content key for a path/params combo.
   *
   * @param string $path
   * @param array  $params
   *
   * @return string
   */
  protected function _genContentKey( $path, array $params )
  {
    /* Because request signatures can't be predicted, ignore them. */
    unset($params['_salt'], $params['_signature'], $params['_timestamp']);

    /* Convert all parameters to strings. */
    array_walk_recursive(
      $params,
      create_function('&$val, $key', '$val = (string) $val;')
    );

    return (string) $this->getUri($path, $params);
  }
}
