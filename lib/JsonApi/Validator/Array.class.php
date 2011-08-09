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

/** Used to validate an array of incoming values using an identical set of
 *    validators for each element.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi.validator
 *
 * @todo Implement this.
 */
class JsonApi_Validator_Array extends sfValidatorBase
{
  protected function configure( $options = array(), $messages = array() )
  {
  }

  protected function doClean( $value )
  {
    /** @todo Moved from JsonApi_Actions.  Convert for validator. */
//    if( is_array($var) )
//    {
//      if( $allowArrayValue > 0 )
//      {
//        $validated = array();
//        foreach( $var as $subKey => $subVal )
//        {
//          $validated[$subKey] = $this->validate(
//            "{$name}[{$subKey}]",
//            $subVal,
//            $validators,
//            is_int($allowArrayValue) ? ($allowArrayValue - 1) : (bool) $allowArrayValue
//          );
//        }
//        return $validated;
//      }
//      else
//      {
//        $this->_setError(
//          $name,
//          self::ERR_ARRAY_INVALID
//        );
//        return null;
//      }
//    }
//    else
//    {
//      foreach( (array) $validators as $Validator )
//      {
//        try
//        {
//          $var = $Validator->clean($var);
//        }
//        catch( sfValidatorError $e )
//        {
//          $this->setError($name, $e->getMessage());
//          return null;
//        }
//      }
//
//      return $var;
//    }

    return $value;
  }
}