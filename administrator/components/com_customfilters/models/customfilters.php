<?php
/**
 * The Customfilters model file
 *
 * @package 	customfilters
 * @author		Sakis Terz
 * @link		http://breakdesigns.net
 * @copyright	Copyright (c) 2012 - 2014 breakdesigns.net. All rights reserved.
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @version $Id: customfilters.php 2014-06-03 18:34 sakis $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;


// Load the model framework
jimport('joomla.application.component.modellist');

/**
 * The basic model class
 *
 * @author	Sakis Terz
 * @since	1.0
 */
class CustomfiltersModelCustomfilters extends JModelList{
	/**
	 * @var string Model context string
	 */
	var $extension='com_customfilters';
	var $name='Custom Filters';

	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
			'filter_id',
			'alias',
			'ordering',
			'data_type',
			'custom_title',
			'field_type',
			'type_id',
			'published',
            'custom_id');
		}
		parent::__construct($config);

	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.0
	 */
	protected function populateState($ordering = null, $direction = null){
		// Initialise variables.

		$app = JFactory::getApplication('administrator');

		// Adjust the context to support modal layouts.
		if ($layout = JRequest::getVar('layout', 'default')) {
			$this->context .= '.'.$layout;
		}

		// Load the filter published.
		$this->setState('filter.published', $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', ''));

		// Load the filter search.
		$this->setState('filter.search', $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search', ''));

		// Load the filter published.
		$this->setState('filter.type_id', $app->getUserStateFromRequest($this->context.'.filter.type_id', 'filter_type_id', ''));

		parent::populateState('ordering','ASC');
	}


	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 * @author	Sakis Terz
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.type_id');
		return parent::getStoreId($id);
	}



	

	/**
	 * Function that returns version info in JSON format
	 * @return string
	 * @since 1.3.1
	 */
	function getVersionInfo($updateFrequency=2){
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'update.php');
		$version_info=array();
		$html='';
		$html_current='';
		$html_outdated='';
		$pathToXML=JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'customfilters.xml';
		$installData=JApplicationHelper::parseXMLInstallFile($pathToXML);

		$updateHelper=extensionUpdateHelper::getInstance($extension='customfilters_starter',$targetFile='assets/lastversion.ini',$updateFrequency=2);
		$updateRegistry=$updateHelper->getData(); 

		if($installData['version']){
			if(is_object($updateRegistry) && $updateRegistry!==false){
				$isoutdated_code=version_compare($installData['version'], $updateRegistry->version);
				if($isoutdated_code<0){
					$html_current='<div class="cfversion">
					<span class="pbversion_label">'.JText::_('COM_CUSTOMFILTERS_LATEST_VERSION') .' : v. </span>
					<span class="cfversion_no">'.$updateRegistry->version.'</span><span> ('.$updateRegistry->date.')</span>
					</div>';
				}

				if($isoutdated_code<0)$html_outdated=' <span id="cfoutdated">!Outdated</span>';
				else $html_outdated=' <span id="cfupdated">Updated</span>';
			}

			$html.='<div class="cfversion">
			<span class="pbversion_label">'.JText::_('COM_CUSTOMFILTERS_CURRENT_VERSION') .' : v. </span> 
			<span class="cfversion_no">'.$installData['version'].'</span><span> ('.$installData['creationDate'].')</span>'.$html_outdated.
			'</div>';			

		}
		$html.=$html_current;
		$version_info['html']=$html;
		$version_info['status_code']=$isoutdated_code;
		return $version_info;
	}


	/**
	 * Does the user need to enter a Download ID in the component's Options page?
	 *
	 * @return bool
	 */
	public function needsDownloadID()
	{
		// Do I need a Download ID?
		$ret = true;

		JLoader::import('joomla.application.component.helper');
		$dlid = cfHelper::getValue('update_dlid', '');

		if(preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
		{
			$ret = false;
		}

		return $ret;
	}

	/**
	 * Refreshes the Joomla! update sites for this extension as needed
	 *
	 * @return  void
	 */
	public function refreshUpdateSite()
	{
		JLoader::import('joomla.application.component.helper');
		$dlid = cfHelper::getValue('update_dlid', '');
		$extra_query = null;
		

		// If I have a valid Download ID I will need to use a non-blank extra_query in Joomla! 3.2+
		if (preg_match('/^([0-9]{1,}:)?[0-9a-f]{32}$/i', $dlid))
		{
			$extra_query = 'dlid=' . $dlid;
		}

		// Create the update site definition we want to store to the database
		$update_site = array(
			'name'		=> $this->name,
			'type'		=> 'extension',
			'location'	=> 'http://breakdesigns.net/index.php?option=com_ars&view=update&task=stream&format=xml&id=2',
			'enabled'	=> 1,
			'last_check_timestamp'	=> 0,
			'extra_query'	=> $extra_query
		);
		
		//in joomla 2.5 the update operation is handled by a plugin, because of the absence of the extra query field in the db table
		if (version_compare(JVERSION, '3.0.0', 'lt'))
		{
			unset($update_site['extra_query']);
		}		
		
		$extension_id=$this->getExtensionId();
		
		if (empty($extension_id))
		{
			return;
		}
		
		$db = $this->getDbo();
		// Get the update sites for our extension
		$query = $db->getQuery(true)
		->select($db->quoteName('update_site_id'))
		->from($db->quoteName('#__update_sites_extensions'))
		->where($db->quoteName('extension_id') . ' = ' . $db->quote($extension_id));
		$db->setQuery($query);

		$updateSiteIDs = $db->loadColumn(0);
		

		if (!count($updateSiteIDs))
		{
			// No update sites defined. Create a new one.
			$newSite = (object)$update_site;
			$db->insertObject('#__update_sites', $newSite);

			$id = $db->insertid();

			$updateSiteExtension = (object)array(
				'update_site_id'	=> $id,
				'extension_id'		=> $extension_id,
			);
			$db->insertObject('#__update_sites_extensions', $updateSiteExtension);
		}
		else
		{
			// Loop through all update sites
			foreach ($updateSiteIDs as $id)
			{
				$query = $db->getQuery(true)
				->select('*')
				->from($db->quoteName('#__update_sites'))
				->where($db->quoteName('update_site_id') . ' = ' . $db->quote($id));
				$db->setQuery($query);
				$aSite = $db->loadObject();

				// Does the name and location match?
				if (($aSite->name == $update_site['name']) && ($aSite->location == $update_site['location']))
				{
					
					// Do we have the extra_query property (J 3.2+) and does it match?
					if (property_exists($aSite, 'extra_query'))
					{
						if ($aSite->extra_query == $update_site['extra_query'])
						{
							continue;
						}
					}
					else
					{
						// Joomla! 3.1 or earlier. Updates may or may not work.
						continue;
					}
				}
				
				$update_site['update_site_id'] = $id;
				$newSite = (object)$update_site;
				$db->updateObject('#__update_sites', $newSite, 'update_site_id', true);
			}
		}
	}

	/**
	 * Get the update id from the updates table
	 *
	 * @param string $extension
	 * @param string $type
	 * @since 2.1.0
	 */
	public function getUpdateId($extension='com_customfilters', $type='component'){
		// Get the update ID to ourselves
		$db=$this->getDbo();
		$query = $db->getQuery(true);
		$query
		->select($db->quoteName('update_id'))
		->from($db->quoteName('#__updates'))
		->where($db->quoteName('type') . ' = ' . $db->quote($type))
		->where($db->quoteName('element') . ' = ' . $db->quote($extension));
		$db->setQuery($query);

		$update_id = $db->loadResult();

		if (empty($update_id))
		{
			return false;
		}
		return $update_id;
	}

	/**
	 * Get the update id from the updates table
	 *
	 * @param string $extension
	 * @param string $type
	 * @since 2.1.0
	 */
	public function getExtensionId($extension='com_customfilters', $type='component'){
		// Get the extension ID to ourselves
		$db=$this->getDbo();
		$query = $db->getQuery(true)
		->select($db->quoteName('extension_id'))
		->from($db->quoteName('#__extensions'))
		->where($db->quoteName('type') . ' = ' . $db->quote($type))
		->where($db->quoteName('element') . ' = ' . $db->quote($extension));
		$db->setQuery($query);

		$extension_id = $db->loadResult();

		if (empty($extension_id))
		{
			return;
		}
		return $extension_id;
	}

	/**
	 * Checks if the download ID provisioning plugin for the updates of this extension is published. If not, it will try
	 * to publish it automatically. It reports the status of the plugin as a boolean.
	 *
	 * @return  bool
	 */
	public function isUpdatePluginEnabled()
	{
		// We can't be bothered about the plugin in Joomla! 2.5.0 through 2.5.19
		if (version_compare(JVERSION, '2.5.19', 'lt'))
		{
			return true;
		}

		// We can't be bothered about the plugin in Joomla! 3.x
		if (version_compare(JVERSION, '3.0.0', 'gt'))
		{
			return true;
		}

		$db = $this->getDbo();

		// Let's get the information of the update plugin
		$query = $db->getQuery(true)
		->select('*')
		->from($db->quoteName('#__extensions'))
		->where($db->quoteName('folder').' = '.$db->quote('installer'))
		->where($db->quoteName('element').' = '.$db->quote('customfilters'))
		->where($db->quoteName('type').' = '.$db->quote('plugin'))
		->order($db->quoteName('ordering').' ASC');
		$db->setQuery($query);
		$plugin = $db->loadObject();

		// If the plugin is missing report it as unpublished (of course!)
		if (!is_object($plugin))
		{
			return false;
		}

		// If it's enabled there's nothing else to do
		if ($plugin->enabled)
		{
			return true;
		}

		// Otherwise, try to enable it and report false (so the user knows what he did wrong)
		$pluginObject = (object)array(
			'extension_id'	=> $plugin->extension_id,
			'enabled'		=> 1
		);

		try
		{
			$result = $db->updateObject('#__extensions', $pluginObject, 'extension_id');
			// Do not remove this line. We need to tell the user he's doing something wrong.
			$result = false;
		}
		catch (Exception $e)
		{
			$result = false;
		}

		return $result;
	}

}
