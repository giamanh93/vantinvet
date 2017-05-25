<?php
// namespace administrator\components\com_jmap\models;
/**
 * @package JMAP::WIZARD::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');

/**
 * Wizard model responsibilities
 *
 * @package JMAP::WIZARD::administrator::components::com_jmap
 * @subpackage models
 * @since 2.0
 */
interface IJMapModelWizard {
	/**
	 * Main get data method
	 *
	 * @access public
	 * @return Object[]
	 */
	public function getData($path = null);

	/**
	 * Creational method for extensions data source
	 *
	 * @access public
	 * @return Void It doesn't require a return value because called by ajax and manage exceptions app queue
	 */
	public function createEntityProcess();
	
	/**
	 * Try to evaluate if any substitutions to perform is available for extension and data source
	 * chosen, if any perform it calling doSubstitutions, otherwise original string untouched is returned
	 *
	 * @access public
	 * @param string $sqlString
	 * @return array
	 */
	public function getSubstitutionsOnDemand($sqlString);
}

/**
 * Wizard business logic to auto-create extensions data source <<testable_behavior>>
 *
 * @package JMAP::WIZARD::administrator::components::com_jmap
 * @subpackage models
 * @since 2.0
 */
class JMapModelWizard extends JMapModel implements IJMapModelWizard {
	/**
	 * JSON manifest file path for target extension data source to create
	 *
	 * @access private
	 * @var string
	 */
	private $manifestFilePath;
	
	/**
	 * Language tag code used for replacement of placeholders
	 *
	 * @access private
	 * @var string
	 */
	private $language;
	
	/**
	 * Langtag code used for replacement of placeholders
	 *
	 * @access private
	 * @var string
	 */
	private $langtag;
	
	/**
	 * Deserialized manifest object for specific extension wizard informations
	 *
	 * @access protected
	 * @var Object
	 */
	protected $manifestObject;
	
	/**
	 * Name of detected extension to create data source for
	 *
	 * @access protected
	 * @var string
	 */
	protected $extension;
	
	/**
	 * User array sorting service function
	 *
	 * @access private
	 * @param array $a
	 * @param array $b
	 * @return boolean
	 */
	private function cmp($a, $b) {
		if ($a['dataSourceName'] == $b['dataSourceName']) {
			return 0;
		}
		return ($a['dataSourceName'] < $b['dataSourceName']) ? -1 : 1;
	}

	/**
	 * Do replacement for placeholders on JSON manifest chunks for fields to inject into REQUEST
	 * 
	 * @access protected
	 * @param Object $manifest
	 * @return void
	 */
	protected function doReplacement($manifest) {
		// Check if substitutions are set on manifest object
		if(property_exists($manifest, 'placeholders')) {
			// Get manifest object source and detect if any placeholders to replace exists
			if(!empty($manifest->placeholders)) {
				// Iterate on object properties
				foreach ($manifest->placeholders as $postfield=>$toReplace) {
					// Do replacement in $manifest referenced object
					if(!property_exists($manifest->postfields, $postfield)) {
						throw new JMapException(JText::_('COM_JMAP_ERROR_NOPROPERTY_EXISTS_POSTFIELDS'), 'error');
					}
					// Do evaluate
					if(!property_exists($this, $toReplace[1])) {
						throw new JMapException(JText::_('COM_JMAP_ERROR_NOPROPERTY_EXISTS_MODELOBJECT'), 'error');
					}
					$evaluatedValue = $this->{$toReplace[1]};
					// Do replace
					$manifest->postfields->{$postfield} = str_ireplace($toReplace[0], $evaluatedValue, $manifest->postfields->{$postfield});
				}
			}
		}
	}

	/**
	 * Do substitution for auto generated raw SQL query string placed in DB table field after creation process
	 * After substitute chunks in SQL query string for the table field perform an ORM table update
	 * 
	 * @access protected
	 * @param string $sqlString
	 * @param Object $manifest
	 * @return string
	 */
	protected function doSubstitution($sqlString, $manifest) {
		// Check if substitutions are set on manifest object
		if(property_exists($manifest, 'substitutions')) {
			// Check if is array and not empty
			if(!empty($manifest->substitutions)) {
				foreach ($manifest->substitutions as $substitution) {
					$sqlString = str_ireplace($substitution[0], $substitution[1], $sqlString);
				}
			}
		}
		
		return $sqlString;
	}

