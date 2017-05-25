<?php
// namespace components\com_jmap\libraries\xml;
/**
 *
 * @package JMAP::FRAMEWORK::components::com_jmap
 * @subpackage framework
 * @subpackage xml
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Sitemaps XML precacher public responsibilities
 *
 * @package JMAP::FRAMEWORK::components::com_jmap
 * @subpackage framework
 * @subpackage xml
 * @since 2.3
 */
interface IJMapXmlPrecacher {
	/**
	 * Merge the sitemap generated in the current iteration in the
	 * precaching file, temp file during iterations process, renamed at the end
	 *
	 * @access public
	 * @param string $sitemapIterationData
	 *        	The formatted XML string for the current sitemap iteration generation
	 * @return Object
	 */
	public function mergeSitemap($sitemapIterationData = null);
}

/**
 * Precacher for XML sitemaps
 * This class is responsible to manage correct merge for every generation iteration,
 * it has to strip out start, both, end tags urlset based on process status and
 * finally write precaching file to disk renaming it only if process is finished
 * and last ajax request is detected
 *
 * <<testable_behavior>>
 *
 * @package JMAP::FRAMEWORK::components::com_jmap
 * @subpackage libraries
 * @subpackage xml
 * @since 2.3
 */
class JMapXmlPrecacher implements IJMapXmlPrecacher {
	/**
	 * Path to store precaching files
	 *
	 * @access private
	 * @var string
	 */
	private $preCachingPath;
	
	/**
	 * File name based on the hash of posted params and sietmap type/format
	 *
	 * @access private
	 * @var string
	 */
	private $fileName;
	
	/**
	 * Once finished processing and after renaming and finalizing file try
	 * to get and send to JS client the filemtime to show in the green label
	 *
	 * @access private
	 * @var string
	 */
	private $finalFileMTime;
	
	/**
	 * Object to perform file writing tasks in write append mode
	 * Support stream context
	 *
	 * @access protected
	 * @var string
	 */
	protected $fileStreamWriter;
	
	/**
	 * True if the ajax request is the first in the whole process
	 * If = 'start' the start tag is not stripped out and the close tag is stripped out
	 * If = 'run' both tags are stripped out we are in the middle of the processing
	 * If = 'end' the close tag is not stripped out and the start tag is stripped out
	 *
	 * @access protected
	 * @var string
	 */
	protected $processStatus;
	
	/**
	 * Application reference
	 *
	 * @access protected
	 * @var Object
	 */
	protected $app;
	
	/**
	 * Strip the start/end tags based on process status
	 *
	 * @access protected
	 * @param string $xmlData        	
	 * @return string
	 */
	protected function stripSitemapTags($xmlData) {
		// Data are missing
		if (! $xmlData && $this->processStatus != 'end') {
			return null;
		}
		
		// Evaluate process status and strip iteration tags accordingly
		switch ($this->processStatus) {
			case 'start' :
				$xmlData = preg_replace ( "/<\/urlset(.|\s)*?>/i", '', $xmlData );
				$xmlData = rtrim ( $xmlData, PHP_EOL );
				break;
			
			case 'end' :
				$xmlData = '</urlset>';
				break;
			
			case 'run' :
			default :
				$xmlData = preg_replace ( "/<\/?\?xml(.|\s)*?>/i", null, $xmlData );
				$xmlData = preg_replace ( "/<urlset(.|\s)*?>/i", null, $xmlData );
				$xmlData = preg_replace ( "/<\/urlset(.|\s)*?>/i", null, $xmlData );
				$xmlData = trim ( $xmlData, PHP_EOL );
				break;
		}
		
		return $xmlData;
	}
	
