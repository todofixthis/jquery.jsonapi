<?php
/** Symfony 1.0-compatible validator for checking request signatures.
 *
 * @package jwt
 * @subpackage lib.jsonapi
 *
 * @todo Replace with crypto validator.
 */
class JsonApi_Validator_Signature extends sfValidator
{
  /** Executes the validator.
   *
   * @param mixed&  $value
   * @param string& $error
   *
   * @return bool
   */
  public function execute( &$value, &$error )
  {
    if( ! is_array($value) )
    {
      throw new InvalidArgumentException(sprintf(
        'Invalid argument passed to %s::doClean():  array expected, %s encountered.',
          __CLASS__,
          gettype($value)
      ));
    }

    $Params = $this->getParameterHolder();

    if( empty($value['_signature']) )
    {
      $error = $Params->get('missing_err');
      return false;
    }

    if( $Params->get('salt_required') )
    {
      if( empty($value['_salt']) )
      {
        $error = $Params->get('salt_missing_err');
        return false;
      }
      elseif( ! preg_match('/^[a-f\d]{40}$/', $value['_salt']) )
      {
        $error = $Params->get('salt_invalid_err');
        return false;
      }
    }

    $test = $value['_signature'];

    $compare = JsonApi_Base::generateSignature(
      $value,
      $Params->get('public_key'),
      false
    );

    /* For some reason, JsonApi_Base::generateSignature() will modify $value,
     *  even if we explicitly make a copy of it.  Might be a PHP 5.3 bug.
     */
    $value['_signature'] = $test;

    if( $value['_signature'] != $compare )
    {
      $error = $Params->get('invalid_err');
      return false;
    }

    return true;
  }

  /** Initialize validator configuration options.
   *
   * @param sfContext $context
   * @param array     $parameters
   *
   * @return bool
   */
  public function initialize( $context, $parameters = array() )
  {
    parent::initialize($context);

    $defaults = array(
      'public_key'    => null,
      'salt_required' => true,
      'required'      => true,

      'missing_err'       => 'Request signature missing.',
      'invalid_err'       => 'Invalid request signature.',
      'salt_missing_err'  => 'Add a salt to the request to increase entropy.',
      'salt_invalid_err'  => 'Invalid salt provided with request.'
    );

    $parameters = array_merge(
      $defaults,
      array_intersect_key($parameters, $defaults)
    );

    foreach( $parameters as $key => $value )
    {
      if( empty($value) and ! is_bool($defaults[$key]) )
      {
        throw new sfValidatorException(sprintf('Missing parameter "%s".', $key));
      }

      $this->getParameterHolder()->set($key, $value);
    }

    return true;
  }
}