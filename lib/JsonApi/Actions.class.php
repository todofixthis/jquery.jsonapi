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
 * @subpackage lib.jsonapi
 */
class JsonApi_Actions extends sfActions
{
  const
    DEBUG = 'Debug',

    ERR_ARRAY_INVALID = 'Array value not allowed.';

  /** Require POST or dev environment.
   *
   * @return void Automatically forwards to the 404 page if not valid.
   */
  protected function requirePost(  )
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
  protected function getParam( $key, $validators = array(), $allowArrayValue = false )
  {
    return $this->_validate(
      $key,
      $this->getRequest()->getParameter($key),
      $validators,
      $allowArrayValue
    );
  }

  /** Returns whether there are error messages.
   *
   * @return bool
   */
  protected function hasErrors(  )
  {
    return ! empty($this->errors);
  }

  /** Accessor for error messages.
   *
   * @return array
   */
  protected function getErrors(  )
  {
    return $this->hasErrors() ? (array) $this->errors : array();
  }

  /** Sets an error message.
   *
   * @param string $key
   * @param string $message
   *
   * @return void
   */
  protected function setError( $key, $message )
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
  protected function setErrors( array $errors )
  {
    foreach( $errors as $key => $message )
    {
      $this->setError($key, $message);
    }
  }

  /** Sends success response.
   *
   * @param array $messages Additional messages to be included in the response
   *  detail.
   *
   * @return string
   */
  protected function success( array $messages = array() )
  {
    $response = array(
      JsonApi_Response::KEY_STATUS => JsonApi_Response::STATUS_OK
    );

    if( $messages )
    {
      $response[JsonApi_Response::KEY_DETAIL] = $messages;
    }

    return $this->_renderJson($response);
  }

  /** Sends error response.
   *
   * @return string
   */
  protected function error(  )
  {
    $this->getResponse()->setStatusCode(400);

    return $this->_renderJson(array(
      JsonApi_Response::KEY_STATUS  => JsonApi_Response::STATUS_FAIL,
      JsonApi_Response::KEY_DETAIL  => array(
        JsonApi_Response_Failure::KEY_ERRORS => $this->getErrors()
      )
    ));
  }

  /** Validates an incoming parameter.
   *
   * @param string                  $key
   * @param mixed                   $val
   * @param array(sfValidatorBase)  $validators
   * @param bool|int                $array
   *
   * @todo Refactor $array functionality into separate validator.
   * @todo Do away with int $array value; if $val is an array, assume that each
   *  element should be sent to the validator array (add additional array
   *  validators to $validators to validate sub-sub-elements).
   */
  private function _validate( $key, $val, array $validators, $array )
  {
    if( is_array($val) )
    {
      if( $array > 0 )
      {
        /* Validate all elements of $val using the same set of validators. */
        $validated = array();
        foreach( $val as $subKey => $subVal )
        {
          $validated[$subKey] = $this->_validate(
            "{$key}[{$subKey}]",
            $subVal,
            $validators,
            (is_int($array) ? ($array - 1) : (bool) $array)
          );
        }
        return $validated;
      }
      else
      {
        $this->setError($key, self::ERR_ARRAY_INVALID);
        return null;
      }
    }
    else
    {
      /* @var $validator sfValidatorBase */
      foreach( (array) $validators as $validator )
      {
        try
        {
          $val = $validator->clean($val);
        }
        catch( sfValidatorError $e )
        {
          $this->setError($key, $e->getMessage());
          return null;
        }
      }

      return $val;
    }
  }

  /** Renders an array as a JSON string.
   *
   * @param array $response
   *
   * @return string 'NONE'
   */
  private function _renderJson( array $response )
  {
    if( sfConfig::get('sf_environment') == 'dev' )
    {
      $root = sfContext::getInstance()->getConfiguration()
        ->getPluginConfiguration('sfJwtJsonApiPlugin')
          ->getRootDir() . '/modules/jsonapi/templates/';

      /* Using a template so that the sfWebDebugToolbar can render as well. */
      $this->setTemplate($root . '_api');
      $this->setLayout($root . 'layout');

      $this->result = $response;

      return self::DEBUG;
    }
    else
    {
      return $this->renderText(json_encode($response));
    }
  }
}