	/**
	 * Load the JSON format manifest file from file system based on extension wizard configuration folder
	 * Once deserialized contents of manifest file it assigns object to $manifestObject local member property
	 * 
	 * @access protected
	 * @param string $fileName
	 * @return Object
	 */
	protected function loadManifestFile($fileName) {
		// Check if file exists and is valid manifest
		if(!$fileName || !file_exists($fileName)) {
			throw new JMapException(JText::_('COM_JMAP_ERROR_NOMANIFEST_FOUND'), 'error');
		}
		
		// Load the manifest serialized file and assign to local variable
		$manifestContents = file_get_contents($fileName);
		
		// Unserialize data and assign object data to local manifestObject property
		$this->manifestObject = json_decode($manifestContents);
		if(!$this->manifestObject) {
			throw new JMapException(JText::_('COM_JMAP_ERROR_MANIFEST_FORMAT'), 'error');
		}
		
		// Return unserialized manifest object
		return $this->manifestObject;
	}

	/**
	 * Inject from manifest file object array containing sqlquery_managed/postfields and params/querystringlinkparams properties
	 * into the global REQUEST array to imitate a standard POST to sources model from a user compiled form
	 * 
	 * @access protected
	 * @param Object $manifest
	 * @return void
	 */
	protected function injectRequestField($manifest) {
		// Inject fields into POST HTTP request
		
		/**
		 * Mapping is:
		 * postfields = $this->requestArray[$this->requestName]['sqlquery_managed'][]
		 * querystringlinkparams = $this->requestArray[$this->requestName]['params'][]
		 */
		//Inject data source name first
		$dataSourceUserfriendlyName = ucfirst(str_replace('_', ' ', $this->extension));
		$this->requestArray[$this->requestName]['name'] = $dataSourceUserfriendlyName;
		$this->requestArray[$this->requestName]['params']['datasource_extension'] = $this->extension;
		
		// Missing required data to construct data source creation
		if(!property_exists($manifest, 'postfields')) {
			throw new JMapException(JText::_('COM_JMAP_ERROR_INVALID_MANIFEST_MISSING_POSTFIELDS'), 'error');
		}
		
		// Error in object syntax or not valid object syntax
		if(!is_object($manifest->postfields)) {
			throw new JMapException(JText::_('COM_JMAP_ERROR_INVALID_MANIFEST_OBJECT_POSTFIELDS'), 'error');
		}
		
		//Cycle and store postfields REQUIRED
		foreach ($manifest->postfields as $fieldName=>$fieldValue) {
			// Store in superglobal POST array
			$this->requestArray[$this->requestName]['sqlquery_managed'][$fieldName] = $fieldValue;
		}
		
		//Cycle and store querystringlinkparams OPTIONAL
		if(property_exists($manifest, 'querystringlinkparams')) {
			// Error in object syntax or not valid object syntax
			if(!is_object($manifest->querystringlinkparams)) {
				throw new JMapException(JText::_('COM_JMAP_ERROR_INVALID_MANIFEST_OBJECT_QSLINKPARAMS'), 'error');
			}
			foreach ($manifest->querystringlinkparams as $fieldName=>$fieldValue) {
				// Store in superglobal POST array
				$this->requestArray[$this->requestName]['params'][$fieldName] = $fieldValue;
			}
		}
		
	}

	/**
	 * Try to evaluate if any substitutions to perform is available for extension and data source
	 * chosen, if any perform it calling doSubstitutions, otherwise original string untouched is returned
	 *
	 * @access public
	 * @param string $sqlString
	 * @return array
	 */
	public function getSubstitutionsOnDemand($sqlString) {
		// Load manifest file
		$this->manifestFilePath = JPATH_COMPONENT . '/images/wizard/' . $this->extension . '/manifest.json';
		
		// Manifest object could exists or not for specified data source extension
		if(file_exists($this->manifestFilePath)) {
			// Load the manifest serialized file and assign to local variable
			$manifestContents = file_get_contents($this->manifestFilePath);
			// Unserialize data and assign object data to local manifestObject property
			$this->manifestObject = json_decode($manifestContents);
			$sqlString = $this->doSubstitution($sqlString, $this->manifestObject);
		}
		
		return $sqlString;
	}
	
