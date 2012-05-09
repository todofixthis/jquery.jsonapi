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
    DEBUG             = 'Debug',
    TPL_FORM_REQUIRED = '%form% is required.';

  /** Require the specified request method unless in dev mode.
   *
   * @param string,... $method If multiple values are passed in, the request
   *  must match at least one.
   *
   * @return void Automatically forwards to the 404 page if not valid.
   */
  protected function requireMethod(
    /** @noinspection PhpUnusedParameterInspection */
    $method /*, ... */
  )
  {
    $this->forward404Unless(
          in_array($this->getRequest()->getMethod(), func_get_args())
      or  (sfConfig::get('sf_environment')  == 'dev')
    );
  }

  /** Require POST or dev environment.
   *
   * @return void Automatically forwards to the 404 page if not valid.
   */
  protected function requirePost(  )
  {
    $this->requireMethod(sfWebRequest::POST);
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
    return $this->validate(
      $key,
      $this->getRequest()->getParameter($key),
      $validators
    );
  }

  /** Attempts to bind a form object to the request.
   *
   * Note:  If the request does not contain values for the form, the form will
   *  remain unbound.
   *
   * @param sfForm  $form
   * @param bool    $validate If true, validate the form and add failure
   *  messages for any validation failures.  This will also generate a failure
   *  message if the form could not be bound to the request.
   * @param string  $template Template for failure message if the form could not
   *  be bound to the request.
   *
   * @throws LogicException If the form class does not have a parameter name.
   * @return sfForm $form
   */
  protected function bindForm(
    sfForm  $form,
            $validate = true,
            $template = self::TPL_FORM_REQUIRED
  )
  {
    $request = $this->getRequest();

    if( ! $name = $form->getName() )
    {
      throw new LogicException(sprintf(
        'Please add "$this->widgetSchema->setNameFormat(...);" to %s->configure().'
          , get_class($form)
      ));
    }

    if( $request->hasParameter($name) )
    {
      $form->bind(
          $request->getParameter($name)
        , $request->getFiles($name)
      );
    }

    if( $validate )
    {
      if( $form->isBound() )
      {
        if( ! $form->isValid() )
        {
          $this->addFailures($form->getErrorSchema());
        }
      }
      else
      {
        $this->setFailure($name, strtr($template, array('%form%' => $name)));
      }
    }

    return $form;
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
   * @return static
   */
  protected function setFailure( $key, $message )
  {
    if( ! isset($this->failures) )
    {
      $this->failures = array();
    }

    $this->failures[$key] = $message;

    return $this;
  }

  /** Sets multiple failure messages, without removing any existing ones.
   *
   * This method will overwrite any failure messages that have the same key,
   *  however.
   *
   * @param string[]|sfValidatorErrorSchema $failures
   *
   * @return static
   */
  protected function addFailures( $failures )
  {
    if( $failures instanceof sfValidatorErrorSchema )
    {
      $failures = $this->_convertErrorSchema($failures);
    }

    foreach( (array) $failures as $key => $message )
    {
      $this->setFailure($key, $message);
    }

    return $this;
  }

  /** Sets failure messages, removing any existing ones.
   *
   * @param string[] $failures
   *
   * @return static
   */
  protected function setFailures( array $failures )
  {
    $this->failures = array();
    return $this->addFailures($failures);
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
  protected function validate( $key, $val, array $validators )
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

  /** Converts an sfValidatorErrorSchema into an array of error messages.
   *
   * @param sfValidatorErrorSchema $schema
   *
   * @return array
   */
  private function _convertErrorSchema( sfValidatorErrorSchema $schema )
  {
    $errors = array();

    foreach( $schema->getErrors() as $key => $error )
    {
      $errors[$key] = (
        ($error instanceof sfValidatorErrorSchema)
          ? $this->_convertErrorSchema($error)
          : (string) $error
      );
    }

    return $errors;
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

      $this->getRequest()->setRequestFormat('html');
      $this->getResponse()->setContentType('text/html');
      return self::DEBUG;
    }
    else
    {
      $this->getRequest()->setRequestFormat('js');
      $this->getResponse()->setContentType('application/json');
      return $this->renderText(json_encode($response));
    }
  }
}