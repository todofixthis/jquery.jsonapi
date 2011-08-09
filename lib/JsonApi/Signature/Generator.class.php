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

/** Generates parameter signatures for JsonApi requests.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi.signature
 */
class JsonApi_Signature_Generator
{
  const
    KEY_SALT    = 'salt',
    KEY_TIME    = 'time',
    KEY_SIG     = 'sig',

    ALGORITHM_DEFAULT = 'sha512';

  protected
    $_key,
    $_algorithm;

  /** Init the class instance.
   *
   * @param string $key Key used to generate the signature hash.
   *
   * @return void
   */
  public function __construct( $key, $algorithm = self::ALGORITHM_DEFAULT )
  {
    if( $key == '' )
    {
      throw new InvalidArgumentException('Key must not be empty.');
    }

    if( ! function_exists('hash') )
    {
      throw new RuntimeException(sprintf(
        '%s requires the hash extension.',
          __CLASS__
      ));
    }

    if( ! in_array($algorithm, hash_algos()) )
    {
      throw new InvalidArgumentException(sprintf(
        'Unknown hash algorithm "%s"; see output of hash_algos().',
          $algorithm
      ));
    }

    $this->_key       = (string) $key;
    $this->_algorithm = (string) $algorithm;
  }

  /** Generates a signed array of parameters.
   *
   * @param array $params
   * @param bool  $preserve Preserve salt and timestamp in array when computing
   *  the signature hash.  This is used when verifying that the signature in an
   *  already-signed array is valid.
   *
   * @return array copy of $params with signature parameters.
   */
  public function sign( array $params, $preserve = false )
  {
    /* Convert all keys and values to strings because that's how they will look
     *  on the receiving end.
     */
    array_walk_recursive($params, array('JsonApi_Utility', '_stringify'));

    /* Operate on a copy of $params so that we can modify stuff to our heart's
     *  content but still return a minimally-modified version at the end.
     */
    $copy = JsonApi_Utility::normalizeParams($params);

    /* Add salt to both arrays (it's part of the signature). */
    $key = JsonApi_Utility::internalKey(self::KEY_SALT);
    if( $preserve )
    {
      if( empty($params[$key]) )
      {
        throw new InvalidArgumentException(
          'Signed array is missing salt value.'
        );
      }
      else
      {
        $copy[$key] = $params[$key];
      }
    }
    else
    {
      $params[$key] = $copy[$key] = $this->_genSalt();
    }

    /* Add timestamp to both arrays (it's part of the signature). */
    $key = JsonApi_Utility::internalKey(self::KEY_TIME);
    if( $preserve )
    {
      if( empty($params[$key]) )
      {
        throw new InvalidArgumentException(
          'Signed array is missing timestamp value.'
        );
      }
      else
      {
        $copy[$key] = $params[$key];
      }
    }
    else
    {
      $params[$key] = $copy[$key] = $this->_genTimestamp();
    }

    /* Generate the signature. */
    $key = JsonApi_Utility::internalKey(self::KEY_SIG);
    $params[$key]  = hash($this->_algorithm, $this->_key . serialize($copy));
    return $params;
  }

  /** Generates a salt to inject into the parameters array.
   *
   * @return string
   *
   * @see JsonApi_Signature_Generator_Mock
   */
  protected function _genSalt(  )
  {
    return sha1(uniqid(__CLASS__, true));
  }

  /** Generates a timestamp to inject into the parameters array.
   *
   * @return string(unixtimestamp)
   *
   * @see JsonApi_Signature_Generator_Mock
   */
  protected function _genTimestamp(  )
  {
    return (string) time();
  }
}