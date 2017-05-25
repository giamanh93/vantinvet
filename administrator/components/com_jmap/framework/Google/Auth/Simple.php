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
 * Simple API access implementation. Can either be used to make requests
 * completely unauthenticated, or by using a Simple API Access developer
 * key.
 * @author Chris Chabot <chabotc@google.com>
 * @author Chirag Shah <chirags@google.com>
 */
class Google_Auth_Simple extends Google_Auth_Abstract
{
  private $key = null;
  private $client;

  public function __construct(Google_Client $client, $config = null)
  {
    $this->client = $client;
  }

  /**
   * Perform an authenticated / signed apiHttpRequest.
   * This function takes the apiHttpRequest, calls apiAuth->sign on it
   * (which can modify the request in what ever way fits the auth mechanism)
   * and then calls apiCurlIO::makeRequest on the signed request
   *
   * @param Google_Http_Request $request
   * @return Google_Http_Request The resulting HTTP response including the
   * responseHttpCode, responseHeaders and responseBody.
   */
  public function authenticatedRequest(Google_Http_Request $request)
  {
    $request = $this->sign($request);
    return $this->io->makeRequest($request);
  }

  public function sign(Google_Http_Request $request)
  {
    $key = $this->client->getClassConfig($this, 'developer_key');
    if ($key) {
      $request->setQueryParam('key', $key);
    }
    return $request;
  }
}
