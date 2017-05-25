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
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');

/**
 * Multilanguage fallback utility class
 * 
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage file
 * @since 3.0
 */
class JMapFileSources extends JObject {
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
	 * Variables in request array
	 *
	 * @access protected
	 * @var Object
	 */
	protected $requestArray;
	
	/**
	 * Variables in request array name
	 *
	 * @access protected
	 * @var Object
	 */
	protected $requestName;
	
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
			
			$tmpFileSize = $file['size'];
			$allowedFileSize = 2 * 1024 * 1024; // MB->Bytes
			if($tmpFileSize > $allowedFileSize) {
				throw new JMapException(JText::_('COM_JMAP_SIZE_ERROR') .' Max 2MB.', 'error');
			}
			
			$tmpFileExtension = @array_pop(explode('.', $tmpFileName));
			if($tmpFileExtension != 'json') {
				throw new JMapException(JText::_('COM_JMAP_EXT_ERROR'), 'error');
			}

			// Deserialize contents
			$fileContents = file_get_contents($tmpFile);
			if($fileContents) {
				$objectToRestore = json_decode($fileContents);
			}
			
			if(!is_array($objectToRestore)) {
				throw new JMapException(JText::_('COM_JMAP_INVALID_IMPORT_DATA'), 'error');
			}
			
			// Prepare the values array
			$dbQueryArray = array();
			foreach ($objectToRestore as $dataSource) {
				// Check if the data source is of type plugin and if the plugin is installed, otherwise skip and warn user
				if($dataSource->type == 'plugin' && !JFolder::exists(JPATH_COMPONENT_ADMINISTRATOR . '/plugins/' . strtolower($dataSource->name))) {
					$this->app->enqueueMessage(JText::sprintf('COM_JMAP_NOPLUGIN_INSTALLED_IMPORTED_DATASOURCE', $dataSource->name));
					continue;
				}
				
				$dbSourceArray = array();
				$dbSourceArray[] = $this->dbo->quote($dataSource->type);
				$dbSourceArray[] = $this->dbo->quote(strip_tags($dataSource->name));
				$dbSourceArray[] = $this->dbo->quote(strip_tags($dataSource->description));
				$dbSourceArray[] = (int)$dataSource->published;
				$dbSourceArray[] = $this->dbo->quote($dataSource->sqlquery);
				$dbSourceArray[] = $this->dbo->quote($dataSource->sqlquery_managed);
				$dbSourceArray[] = $this->dbo->quote($dataSource->params);
				
				// Source imploded assignment
				$dbQueryArray[] = implode(',', $dbSourceArray);
			}
			
			// Check if some valid data to import are available
			if(!count($dbQueryArray)) {
				throw new JMapException(JText::_('COM_JMAP_NO_IMPORT_DATA_FOUND'), 'warning');
			}
			
			// Final sources imploded assignment
			$implodedSources = '(' . implode('),(', $dbQueryArray) . ')';
			
