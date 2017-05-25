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

use google\appengine\api\app_identity\AppIdentityService;

/**
 * Authentication via the Google App Engine App Identity service.
 */
class Google_Auth_AppIdentity extends Google_Auth_Abstract
{
  const CACHE_PREFIX = "Google_Auth_AppIdentity::";
  const CACHE_LIFETIME = 1500;
  private $key = null;
  private $client;
  private $token = false;
  private $tokenScopes = false;

  public function __construct(Google_Client $client, $config = null)
  {
    $this->client = $client;
  }

  /**
   * Retrieve an access token for the scopes supplied.
   */
  public function authenticateForScope($scopes)
  {
    if ($this->token && $this->tokenScopes == $scopes) {
      return $this->token;
    }
    $memcache = new Memcached();
    $this->token = $memcache->get(self::CACHE_PREFIX . $scopes);
    if (!$this->token) {
      $this->token = AppIdentityService::getAccessToken($scopes);
      if ($this->token) {
        $memcache->set(self::CACHE_PREFIX . $scopes, $this->token, self::CACHE_LIFETIME);
      }
    }
    $this->tokenScopes = $scopes;
    return $this->token;
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
    if (!$this->token) {
      // No token, so nothing to do.
      return $request;
    }
    // Add the OAuth2 header to the request
    $request->setRequestHeaders(
        array('Authorization' => 'Bearer ' . $this->token['access_token'])
    );

    return $request;
  }
}
