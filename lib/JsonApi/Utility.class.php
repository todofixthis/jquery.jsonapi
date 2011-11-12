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

/** Provides miscellaneous functions that aren't specialized enough to fit with
 *    any existing class.
 *
 * @author Phoenix Zerin <phoenix@todofixthis.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi
 */
class JsonApi_Utility
{
  const
    /** Prefix added to parameter keys to mark them as internal to JsonApi. */
    KEY_PREFIX = '__jsonapi_';

  /** Creates an "internalized" version of a key name.
   *
   * @param string $key
   *
   * @return string
   */
  static public function internalKey( $key )
  {
    return self::KEY_PREFIX . $key;
  }

  /** Returns whether a variable is iterable.
   *
   * An iterable variable meets one of these criteria:
   * - Is an array.
   * - Is a stdClass instance.
   * - Is an instance of a class that implements the Iterable interface.
   *
   * @param mixed $var
   *
   * @return bool
   */
  static public function isIterable( $var )
  {
    return
    (
      is_array($var)
      or
      (
        is_object($var)
        and
        (
              class_implements($var, 'Traversable')
          or  (get_class($var) == 'stdClass')
        )
      )
    );
  }

  /** Normalize an array of request parameters.
   *
   *  - Convert all values to strings (keys are ignored because of the way PHP
   *      handles array keys).
   *  - Remove any sf_* or __jsonapi_* parameters.
   *  - Sort keys (ensures that order is not important to operations).
   *
   * @param array $params
   *
   * @return array Returns a modified *copy* of $params ($params is unmodified).
   */
  static public function normalizeParams( array $params )
  {
    /* Convert all values to strings. */
    array_walk_recursive($params, array(__CLASS__, '_stringify'));

    /* Do not use sf_* or __jsonapi_* keys. */
    $copy = array();
    $len  = strlen(self::KEY_PREFIX);
    foreach( $params as $name => $val )
    {
      $isSF = (substr($name, 0, 3) == 'sf_');
      $isJA = (substr($name, 0, $len) == self::KEY_PREFIX);

      if( ! ($isSF or $isJA) )
      {
        $copy[$name] = $val;
      }
    }

    /* Ensure that key order is not important. */
    return self::_sortify($copy);
  }

  /** Used by e.g., array_walk_recursive() to convert all values in an array to
   *    strings.
   *
   * @param mixed&  $val
   * @param mixed   $key  Note that converting the key is less important because
   *  PHP automatically converts all non-numerics into strings and all numerics
   *  into ints.
   *
   * @return void
   */
  static public function _stringify( &$val, $key )
  {
    if( self::isIterable($val) )
    {
      $copy = array();
      foreach( $val as $field => $value )
      {
        $copy[$field] = self::_stringify($value, $field);
      }
      $val = $copy;
    }
    else
    {
      $val = (string) $val;
    }
  }

  /** Sorts an array and all of its sub-arrays by key.
   *
   * @param array $array
   *
   * @return array *copy*
   */
  static public function _sortify( array $array )
  {
    uksort($array, 'strnatcasecmp');

    foreach( $array as &$value )
    {
      if( is_array($value) )
      {
        $value = self::_sortify($value);
      }
    }

    return $array;
  }
}