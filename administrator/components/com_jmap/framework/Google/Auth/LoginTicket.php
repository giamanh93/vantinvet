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
 * Class to hold information about an authenticated login.
 *
 * @author Brian Eaton <beaton@google.com>
 */
class Google_Auth_LoginTicket
{
  const USER_ATTR = "sub";

  // Information from id token envelope.
  private $envelope;

  // Information from id token payload.
  private $payload;

  /**
   * Creates a user based on the supplied token.
   *
   * @param string $envelope Header from a verified authentication token.
   * @param string $payload Information from a verified authentication token.
   */
  public function __construct($envelope, $payload)
  {
    $this->envelope = $envelope;
    $this->payload = $payload;
  }

  /**
   * Returns the numeric identifier for the user.
   * @throws Google_Auth_Exception
   * @return
   */
  public function getUserId()
  {
    if (array_key_exists(self::USER_ATTR, $this->payload)) {
      return $this->payload[self::USER_ATTR];
    }
    throw new Google_Auth_Exception("No user_id in token");
  }

  /**
   * Returns attributes from the login ticket.  This can contain
   * various information about the user session.
   * @return array
   */
  public function getAttributes()
  {
    return array("envelope" => $this->envelope, "payload" => $this->payload);
  }
}
