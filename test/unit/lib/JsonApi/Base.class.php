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

/** Unit tests for JsonApi_Base.
 *
 * @author Phoenix Zerin <phoenix@todofixthis.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage test.lib.jsonapi
 */
class JsonApi_BaseTest extends Test_Case_Unit
{
  protected
    $_plugin = 'sfJwtJsonApiPlugin',
    $_class,
    $_hostname;

  protected function _setUp(  )
  {
    $this->_class     = 'JsonApi_TestApi';
    $this->_hostname  = 'localhost';

    JsonApi_Base::getInstance($this->_class)
      ->getHttpClient()
        ->setHostname($this->_hostname);
  }

  public function testGetUriFor()
  {
    $timezone = 'America/DFW';

    $uri = JsonApi_Base::getUriFor($this->_class, 'getTime', array($timezone));

    $this->assertEquals(
      'http://localhost/time/get?timezone=' . urlencode($timezone),
      $uri,
      'Expected correct URI reported.'
    );
  }

  public function testGetUriForRequiresJsonApiClassname()
  {
    try
    {
      JsonApi_Base::getUriFor('sfContext', 'getInstance');
      $this->fail(
        'Expected InvalidArgumentException when invoking on a non-JsonApi class.'
      );
    }
    catch( InvalidArgumentException $e )
    {
    }
  }
}

/** Used to perform tests on JsonApi_Base.
 */
class JsonApi_TestApi extends JsonApi_Base
{
  /** Send a web request to find out the time.
   *
   * @param string $timezone
   *
   * @return string(timestamp)
   */
  static public function getTime( $timezone = null )
  {
    return self::_doApiCall(__CLASS__, '/time/get', compact('timezone'), 'get');
  }
}