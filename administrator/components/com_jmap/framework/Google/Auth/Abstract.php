<?php
// namespace administrator\components\com_jmap\framework\google;
/**
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage google
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ();

/**
 * Abstract class for the Authentication in the API client
 * @author Chris Chabot <chabotc@google.com>
 *
 */
abstract class Google_Auth_Abstract
{
  /**
   * An utility function that first calls $this->auth->sign($request) and then
   * executes makeRequest() on that signed request. Used for when a request
   * should be authenticated
   * @param Google_Http_Request $request
   * @return Google_Http_Request $request
   */
  abstract public function authenticatedRequest(Google_Http_Request $request);
  abstract public function sign(Google_Http_Request $request);
}
