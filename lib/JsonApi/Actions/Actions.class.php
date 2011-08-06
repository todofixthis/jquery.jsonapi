<?php
/** Defines custom behavior specific to all API actions.
 *
 * @package jwt
 * @subpackage app.lib
 *
 * @todo Remove validator message nonsense.
 * @todo Convert array validation into custom validator.
 */
class JsonApi_Actions extends sfActions
{
  const
    STATUS_OK     = 'OK',
    STATUS_ERROR  = 'ERROR',

    DEBUG = 'Debug',

    KEY_ARRAY_INVALID = 'array_invalid',
    ERR_ARRAY_INVALID = 'Array value not allowed.';

  private
    $_validatorMessages = array();

  /** Common stuff that should be done before executing the action.
   *
   * @return void
   *
   * @todo Slugifying error messages is not generally desirable behavior.  See
   *  if this behavior can be removed safely, or better - return errors in
   *  (slug, human-readable) format.
   */
  public function preExecute(  )
  {
    $this->_setValidatorMessages(array(
      'invalid'       => 'invalid',
      'required'      => 'missing',
      'min'           => 'too-small',
      'min_length'    => 'too-small',
      'max'           => 'too-big',
      'max_length'    => 'too-big',

      /* Triggered if an array is found where a scalar was expected. */
      self::KEY_ARRAY_INVALID => 'array-invalid'
    ));
  }

  /** Accessor for $_validatorMessages.
   *
   * @return array
   */
  protected function _getValidatorMessages(  )
  {
    return $this->_validatorMessages;
  }

  /** Returns a specific validator message.
   *
   * @param string $key
   * @param string $fallback
   *
   * @return string
   */
  protected function _getValidatorMessage( $key, $fallback = '' )
  {
    return
      isset($this->_validatorMessages[$key])
        ? $this->_validatorMessages[$key]
        : $fallback;
  }

  /** Mutator for $_validatorMessages.
   *
   * @param array $messages
   *
   * @return void
   */
  protected function _setValidatorMessages( array $messages )
  {
    $this->_validatorMessages = $messages;
  }

  /** Require POST or dev environment.
   *
   * @return void Automatically forwards to the 404 page if not valid.
   */
  protected function _requirePost(  )
  {
    $this->forward404Unless(
          $this->getRequest()->getMethod() == sfWebRequest::POST
      or  sfConfig::get('sf_environment') == 'dev'
    );
  }

  /** Get and validate a request parameter.
   *
   * @param string    $key
   * @param array     $validators
   * @param bool|int  $allowArrayValue
   *  - true:   array values are allowed.
   *  - false:  array values are not allowed.
   *  - (int):  array values allowed, but only this many levels of nesting.
   *
   * @return mixed
   */
  protected function _getParam( $key, $validators = array(), $allowArrayValue = false )
  {
    return $this->_validate(
      $key,
      $this->getRequest()->getParameter($key),
      $validators,
      $allowArrayValue
    );
  }

  /** Runs a set of validators on a value.
   *
   * @param string    $name
   * @param mixed     $var
   * @param array     $validators
   * @param bool|int  $allowArrayValue
   *
   * @return mixed
   */
  protected function _validate( $name, $var, array $validators, $allowArrayValue = false )
  {
    if( is_array($var) )
    {
      if( $allowArrayValue > 0 )
      {
        $validated = array();
        foreach( $var as $subKey => $subVal )
        {
          $validated[$subKey] = $this->_validate(
            "{$name}[{$subKey}]",
            $subVal,
            $validators,
            is_int($allowArrayValue) ? ($allowArrayValue - 1) : (bool) $allowArrayValue
          );
        }
        return $validated;
      }
      else
      {
        $this->_setError(
          $name,
          $this->_getValidatorMessage(
            self::KEY_ARRAY_INVALID,
            self::ERR_ARRAY_INVALID
          )
        );
        return null;
      }
    }
    else
    {
      $messages = $this->_getValidatorMessages();

      foreach( (array) $validators as $Validator )
      {
        /* Iterate over $messages instead of using setDefaultMessages() to avoid
         *  erasing any messages that are not included in $messages but supported
         *  by the $Validator.
         */
        foreach( $messages as $code => $message )
        {
          $Validator->setDefaultMessage($code, $message);
        }

        try
        {
          $var = $Validator->clean($var);
        }
        catch( sfValidatorError $e )
        {
          $this->_setError($name, $e->getMessage());
          return null;
        }
      }

      return $var;
    }
  }

  /** Fetch a numeric parameter from the request.
   *
   * @param string $key
   * @param array  $params Passed to sfValidatorNumber.
   *
   * @return int|float
   */
  protected function _getNumericParam( $key, array $params = array() )
  {
    return
      $this->_getParam($key, array(
        new sfValidatorNumber($params, array(
          'invalid' => 'malformed'
        ))
      ));
  }

  /** Returns whether there are error messages.
   *
   * @return bool
   */
  protected function _hasErrors(  )
  {
    return ! empty($this->errors);
  }

  /** Accessor for error messages.
   *
   * @return array
   */
  protected function _getErrors(  )
  {
    return $this->_hasErrors() ? (array) $this->errors : array();
  }

  /** Sets an error message.
   *
   * @param string $key
   * @param string $message
   *
   * @return void
   */
  protected function _setError( $key, $message )
  {
    if( ! isset($this->errors) )
    {
      $this->errors = array();
    }

    $this->errors[$key] = $message;
  }

  /** Sets multiple error messages.
   *
   * @param array $errors
   *
   * @return void
   */
  protected function _setErrors( array $errors )
  {
    foreach( $errors as $key => $message )
    {
      $this->_setError($key, $message);
    }
  }

  /** Generic success notifier, shared among API calls.
   *
   * @param array $messages Additional messages to be included in the response.
   *
   * @return string
   */
  protected function _success( array $messages = array() )
  {
    return $this->_renderJson(array_merge(
      array('status' => self::STATUS_OK),
      $messages
    ));
  }

  /** Generic error notifier, shared among API calls.
   *
   * @return string
   */
  protected function _error(  )
  {
    $this->getResponse()->setStatusCode(400);

    return $this->_renderJson(array(
      'status'  => self::STATUS_ERROR,
      'errors'  => $this->_getErrors()
    ));
  }

  /** Renders an array as a JSON string.
   *
   * @param array $response
   *
   * @return string 'NONE'
   */
  protected function _renderJson( array $response )
  {
    if( sfConfig::get('sf_environment') == 'dev' )
    {
      $this->setTemplate('_api', 'jsonapi');

      $this->result = $response;

      return self::DEBUG;
    }
    else
    {
      return $this->renderText(json_encode($response));
    }
  }
}