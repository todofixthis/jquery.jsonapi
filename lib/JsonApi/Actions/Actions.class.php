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

/** Defines custom behavior specific to all API actions.
 *
 * @author Phoenix Zerin <phoenix.zerin@jwt.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi.actions
 *
 * @todo Convert array validation into custom validator.
 * @todo Add crypto functionality.
 */
class JsonApi_Actions extends sfActions
{
  const
    STATUS_OK     = 'OK',
    STATUS_ERROR  = 'ERROR',

    DEBUG = 'Debug',

    KEY_ARRAY_INVALID = 'array_invalid',
    ERR_ARRAY_INVALID = 'Array value not allowed.';

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
      foreach( (array) $validators as $Validator )
      {
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

  /** Sends success response.
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

  /** Sends error response.
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
      /* Using a template so that the sfWebDebugToolbar can render as well. */
      $this->setTemplate(
        sfContext::getInstance()->getConfiguration()
          ->getPluginConfiguration('sfJwtJsonApiPlugin')
            ->getRootDir() . '/modules/jsonapi/templates/_api'
      );

      $this->result = $response;

      return self::DEBUG;
    }
    else
    {
      return $this->renderText(json_encode($response));
    }
  }
}