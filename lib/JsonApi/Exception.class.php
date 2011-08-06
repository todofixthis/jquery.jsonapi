<?php
/** Base class for all JsonApi exceptions, used when a try...catch block should
 *   be able to catch any JsonApi-specific exception without having to resort to
 *   `catch( Exception $e )`.
 *
 * @package cms
 * @subpackage lib.jsonapi
 */
class JsonApi_Exception extends Exception
{
}