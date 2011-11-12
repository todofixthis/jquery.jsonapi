<?php
/**
 * jsonapi_test actions.
 *
 * @package    jsonapi-tester
 * @subpackage api
 * @author     Phoenix Zerin <phoenix@todofixthis.com>
 */
class jsonapi_testActions extends JsonApi_Actions
{
  /** Basic mock action, used to test simple validators.
   *
   * @param sfWebRequest $request
   *
   * @return string
   */
  public function executeBasic( sfWebRequest $request )
  {
    $this->requirePost();

    $result = $this->getParam('result', array(
      new sfValidatorString(array(
        'required'  => true
      ))
    ));

    if( $this->hasErrors() )
    {
      return $this->error();
    }

    return $this->success(array('result' => $result));
  }
}