			$queryImport = 	"INSERT INTO" . $this->dbo->quoteName('#__jmap') .
						   	"\n (" .
						   	"\n" . $this->dbo->quoteName('type') . "," .
						   	"\n" . $this->dbo->quoteName('name') . "," .
						   	"\n" . $this->dbo->quoteName('description') . "," .
						   	"\n" . $this->dbo->quoteName('published') . "," .
						   	"\n" . $this->dbo->quoteName('sqlquery') . "," .
							"\n" . $this->dbo->quoteName('sqlquery_managed') . "," .
							"\n" . $this->dbo->quoteName('params') . ")" .
						   	"\n VALUES " . $implodedSources;
			$this->dbo->setQuery($queryImport);
			$this->dbo->execute();
			if($this->dbo->getErrorNum()) {
				throw new JMapException(JText::sprintf('COM_JMAP_DBERROR_IMPORT_DATA', $this->dbo->getErrorMsg()), 'error');
			}
		}
		catch(JMapException $e) {
			$this->setError($e);
			return false;
		} catch (Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->setError($jmapException);
			return false;
		}
		
		return true;
	}

	/**
	 * Download uploaded file message
	 * 
	 * @access public
	 * @return boolean
	 */
	public function export($ids = null) { 
		// Load data from DB 
		try {
			$query = "SELECT * FROM #__jmap" .
					 "\n WHERE id IN ( " . implode(',', $ids) . ")";
			$this->dbo->setQuery($query);
			$resultInfo = $this->dbo->loadObjectList();
			if(!$resultInfo) {
				if(!$resultInfo) {
					throw new JMapException(JText::_('COM_JMAP_ERROR_NODATA_TOEXPORT'), 'error');
				}
			}
			
			// Serialize data to export
			$dataToExport = json_encode($resultInfo);
		} catch(JMapException $e) {
			$this->setError($e);
			return false;
		} catch (Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->setError($jmapException);
			return false;
		}
		
		$fsize = strlen($dataToExport);
		$cont_dis = 'attachment';
		$mimeType = 'application/json';
		
		// required for IE, otherwise Content-disposition is ignored
		if (ini_get ( 'zlib.output_compression' )) {
			ini_set ( 'zlib.output_compression', 'Off' );
		}
		header ( "Pragma: public" );
		header ( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
		header ( "Expires: 0" );
		header ( "Content-Transfer-Encoding: binary" );
		header ( 'Content-Disposition:' . $cont_dis . ';' . ' filename="datasources.json";' . ' size=' . $fsize . ';' ); //RFC2183
		header ( "Content-Type: " . $mimeType ); // MIME type
		header ( "Content-Length: " . $fsize );
		if (! ini_get ( 'safe_mode' )) { // set_time_limit doesn't work in safe mode
			@set_time_limit ( 0 );
		}
		// No encoding - we aren't using compression... (RFC1945)
		//header("Content-Encoding: none");
		//header("Vary: none");
		echo $dataToExport;
		
		exit();
	}

	/**
	 * Install a plugin like data source with source code and manifest zip package
	 *
	 * @access public
	 * @return boolean
	 */
	public function install() {
		// Get file info
		$file = $this->app->input->files->get('datasourceinstallplugin', null, 'raw');
		$tmpFile = $file['tmp_name'];
		$tmpFileName = $file['name'];
		try {
			if(!$tmpFile || !$tmpFileName) {
				throw new JMapException(JText::_('COM_JMAP_NOFILE_SELECTED'), 'error');
			}

			$tmpFileSize = $file['size'];
			$allowedFileSize = 2 * 1024 * 1024; // MB->Bytes
			if($tmpFileSize > $allowedFileSize) {
				throw new JMapException(JText::_('COM_JMAP_SIZE_ERROR') .' Max 2MB.', 'error');
			}

			$tmpFileExtension = @array_pop(explode('.', $tmpFileName));
			if($tmpFileExtension != 'zip') {
				throw new JMapException(JText::_('COM_JMAP_EXT_PLUGIN_ERROR'), 'error');
			}
	
			// Get the folder name to create, if existing delete it
			$folderPluginName = strtolower(@array_shift(explode('.', $tmpFileName)));
			$folderPluginPath = JPATH_COMPONENT_ADMINISTRATOR . '/plugins/' . $folderPluginName;
			if(JFolder::exists($folderPluginPath)) {
				JFolder::delete($folderPluginPath);
			}
			JFolder::create($folderPluginPath);

			// Copy the zip archive to the target directory
			if(!move_uploaded_file($tmpFile, $folderPluginPath . '/' . $tmpFileName)) {
				throw new JMapException(JText::_('COM_JMAP_UPLOAD_ERROR'), 'error');
			}

			// Unpack the zip archive
			JArchive::extract($folderPluginPath . '/' . $tmpFileName, $folderPluginPath);

			// Delete the original zip archive
			JFile::delete($folderPluginPath . '/' . $tmpFileName);

			// Parse the XML manifest file of the plugin
			$manifestFiles = glob( $folderPluginPath . '/*.xml');
			if(!count($manifestFiles)) {
				throw new JMapException(JText::_('COM_JMAP_ERROR_MANIFEST_NOTFOUND'), 'error');
			}
			$pluginManifestFile = $manifestFiles[0];
			$pluginManifest = simplexml_load_file($pluginManifestFile);

			// Get plugin name and descriptions
			$pluginName = $pluginManifest->name;
			$pluginDescription = $pluginManifest->description;

			// Get plugin params
			$pluginParams = $pluginManifest->config->fields->fieldset->field;

			// Inject everything in the POST request and prepare for the save task
			$this->requestArray[$this->requestName]['name'] = ucfirst($pluginName);
			$this->requestArray[$this->requestName]['description'] = (string)$pluginDescription;
			foreach ($pluginParams as $field) {
				// Store in superglobal POST array
				$fieldName = (string)$field->attributes()->name;
				$fieldValue = (string)$field->attributes()->default;
				$this->requestArray[$this->requestName]['params'][$fieldName] = $fieldValue;
			}
			
			// Check if plugin folder name is the same as specified in the manifest
			if($folderPluginName != strtolower($pluginName)) {
				rename($folderPluginPath, JPATH_COMPONENT_ADMINISTRATOR . '/plugins/' . strtolower($pluginName));
			}
		}
		catch(JMapException $e) {
			$this->setError($e);
			return false;
		} catch (Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
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
		
		$this->requestArray = &$GLOBALS;
		$this->requestName = '_' . strtoupper('post');
	}
}