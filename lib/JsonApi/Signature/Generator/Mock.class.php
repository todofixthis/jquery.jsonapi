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

/** A mockable signature generator, used for testing.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi.signature.generator
 */
class JsonApi_Signature_Generator_Mock
  extends JsonApi_Signature_Generator
{
  protected
    $_salt,
    $_time;

  /** Init the class instance.
   *
   * @param string  $key
   * @param string  $algorithm
   * @param string  $salt
   * @param int     $time
   *
   * @return void
   */
  public function __construct(
    $key,
    $algorithm  = self::ALGORITHM_DEFAULT,
    $salt       = '',
    $time       = ''
  )
  {
    parent::__construct($key, $algorithm);

    $this
      ->setSalt($salt)
      ->setTime($time);
  }

  /** Accessor for the stored salt value.
   *
   * @return string
   */
  public function getSalt(  )
  {
    return $this->_salt;
  }

  /** Sets the salt value to be used to generate signatures.
   *
   * @param string $salt
   *
   * @return JsonApi_Signature_Generator_Mock($this)
   */
  public function setSalt( $salt )
  {
    $this->_salt = (string) $salt;
    return $this;
  }

  /** Accesor for the stored timestamp value.
   *
   * @return string(unixtimestamp)
   */
  public function getTime(  )
  {
    return $this->_time;
  }

  /** Sets the timestamp value to be used to generate signatures.
   *
   * @param int $time
   *
   * @return JsonApi_Signature_Generator_Mock($this)
   */
  public function setTime( $time )
  {
    $this->_time = (string) (int) $time;
    return $this;
  }

  /** Generates a salt to inject into the parameters array.
   *
   * @return string
   *
   * @see JsonApi_Signature_Generator_Mock
   */
  protected function _genSalt(  )
  {
    return $this->getSalt();
  }

  /** Generates a timestamp to inject into the parameters array.
   *
   * @return string(unixtimestamp)
   *
   * @see JsonApi_Signature_Generator_Mock
   */
  protected function _genTimestamp(  )
  {
    return $this->getTime();
  }
}