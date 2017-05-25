<?php
/**
 * @package		mod_form
 * @copyright	Copyright (C) 2013 ql.de All rights reserved.
 * @author 		Ingo Holewcuk ingo.holewczuk@ql.de
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

class modQlFormCaptcha
{
	/**
	* path and filename for the catpcha font
	* @var string 
	* @access public
	*/		
	public $strFont;
	/**
	* path where the rendered captcha image should be safed 
	* @var int 
	* @access public
	*/	
	public $strCaptchaSaveFile;
	/**
	* the width of the captcha image
	* @var int 
	* @access public
	*/		
	public $intIMGWidth=140;
	/**
	* the height of the captcha image
	* @var int 
	* @access public
	*/		
	public $intIMGHeight=80;
	/**
	* an array with rgb colors backgroundcolors of the captcha image 
	* @var array 
	* @access public
	*/		
	public $arrBGColor=array(255,255,255);
	/**
	* an array with rgb colors fontcolors of the captcha letters 
	* @var array 
	* @access public
	*/		
	public $arrTextColor=array(24,24,24);
	/**
	* the number of letters  
	* @var int 
	* @access public
	*/		
	public $intTextLenght=4;
	/**
	* the fontsize of the captcha letters 
	* @var int 
	* @access public
	*/		
	public $intFontSize=26;
	/**
	* the angel of the captcha letters 
	* @var int 
	* @access public
	*/		
	public $intFontAngel=5;
	/**
	* the horizontal start position of the captcha letters 
	* @var int 
	* @access public
	*/		
	public $intFontStartX=20;
	/**
	* the vertical start position of the captcha letters 
	* @var int 
	* @access public
	*/		
	public $intFontStartY=50;

	/**
	* wants to have file path and folder path
	* @param array $arrBG with rgb color information for background
	* @param array $arrTxt with rgb color information for text
	* @param string $strFont path of font-file
	* @return bool ture on success, false on failure
	*/
	function __construct($strFont,$strCaptchaSaveFile) 
	{
		$this->strFont=$strFont;
		$this->strCaptchaSaveFile=$strCaptchaSaveFile;
	}

	/**
	* generates a random text for the captcha image
	* @param int the wanted lenght of the text  
	* @return string
	*/
	function randomText($laenge) 
	{
		$zeichen = 'bcdefghkmnopqrstuvwxyz2345678';
		$kette = '';
		mt_srand ((double) microtime() * 1000000);
		for ($i = 0; $i < $laenge; $i++) $kette .= $zeichen{mt_rand (0,strlen($zeichen)-1)};
    	return $kette;
	}
	/**
	* generates a captacha
	* @return string the rendered text
	*/
	function generateCaptcha() 
	{
		$this->handleImage = ImageCreate ($this->intIMGWidth, $this->intIMGHeight);
		ImageColorAllocate ($this->handleImage, $this->arrBGColor[0],$this->arrBGColor[1], $this->arrBGColor[2]);
		$text_color = ImageColorAllocate ($this->handleImage,$this->arrTextColor[0], $this->arrTextColor[1],$this->arrTextColor[2]);
		$text=$this->randomText($this->intTextLenght);
		ImageTTFText($this->handleImage, $this->intFontSize, $this->intFontAngel, $this->intFontStartX, $this->intFontStartY, $text_color, $this->strFont, ''.$text.'');
        imagepng($this->handleImage,$this->strCaptchaSaveFile);
        return($this->text=$text);
	}
	/**
	* checks captcha
	* @return string the rendered text
	*/
    function checkCaptcha($strCaptcha,$moduleId)
    {
    	if (isset($strCaptcha) AND isset($_SESSION['strCaptchaCode']) AND isset($_SESSION['strCaptchaCode'][$moduleId]) AND $strCaptcha==$_SESSION['strCaptchaCode'][$moduleId]) return(true);
    	return(false);
	}
	/**
	* checks captcha
	* @return string the rendered text
	*/
	function getFilename($moduleId)
	{
		$filename=$this->strCaptchaSaveFile.'/'.session_id().'_'.$moduleId.'_'.date('ymdhis').'.png';
		$this->strCaptchaSaveFile=$filename;
		return $filename.'?'.rand(0,10000);
	}
	/**
	* start session and initiate session variables
	* @return string the rendered text
	*/
	function setSession($moduleId)
	{
		@session_start();
        if (!isset($_SESSION['strCaptchaCode']) OR (!is_array($_SESSION['strCaptchaCode'])))$_SESSION['strCaptchaCode']=array();
        if (!isset($_SESSION['strCaptchaFilename']) OR (!is_array($_SESSION['strCaptchaFilename'])))$_SESSION['strCaptchaFilename']=array();
        $_SESSION['strCaptchaCode'][$moduleId]=$this->text;
        //if (isset($_SESSION['strCaptchaFilename']) AND isset($_SESSION['strCaptchaFilename'][$moduleId]) AND is_file($_SESSION['strCaptchaFilename'][$moduleId])) unlink($_SESSION['strCaptchaFilename'][$moduleId]);/*wtf*/
		$_SESSION['strCaptchaFilename'][$moduleId]=$this->strCaptchaSaveFile;
    }
}
