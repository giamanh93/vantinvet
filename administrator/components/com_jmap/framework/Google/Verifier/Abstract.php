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
 * Verifies signatures.
 *
 * @author Brian Eaton <beaton@google.com>
 */
abstract class Google_Verifier_Abstract
{
  /**
   * Checks a signature, returns true if the signature is correct,
   * false otherwise.
   */
  abstract public function verify($data, $signature);
}
