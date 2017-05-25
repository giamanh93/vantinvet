<?php
// namespace administrator\components\com_jmap\framework\files;
/**
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage file
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

// CSV import fields
define ('COM_JMAP_METAINFO_LINK', 0);
define ('COM_JMAP_METAINFO_TITLE', 1);
define ('COM_JMAP_METAINFO_DESC', 2);
define ('COM_JMAP_METAINFO_IMAGE', 3);
define ('COM_JMAP_METAINFO_ROBOTS', 4);
define ('COM_JMAP_METAINFO_STATUS', 5);
define ('COM_JMAP_METAINFO_EXCLUDED', 6);
define ('COM_JMAP_METAINFO_FIELD_NUM', 7);

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');

/**
 * Multilanguage fallback utility class
 * 
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage file
 * @since 3.5
 */
class JMapFileMetainfo extends JObject {
	/**
	 * Database connector
	 * 
	 * @access private
	 * @var Object
	 */
	private $dbo;
	
	/**
	 * Application object
	 *
	 * @access private
	 * @var Object
	 */
	private $app;
	
	/**
	 * Store uploaded file to cache folder,
	 * fully manage error messages and ask for database insert
	 * 
	 * @access public
	 * @return boolean 
	 */
	public function import() {
		// Get file info
		$file = $this->app->input->files->get('datasourceimport', null, 'raw');
		$tmpFile = $file['tmp_name'];
		$tmpFileName = $file['name'];
		try {
			if(!$tmpFile || !$tmpFileName) {
				throw new JMapException(JText::_('COM_JMAP_NOFILE_SELECTED'), 'error');
			}
			
			$tmpFileExtension = @array_pop(explode('.', $tmpFileName));
			if($tmpFileExtension != 'csv') {
				throw new JMapException(JText::_('COM_JMAP_METAINFO_EXT_ERROR'), 'error');
			}

			// Deserialize contents
			$fileHandle = fopen($tmpFile, "r");
			if(!is_resource($fileHandle)) {
				throw new JMapException(JText::_('COM_JMAP_DATA_FILE_NOT_READABLE'), 'error');
			}
			
			// Parse the CSV files dataset into an importable array
			$skip = true;
			$dbQueryArray = array();
			while ( $csvRecord = fgetcsv ( $fileHandle, 0, ';', '"' ) ) {
				// Skip prima riga intestazioni
				if($skip) {
					$skip = false;
					continue;
				}
				//Insert alla terzultima posizione dell'id azienda da post
				array_push ( $dbQueryArray, $csvRecord );
			}
			
			// Check if some valid data to import are available
			if(!count($dbQueryArray)) {
				throw new JMapException(JText::_('COM_JMAP_METAINFO_NO_IMPORT_DATA_FOUND'), 'warning');
			}
			
			// Prepare the values array
			foreach ($dbQueryArray as $dbRecord) {
				// Ensyre at least that the number of csv fields are correct
				if(count($dbRecord) != COM_JMAP_METAINFO_FIELD_NUM) {
					continue;
				}
				
				// Check if the link as primary key already exists in this table
				$selectQuery = "SELECT" . $this->dbo->quoteName('id') .
							   "\n FROM " . $this->dbo->quoteName('#__jmap_metainfo') .
							   "\n WHERE" .
							   $this->dbo->quoteName('linkurl') . " = " . $this->dbo->quote($dbRecord[COM_JMAP_METAINFO_LINK]);
				$linkExists = $this->dbo->setQuery ( $selectQuery )->loadResult();
	
				// If the link exists just update it, otherwise insert a new one
				if($linkExists) {
					$query = "UPDATE" .
							 "\n " . $this->dbo->quoteName('#__jmap_metainfo') .
							 "\n SET " .
							 "\n " . $this->dbo->quoteName('meta_title') . " = " . $this->dbo->quote($dbRecord[COM_JMAP_METAINFO_TITLE]) . "," .
							 "\n " . $this->dbo->quoteName('meta_desc') . " = " . $this->dbo->quote($dbRecord[COM_JMAP_METAINFO_DESC]) . "," .
							 "\n " . $this->dbo->quoteName('meta_image') . " = " . $this->dbo->quote($dbRecord[COM_JMAP_METAINFO_IMAGE]) . "," .
							 "\n " . $this->dbo->quoteName('robots') . " = " . $this->dbo->quote($dbRecord[COM_JMAP_METAINFO_ROBOTS]) . "," .
							 "\n " . $this->dbo->quoteName('published') . " = " . (int)($dbRecord[COM_JMAP_METAINFO_STATUS]) . "," .
							 "\n " . $this->dbo->quoteName('excluded') . " = " . (int)($dbRecord[COM_JMAP_METAINFO_EXCLUDED]) .
							 "\n WHERE " .
							 "\n " . $this->dbo->quoteName('linkurl') . " = " . $this->dbo->quote($dbRecord[COM_JMAP_METAINFO_LINK]);
					$this->dbo->setQuery ( $query );
				} else {
					$query = "INSERT INTO" .
							 "\n " . $this->dbo->quoteName('#__jmap_metainfo') . "(" .
							 $this->dbo->quoteName('linkurl') . "," .
							 $this->dbo->quoteName('meta_title') . "," .
							 $this->dbo->quoteName('meta_desc') . "," .
							 $this->dbo->quoteName('meta_image') . "," .
							 $this->dbo->quoteName('robots') . "," .
							 $this->dbo->quoteName('published') . "," .
							 $this->dbo->quoteName('excluded') . ") VALUES (" .
							 $this->dbo->quote($dbRecord[COM_JMAP_METAINFO_LINK]) . "," .
							 $this->dbo->quote($dbRecord[COM_JMAP_METAINFO_TITLE]) . "," .
							 $this->dbo->quote($dbRecord[COM_JMAP_METAINFO_DESC]) . "," .
							 $this->dbo->quote($dbRecord[COM_JMAP_METAINFO_IMAGE]) . "," .
							 $this->dbo->quote($dbRecord[COM_JMAP_METAINFO_ROBOTS]) . "," .
							 (int)($dbRecord[COM_JMAP_METAINFO_STATUS]) . "," .
							 (int)($dbRecord[COM_JMAP_METAINFO_EXCLUDED]) . ")";
					$this->dbo->setQuery ( $query );
				}
				$this->dbo->execute ();
				if ($this->dbo->getErrorNum ()) {
					throw new JMapException(JText::sprintf('COM_JMAP_METAINFO_ERROR_STORING_DATA', $this->dbo->getErrorMsg()), 'error');
				}
			}
		}
		catch(JMapException $e) {
			$this->setError($e);
			return false;
		} catch (Exception $e) {
			$jmapException = new JMapException(JText::sprintf('COM_JMAP_METAINFO_ERROR_STORING_DATA', $e->getMessage()), 'error');
			$this->setError($jmapException);
			return false;
		}
		
		return true;
	}

	/**
	 * Class constructor
	 * 
	 * @access public
	 * @param Object $dbo
	 * @param Object $app
	 * @return Object &
	 */
	public function __construct($dbo, $app) {
		// DB connector
		$this->dbo = $dbo;
		
		// Application
		$this->app = $app;
	}
}