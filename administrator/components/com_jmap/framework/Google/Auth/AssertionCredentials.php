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
 * Credentials object used for OAuth 2.0 Signed JWT assertion grants.
 *
 * @author Chirag Shah <chirags@google.com>
 */
class Google_Auth_AssertionCredentials
{
  const MAX_TOKEN_LIFETIME_SECS = 3600;

  public $serviceAccountName;
  public $scopes;
  public $privateKey;
  public $privateKeyPassword;
  public $assertionType;
  public $sub;
  /**
   * @deprecated
   * @link http://tools.ietf.org/html/draft-ietf-oauth-json-web-token-06
   */
  public $prn;
  private $useCache;

  /**
   * @param $serviceAccountName
   * @param $scopes array List of scopes
   * @param $privateKey
   * @param string $privateKeyPassword
   * @param string $assertionType
   * @param bool|string $sub The email address of the user for which the
   *              application is requesting delegated access.
   * @param bool useCache Whether to generate a cache key and allow
   *              automatic caching of the generated token.
   */
  public function __construct(
      $serviceAccountName,
      $scopes,
      $privateKey,
      $privateKeyPassword = 'notasecret',
      $assertionType = 'http://oauth.net/grant_type/jwt/1.0/bearer',
      $sub = false,
      $useCache = true
  ) {
    $this->serviceAccountName = $serviceAccountName;
    $this->scopes = is_string($scopes) ? $scopes : implode(' ', $scopes);
    $this->privateKey = $privateKey;
    $this->privateKeyPassword = $privateKeyPassword;
    $this->assertionType = $assertionType;
    $this->sub = $sub;
    $this->prn = $sub;
    $this->useCache = $useCache;
  }
  
  /**
   * Generate a unique key to represent this credential.
   * @return string
   */
  public function getCacheKey()
  {
    if (!$this->useCache) {
      return false;
    }
    $h = $this->sub;
    $h .= $this->assertionType;
    $h .= $this->privateKey;
    $h .= $this->scopes;
    $h .= $this->serviceAccountName;
    return md5($h);
  }

  public function generateAssertion()
  {
    $now = time();

    $jwtParams = array(
          'aud' => Google_Auth_OAuth2::OAUTH2_TOKEN_URI,
          'scope' => $this->scopes,
          'iat' => $now,
          'exp' => $now + self::MAX_TOKEN_LIFETIME_SECS,
          'iss' => $this->serviceAccountName,
    );

    if ($this->sub !== false) {
      $jwtParams['sub'] = $this->sub;
    } else if ($this->prn !== false) {
      $jwtParams['prn'] = $this->prn;
    }

    return $this->makeSignedJwt($jwtParams);
  }

  /**
   * Creates a signed JWT.
   * @param array $payload
   * @return string The signed JWT.
   */
  private function makeSignedJwt($payload)
  {
    $header = array('typ' => 'JWT', 'alg' => 'RS256');

    $payload = json_encode($payload);
    // Handle some overzealous escaping in PHP json that seemed to cause some errors
    // with claimsets.
    $payload = str_replace('\/', '/', $payload);

    $segments = array(
      Google_Utils::urlSafeB64Encode(json_encode($header)),
      Google_Utils::urlSafeB64Encode($payload)
    );

    $signingInput = implode('.', $segments);
    $signer = new Google_Signer_P12($this->privateKey, $this->privateKeyPassword);
    $signature = $signer->sign($signingInput);
    $segments[] = Google_Utils::urlSafeB64Encode($signature);

    return implode(".", $segments);
  }
}
