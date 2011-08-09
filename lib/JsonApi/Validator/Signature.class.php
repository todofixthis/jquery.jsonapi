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

/** Used to validate a basic signature in a set of request parameters.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi.validator
 */
class WidgetApi_Validator_Signature extends sfValidatorBase
{
  protected function configure( $options = array(), $messages = array() )
  {
    $this->addRequiredOption('private_key');

    $this->addOption('salt_required', true);
    $this->addOption('required', true);

    $this->addOption('max_ttl', 0);

    $this->addMessage('invalid', 'Invalid request signature.');
    $this->addMessage('salt_missing', 'Add a salt to the request to increase entropy.');
    $this->addMessage('salt_invalid', 'Invalid salt provided with request.');

    $this->addMessage('timestamp_missing', 'Request timestamp not found.');
    $this->addMessage('timestamp_invalid', 'Request timestamp is invalid.');
    $this->addMessage('timestamp_expired', 'Request timestamp has expired.');
  }

  protected function doClean( $value )
  {
    if( ! is_array($value) )
    {
      throw new InvalidArgumentException(sprintf(
        'Invalid argument passed to %s::doClean():  array expected, %s encountered.',
          __CLASS__,
          gettype($value)
      ));
    }

    if( empty($value['_signature']) )
    {
      throw new sfValidatorError($this, 'required');
    }

    if( $this->getOption('salt_required') )
    {
      if( empty($value['_salt']) )
      {
        throw new sfValidatorError($this, 'salt_missing');
      }
      elseif( ! preg_match('/^[a-f\d]{40}$/', $value['_salt']) )
      {
        throw new sfValidatorError($this, 'salt_invalid');
      }
    }

    /* Validate request timestamp, if applicable. */
    if( $maxAge = max(0, $this->getOption('max_ttl')) )
    {
      if( empty($value['_timestamp']) )
      {
        throw new sfValidatorError($this, 'timestamp_missing');
      }
      elseif( ! ctype_digit((string) $value['_timestamp']) )
      {
        throw new sfValidatorError($this, 'timestamp_invalid');
      }
      elseif( (time() - $value['_timestamp']) > $maxAge )
      {
        throw new sfValidatorError($this, 'timestamp_expired');
      }
    }

    $compare = WidgetApi_Base::generateSignature(
      $value,
      $this->getOption('private_key'),
      false
    );

    if( $value['_signature'] != $compare )
    {
      throw new sfValidatorError($this, 'invalid');
    }

    return $value;
  }
}