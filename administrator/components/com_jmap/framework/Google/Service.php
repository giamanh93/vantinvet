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

class Google_Service
{
  public $version;
  public $servicePath;
  public $availableScopes;
  public $resource;
  private $client;

  public function __construct(Google_Client $client)
  {
    $this->client = $client;
  }

  /**
   * Return the associated Google_Client class.
   * @return Google_Client
   */
  public function getClient()
  {
    return $this->client;
  }
}
