<?php
/**
 *  @package Productbuilder
 *  @copyright Copyright (c)2014 breakdesigns.net, Nicholas K. Dionysopoulos
 *  @license GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

/**
 * Handle commercial extension update authorization
 *
 * @package     Joomla.Plugin
 * @subpackage  Installer.Productbuilder
 * @since       2.5
 */
class plgInstallerCustomfilters extends JPlugin
{
	/**
	 * @var    String  your extension identifier, to retrieve its params
	 * @since  2.5
	 */
	private $extension = 'com_customfilters';

	/**
	 * Handle adding credentials to package download request
	 *
	 * @param   string  $url        url from which package is going to be downloaded
	 * @param   array   $headers    headers to be sent along the download request (key => value format)
	 *
	 * @return  boolean true if credentials have been added to request or not our business, false otherwise (credentials not set by user)
	 *
	 * @since   2.5
	 */
	public function onInstallerBeforePackageDownload(&$url, &$headers)
	{
		$uri = JURI::getInstance($url);

		// I don't care about download URLs not coming from our site
		// Note: as the Download ID is common for all extensions, this plugin will be triggered for all
		// extensions with a download URL on our site
		$host = $uri->getHost();
		if (!in_array($host, array('www.breakdesigns.net', 'breakdesigns.net')))
		{
			return true;
		}

		// Get the download ID
		JLoader::import('joomla.application.component.helper');
		$component = JComponentHelper::getComponent($this->extension);

		$dlid = $component->params->get('update_dlid', '');

		// If the download ID is invalid, return without any further action
		if (!preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
		{
			return true;
		}

		// Appent the Download ID to the download URL
		if (!empty($dlid))
		{
			$uri->setVar('dlid', $dlid);
			$url = $uri->toString();
		}

		return true;
	}
}
