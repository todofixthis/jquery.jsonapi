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
 * @author Phoenix Zerin <phoenix@todofixthis.com>
 *
 * @package sfJwtJsonApiPlugin
 * @subpackage lib.jsonapi
 *
 * @property string[] $failures
 * @property array $result
 *
 * @method sfWebRequest getRequest()
 * @method sfWebResponse getResponse()
 */
class JsonApi_Actions extends sfActions
{
  const
    DEBUG = 'Debug';

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
   *
   * @return mixed
   */
  protected function getParam( $key, $validators = array() )
  {
    return $this->_validate(
      $key,
      $this->getRequest()->getParameter($key),
      $validators
    );
  }

  /** Returns whether there are failure messages.
   *
   * @return bool
   */
  protected function hasFailures(  )
  {
    return ! empty($this->failures);
  }

  /** Returns all failure messages.
   *
   * @return array
   */
  protected function getFailures(  )
  {
    return $this->hasFailures() ? (array) $this->failures : array();
  }

  /** Sets a failure message.
   *
   * @param string $key
   * @param string $message
   *
   * @return void
   */
  protected function setFailure( $key, $message )
  {
    if( ! isset($this->failures) )
    {
      $this->failures = array();
    }

    $this->failures[$key] = $message;
  }

  /** Sets multiple failure messages, without removing any existing ones.
   *
   * This method will overwrite any failure messages that have the same key,
   *  however.
   *
   * @param array $failures
   *
   * @return void
   */
  protected function addFailures( array $failures )
  {
    foreach( $failures as $key => $message )
    {
      $this->setFailure($key, $message);
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

  /** Sends failure response.
   *
   * @param array $failures Additional failure messages to be included in the
   *  response detail (convenience for calling {@see addFailures()}).
   *
   * @return string
   */
  protected function failure( array $failures = array() )
  {
    if( $failures )
    {
      $this->addFailures($failures);
    }

    $this->getResponse()->setStatusCode(400);

    return $this->_renderJson(array(
      JsonApi_Response::KEY_STATUS  => JsonApi_Response::STATUS_FAIL,
      JsonApi_Response::KEY_DETAIL  => array(
        JsonApi_Response_Failure::KEY_ERRORS => $this->getFailures()
      )
    ));
  }

  /** Validates an incoming parameter.
   *
   * @param string            $key
   * @param mixed             $val
   * @param sfValidatorBase[] $validators
   *
   * @return mixed
   */
  private function _validate( $key, $val, array $validators )
  {
    /* @var $validator sfValidatorBase */
    foreach( $validators as $validator )
    {
      try
      {
        $val = $validator->clean($val);
      }
      catch( sfValidatorError $e )
      {
        $this->setFailure($key, $e->getMessage());
        return null;
      }
    }

    return $val;
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
      $this->setTemplate($root . 'api');
      $this->setLayout($root . 'layout');

      $this->result = $response;

      $this->getResponse()->setContentType('text/html');
      return self::DEBUG;
    }
    else
    {
      $this->getResponse()->setContentType('application/json');
      return $this->renderText(json_encode($response));
    }
  }
}