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
 * Verifies signatures using PEM encoded certificates.
 *
 * @author Brian Eaton <beaton@google.com>
 */
class Google_Verifier_Pem extends Google_Verifier_Abstract
{
  private $publicKey;

  /**
   * Constructs a verifier from the supplied PEM-encoded certificate.
   *
   * $pem: a PEM encoded certificate (not a file).
   * @param $pem
   * @throws Google_Auth_Exception
   * @throws Google_Exception
   */
  public function __construct($pem)
  {
    if (!function_exists('openssl_x509_read')) {
      throw new Google_Exception('Google API PHP client needs the openssl PHP extension');
    }
    $this->publicKey = openssl_x509_read($pem);
    if (!$this->publicKey) {
      throw new Google_Auth_Exception("Unable to parse PEM: $pem");
    }
  }

  public function __destruct()
  {
    if ($this->publicKey) {
      openssl_x509_free($this->publicKey);
    }
  }

  /**
   * Verifies the signature on data.
   *
   * Returns true if the signature is valid, false otherwise.
   * @param $data
   * @param $signature
   * @throws Google_Auth_Exception
   * @return bool
   */
  public function verify($data, $signature)
  {
    $hash = defined("OPENSSL_ALGO_SHA256") ? OPENSSL_ALGO_SHA256 : "sha256";
    $status = openssl_verify($data, $signature, $this->publicKey, $hash);
    if ($status === -1) {
      throw new Google_Auth_Exception('Signature verification error: ' . openssl_error_string());
    }
    return $status === 1;
  }
}
