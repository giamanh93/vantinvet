<?php
/**
 * product builder component
 * @package productbuilder
 * @version helpers/update.php  2012-3-31 sakisTerz $
 * @author Sakis Terz (sakis@breakDesigns.net)
 * @copyright	Copyright (C) 2010-2012 breakDesigns.net. All rights reserved
 * @license	GNU/GPL v2
 */
defined('_JEXEC') or die();
jimport('joomla.filesystem.file');

class extensionUpdateHelper{
	public static $instance;
	private $updateDataFile;
	private $targetFileExists;
	private $extension;
	/*The update frequency in hours*/
	private $updateFrequency;
	/**
	 * Implements the Singleton pattern for this clas
	 *
	 * @staticvar extensionUpdateHelper $instance The static object instance
	 * @return extensionUpdateHelper
	 */
	public static function getInstance($extension=null,$target=null,$updateFrequency=null){
		if(!isset(self::$instance)){
			self::$instance=new extensionUpdateHelper($extension,$target,$updateFrequency);
		}
		return self::$instance;
	}

	public function __construct($extension=null,$target=null,$updateFrequency=2){
		if(!isset($extension) || !isset($target)) throw new Exception('No extension or target defined');
		$this->extension=$extension;
		$this->updateFrequency=$updateFrequency;

		if(JFile::exists(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.$target)){
			$this->targetFileExists=true;
		}else $this->targetFileExists=false;
		$this->updateDataFile=JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.$target;
	}

	/**
	 * Main function that triggers the processes
	 *
	 */
	public function getData(){
		$file_created=true;
		$data='';
		if($this->existFOPEN()){
			if($this->targetFileExists){
			}else {
				$file_created=$this->createFile();
			}
		}

		//if the file does not exist and cannot be created get the data directly from the url
		if($file_created===false){
			$data=$this->downloadData();
		}else if( @ filesize($this->updateDataFile)>0){
			//decide if should read from contents or a download should be done
			$file_modif_time=filemtime($this->updateDataFile);
			$updateFrequency_in_sec=(int)$this->updateFrequency*60;
			$current_timestamp=time();
			//if the frequency time has passed download
			if($file_modif_time===false || $current_timestamp-$file_modif_time>$updateFrequency_in_sec){
				$data=$this->downloadData();
				JFile::write($this->updateDataFile, $data);
			}else $data=JFile::read($this->updateDataFile);
		}else{
			//file exists but its empty,download the data and write them to the file
			$data=$this->downloadData();
			JFile::write($this->updateDataFile, $data);
		}
		if(!empty($data)){
			jimport('joomla.registry.registry');
			$registry=new JRegistry('update');
			$registry->loadString($data,'INI');
			return $registry->toObject();
		}
		else return false;
	}

	/**
	 * Downloads the data from a specified URL
	 *
	 * @param string $url
	 * @return string $data;
	 */
	private function downloadData(){
		if($this->extension)$url='http://breakdesigns.net/index.php?option=com_extensiondata&format=ini&extension='.$this->extension;
		if(isset($url) && $this->existCURL()){
			$ch = curl_init ($url) ;
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			// Pretend we are IE7, so that webservers play nice with us
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)');
			$result = curl_exec($ch);
			curl_close($ch);
			return $result;
		}
		return false;
	}

	/**
	 * Create the file if it does no exist
	 *
	 * @param string $file path
	 */
	private function createFile(){
		$target=$this->updateDataFile;
		$fp = @fopen($target, 'wb');
		if($fp === false) {
			// The file can not be opened for writing. Let's try a hack.
			if( JFile::write($target, '') ) {
				if( self::chmod($target, 511) ) {
					$fp = @fopen($target, 'wb');
					$hackPermissions = true;
				}
			}
		}
		return $fp;
	}

	/**
	 * Does the server support URL fopen() wrappers?
	 * @return bool
	 */
	private function existFOPEN()
	{
		// If we are not allowed to use ini_get, we assume that URL fopen is  disabled
		if(!function_exists('ini_get')) {
			$result = false;
		} else {
			$result = ini_get('allow_url_fopen');
		}
		return $result;
	}

	/**
	 * Does the server support PHP's cURL extension?
	 * @return bool True if it is supported
	 */
	private function existCURL()
	{
		$result = function_exists('curl_init');
		return $result;
	}

	/**
	 * Change the permissions of a file, optionally using FTP
	 * @param string $file Absolute path to file
	 * @param int $mode Permissions, e.g. 0755
	 * @copyright Copyright (c)2010-2012 Nicholas K. Dionysopoulos / AkeebaBackup.com
	 */
	private static function chmod($path, $mode)
	{
		if(is_string($mode))
		{
			$mode = octdec($mode);
			if( ($mode < 0600) || ($mode > 0777) ) $mode = 0755;
		}

		// Initialize variables
		jimport('joomla.client.helper');
		$ftpOptions = JClientHelper::getCredentials('ftp');

		// Check to make sure the path valid and clean
		$path = JPath::clean($path);

		if ($ftpOptions['enabled'] == 1) {
			// Connect the FTP client
			jimport('joomla.client.ftp');
			$ftp = &JFTP::getInstance(
			$ftpOptions['host'], $ftpOptions['port'], null,
			$ftpOptions['user'], $ftpOptions['pass']
			);
		}

		if(@chmod($path, $mode))
		{
			$ret = true;
		} elseif ($ftpOptions['enabled'] == 1) {
			// Translate path and delete
			jimport('joomla.client.ftp');
			$path = JPath::clean(str_replace(JPATH_ROOT, $ftpOptions['root'], $path), '/');
			// FTP connector throws an error
			$ret = $ftp->chmod($path, $mode);
		} else {
			return false;
		}
	}
	/**
	 *
	 * Convert a plain text to array based on line breaks
	 * @param 	string $text
	 * @return	array
	 */
	public static function convertTextToArray($text){
		$lines = explode("\n", $text);
		foreach($lines as $line) {
			$pos=strpos($line, '=');
			if($pos!==false){
			$line=str_replace('"', '', $line);
			$key=substr($line,0,strlen($line)-$pos);
			$value=substr($line, $pos+1);
			echo $key,'--'.$value;
			}
		}
		return;
	}
}