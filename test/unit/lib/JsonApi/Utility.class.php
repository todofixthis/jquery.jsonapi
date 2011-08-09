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

/** Unit tests for JsonApi_Utility.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage test.lib.jsonapi
 */
class JsonApi_UtilityTest extends Test_Case_Unit
{
  public function testIsIterable()
  {
    $this->assertTrue(JsonApi_Utility::isIterable(array()),
      'Expected array to be iterable.'
    );

    $this->assertTrue(JsonApi_Utility::isIterable(new stdClass()),
      'Expected stdClass instance to be iterable.'
    );

    $this->assertTrue(JsonApi_Utility::isIterable(new ArrayObject()),
      'Expected instance of class implementing Traversable to be iterable.'
    );

    $this->assertFalse(JsonApi_Utility::isIterable('hello'),
      'Expected scalar to be not iterable.'
    );

    $this->assertFalse(JsonApi_Utility::isIterable(new JsonApi_Exception()),
      'Expected instance of class not implementing Traversable to be not iterable.'
    );
  }

  public function test_stringifyString()
  {
    $ctrl = $test = array('hello');
    array_walk_recursive($test, array('JsonApi_Utility', '_stringify'));

    $this->assertSame($ctrl, $test,
      'Expected string value to be unchanged when stringified.'
    );
  }

  public function test_stringifyScalar()
  {
    $test = array(5);
    array_walk_recursive($test, array('JsonApi_Utility', '_stringify'));
    $this->assertSame(array('5'), $test,
      'Expected scalar value to be converted to string when stringified.'
    );
  }

  public function test_stringifyStringableObject()
  {
    $test = array(new TestStringable());
    array_walk_recursive($test, array('JsonApi_Utility', '_stringify'));

    $this->assertSame(array(TestStringable::STR), $test,
      'Expected instance of stringable class to be converted to string when stringified.'
    );
  }

  public function test_stringifyMultidimensionalArray()
  {
    $test = array(array(5, 10), array(15, 20));
    array_walk_recursive($test, array('JsonApi_Utility', '_stringify'));

    $this->assertSame(array(array('5', '10'), array('15', '20')), $test,
      'Expected sub-array values to be stringified.'
    );
  }

  public function test_stringifyIterableObject()
  {
    $test = array(new ArrayObject(array(5, 10)));
    array_walk_recursive($test, array('JsonApi_Utility', '_stringify'));

    $this->assertSame(array(array('5', '10')), $test,
      'Expected instance of iterable class to be iterated when stringified.'
    );
  }
}

/** Used to test JsonApi_Utility::_stringify().
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage test.lib.jsonapi
 */
class TestStringable
{
  const
    STR = 'Hello, World!';

  public function __toString(  )
  {
    return self::STR;
  }
}