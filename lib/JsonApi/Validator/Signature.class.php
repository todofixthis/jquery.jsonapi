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

  /** @todo This was moved from JsonApi_Bsae.  Consolidate into validator or
   *    separate class as appropriate.
   */
//  /** Generates a signature for an array of parameters.
//   *
//   * A signature is composed of 2 or 3 elements:
//   *  - _timestamp: Limits the window of opportunity for an attacker to re-use
//   *     or exploit an intercepted API request.
//   *  - _signature: The actual signature.
//   *  - _salt:      Optional (but recommended) random string increases entropy.
//   *
//   * @param array   $params
//   * @param string  $publicKey
//   * @param bool    $returnAll If true, returns the entire array with signature
//   *  element.  If false, only returns the signature value.
//   * @param bool    $addSalt   If true, a salt element will be added to increase
//   *  entropy.  Ignored if $returnAll is false.
//   *
//   * Note that a timestamp is always required unless %APP_JSONAPI_REQUEST_TTL%
//   *  is 0.
//   *
//   * For best results, the value of %APP_JSONAPI_REQUEST_TTL% should be
//   *  identical on all servers that use JsonApi, but it is not technically
//   *  required for the API to function properly.
//   *
//   * @return array|string
//   * @throws DomainException if $returnAll is false, and the request contains
//   *  an invalid (or missing) timestamp, depending on the value of
//   *  %APP_JSONAPI_REQUEST_TTL%.
//   */
//  static public function generateSignature(
//    array $params,
//          $publicKey,
//          $returnAll = true,
//          $addSalt   = true
//  )
//  {
//    /* Convert each value to a string before generating the signature; that is
//     *  how the values will be evaluated on the other server.
//     */
//    array_walk_recursive(
//      $params,
//      create_function('&$val, $key', '$val = (string) $val;')
//    );
//
//    /* Operate on a copy of $params (to avoid returning an incorrectly-
//     *  truncated $params if $returnAll is true.
//     */
//    $copy = $params;
//
//    /* Remove Symfony-specific values and any existing signature. */
//    unset($copy['_signature'], $copy['action'], $copy['module']);
//    foreach( array_keys($copy) as $key )
//    {
//      if( substr($key, 0, 3) == 'sf_' )
//      {
//        unset($copy[$key]);
//      }
//    }
//
//    /* Add salt if applicable. */
//    if( $returnAll and $addSalt )
//    {
//      $params['_salt']  =
//      $copy['_salt']    = sha1(uniqid('', true));
//    }
//
//    /* Add/validate timestamp, if applicable. */
//    if( $returnAll )
//    {
//      $params['_timestamp'] =
//      $copy['_timestamp']   = (string) time();
//    }
//    elseif( $maxAge = max(0, (int) sfConfig::get('app_jsonapi_request_ttl')) )
//    {
//      if( empty($copy['_timestamp']) )
//      {
//        throw new DomainException('Request does not have a timestamp.');
//      }
//      elseif( ! ctype_digit($copy['_timestamp']) )
//      {
//        throw new DomainException('Request timestamp is invalid.');
//      }
//      elseif( (time() - $copy['_timestamp']) > $maxAge )
//      {
//        throw new DomainException('Request timestamp is expired.');
//      }
//    }
//
//    /* Sort keys so that key order does not affect signature. */
//    ksort($copy);
//
//    /* Generate the signature. */
//    $params['_signature'] = sha1(trim($publicKey) . serialize($copy));
//
//    return $returnAll ? $params : $params['_signature'];
//  }
}