	/**
	 * Write precaching file on disk accordingly to request params hash
	 * If processStatus is detected as end, the temp named file is renamed to
	 * final name that will be used by main display controller as precached sitemap
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function writeFile($data) {
		// Never write if no data
		if(!$data) {
			return false;
		}
		
		// Manage file name as temp, to avoid that not complete operations have resulting broken sitemap files
		$tempFileName = $this->preCachingPath . 'temp_' . $this->fileName;
		
		// Delete any pre-existant incomplete temp processing files - otherwise the result would be append again
		if($this->processStatus == 'start' && file_exists($tempFileName)) {
			if(!JFile::delete($tempFileName)) {
				throw new JMapExceptionPrecaching ( JText::_ ( 'COM_JMAP_PRECACHING_ERROR_DELETE_TEMPFILE'), 'error', 'delete_temp_file' );
			}
		}
		
		// Open file in write append mode
		if (! $this->fileStreamWriter->open ( $tempFileName, 'a' )) {
			throw new JMapExceptionPrecaching ( JText::sprintf ( 'COM_JMAP_PRECACHING_ERROR_OPENING_FILE', $this->fileStreamWriter->getError () ), 'error', 'open_file' );
		}
		
		// Write append to file
		if ($data) {
			// Add always new line if process status is not start
			if ($this->processStatus != 'start') {
				$data = PHP_EOL . $data;
			}
			
			// Try to append data to precaching file
			$result = $this->fileStreamWriter->write ( $data );
			
			// Something went wrong
			if (! $result) {
				throw new JMapExceptionPrecaching ( JText::sprintf ( 'COM_JMAP_PRECACHING_ERROR_WRITING_FILE', $this->fileStreamWriter->getError () ), 'error', 'write_file' );
			}
		}
		
		// Finished operation, close file handle
		if (! $this->fileStreamWriter->close ()) {
			throw new JMapExceptionPrecaching ( JText::sprintf ( 'COM_JMAP_PRECACHING_ERROR_CLOSING_FILE', $this->fileStreamWriter->getError () ), 'error', 'close_file' );
		}
		
		// Check if process status has ended successfully, and if so try to rename temp file to be ready to use
		if ($this->processStatus == 'end') {
			if (! rename ( $tempFileName, $this->preCachingPath . $this->fileName )) {
				throw new JMapExceptionPrecaching ( JText::_ ( 'COM_JMAP_PRECACHING_ERROR_RENAMING_FILE' ), 'error', 'rename_file' );
			}
			// Set filemtime
			$joomlaConfig = JFactory::getConfig();
			$localTimeZone = new DateTimeZone($joomlaConfig->get('offset'));
			$lastGenerationTimestamp = filemtime ( $this->preCachingPath . $this->fileName );
			$dateObject = new JDate($lastGenerationTimestamp);
			$dateObject->setTimezone($localTimeZone);
			$this->finalFileMTime = $dateObject->format('Y-m-d', true);
		}
		
		return true;
	}
	
	/**
	 * Merge the sitemap generated in the current iteration in the
	 * precaching file, temp file during iterations process, renamed at the end
	 *
	 * @access public
	 * @param string $sitemapIterationData
	 *        	The formatted XML string for the current sitemap iteration generation, if the injected data is null it means
	 *        	that we are on the last end iteration so no need to grab data from model but only append the </urlset>
	 * @return Object
	 */
	public function mergeSitemap($sitemapIterationData = null) {
		// Response JSON object
		$response = new stdClass ();
		
		try {
			// Strip tags according to process status
			$strippedData = $this->stripSitemapTags ( $sitemapIterationData );
			
			// Write sitemap iteration data to file
			$this->writeFile ( $strippedData, $this->fileName );
			
			// If process ended and filemtime is set, return to js app to show inside label
			if($this->finalFileMTime) {
				$response->lastgeneration = $this->finalFileMTime;
			}
		} catch ( JMapExceptionPrecaching $e ) {
			$response->result = false;
			$response->exception_message = $e->getMessage ();
			$response->context = $e->getContext ();
			return $response;
		} catch ( Exception $e ) {
			$jmapException = new JMapExceptionPrecaching ( $e->getMessage (), 'error', 'joomla_framework' );
			$response->result = false;
			$response->exception_message = $jmapException->getMessage ();
			$response->context = $jmapException->getContext ();
			return $response;
		}
		
		// Manage exceptions from DB Model and return to JS domain
		$response->result = true;
		
		return $response;
	}
	
	/**
	 * Class constructor
	 *
	 * @access public
	 * @param string $fileName        	
	 * @param Object $fileWriter        	
	 * @return Object&
	 */
	public function __construct($fileName, JStream $fileWriter) {
		$this->app = JFactory::getApplication ();
		
		// Set precaching path
		$this->preCachingPath = JPATH_COMPONENT_SITE . '/precache/';
		
		// Set precaching filename based on sitemap params
		$this->fileName = $fileName;
		
		// Object to perform file storing tasks
		$this->fileStreamWriter = $fileWriter;
		
		// Set process status based on JS App Client
		$this->processStatus = $this->app->input->get ( 'process_status' );
		
		// Set always to null, value is available only after end of process
		$this->finalFileMTime = null;
	}
}