	/**
	 * Main get data method that retrieve and array containing informations to build up a graphic interface
	 * for supported extensions data source creational types both with extension name and icon
	 * It makes a directory listings for supported extensions returning array for data source entity name
	 * and data source entity icon found
	 *
	 * @access public
	 * @param string $path Inject the path where are placed subfolders for supported data source entities
	 * @return array
	 */
	public function getData($path = null) {
		// Init extensions discovery array
		$discoveredExtensions = array();
		
		try {
			if(!$path || !is_dir($path)) {
				throw new JMapException(JText::_('COM_JMAP_EMPTY_ERROR_PATH'), 'error');
			}
			if(!class_exists('DirectoryIterator')) {
				throw new JMapException(JText::_('COM_JMAP_SPL_LIBRARY_NOTFOUND_OLDPHPVERSION'), 'error');
			}
			// Discover supported extensions data sources, get available subfolders and assigned names primary key
			$iterator = new DirectoryIterator($path);
			foreach ($iterator as $subfolder) {
				// get only directory and not dotted
				if($subfolder->isDir() && !$subfolder->isDot()) {
					$folderName = $subfolder->getFilename();
					$innerPath = $path . '/' . $folderName . '/';
					// Check if valid folder name, not to contains spaces or extra characters
					if(!preg_match('/[^0-9A-Za-z_]/', $folderName)) {
						$innerPath = $path . '/' . $folderName . '/';
						
						// Try first method that relies on glob if not disabled by safe mode
						if(function_exists('glob')) {
							$extensionFileName = @array_pop(glob($innerPath . "*.txt"));
							$extensionEffectiveName = str_replace($innerPath, '', $extensionFileName);
							$extensionEffectiveName = str_replace('.txt', '', $extensionEffectiveName);
							$discoveredExtensions[] = array('dataSourceName'=>$folderName, 'extensionName'=>$extensionEffectiveName);
						} else {
							// Do all scan with SPL libraries
							$subIterator = new DirectoryIterator($innerPath);
							foreach ($subIterator as $file) {
								$subFileName = $file->getFilename();
								if(preg_match('/.*\.txt/i', $subFileName)) {
									$extensionFileName = $subFileName;
									$extensionEffectiveName = str_replace($innerPath, '', $extensionFileName);
									$extensionEffectiveName = str_replace('.txt', '', $extensionEffectiveName);
									$discoveredExtensions[] = array('dataSourceName'=>$folderName, 'extensionName'=>$extensionEffectiveName);
								}
							}
						}
					}
				}
			}
			// Natural sorting array
			if(!empty($discoveredExtensions)) {
				uasort($discoveredExtensions, array($this, 'cmp'));	
			}
		} catch (JMapException $e) {
			$this->app->enqueueMessage($e->getMessage(), $e->getErrorLevel());
		}
		
		// Return discovered extensions array
		return $discoveredExtensions;
	}

	/**
	 * Main get data method that retrieve and array containing informations to build up a graphic interface
	 * for supported extensions data source creational types both with extension name and icon
	 *
	 * @access public
	 * @return boolean
	 */
	public function createEntityProcess() {
		// Start creating process for data source
		try {
			// Load manifest file
			$this->manifestFilePath = JPATH_COMPONENT . '/images/wizard/' . $this->extension . '/manifest.json';
			$this->loadManifestFile($this->manifestFilePath);
			
			// Do replacement if any
			$this->doReplacement($this->manifestObject);
			
			// Inject request field into REQUEST
			$this->injectRequestField($this->manifestObject);
			
			// Call data source creation model
			$createdDataSourceTable = $this->sourcesModel->storeEntity(true, true);
			
			// Do substitutions on generated raw query if any....
			$sqlString = $createdDataSourceTable->sqlquery;
			$createdDataSourceTable->sqlquery = $this->doSubstitution($sqlString, $this->manifestObject);
			// ....finally update $createdDataSourceTable with new sqlquery if changed by substitutions
			if($sqlString != $createdDataSourceTable->sqlquery) {
				if (! $createdDataSourceTable->store (false)) {
					throw new JMapException($createdDataSourceTable->getError (), 'error');
				}
			}
		} catch (JMapException $e) {
			$this->setError($e);
			return false;
		}  catch(Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->setError($jmapException);
			return false;
		}
		
		return true;
	}
	
	/**
	/* Class constructor
	 * 
	 * @access public
	 * @param $config array
	 * @return Object&
	 */
	public function __construct(array $config = array()) {
		parent::__construct($config);
		
		$langParams = JComponentHelper::getParams('com_languages');
		$this->langtag = $langParams->get('site');
		// Setup predefined site language for placeholders replacement according to RFC 3066 and Virtuemart tables
		$this->language = str_replace('-', '_', strtolower($this->langtag));
		if(!empty($config)) {
			$this->extension = $config['extension'];
			$this->sourcesModel = $config['sourcesModel'];
		}
	}

}