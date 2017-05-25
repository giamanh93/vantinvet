<?php
// namespace components\com_jmap\libraries\framework\exception;
/**
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage exception
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');

/**
 * JMap Exception object
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage exception
 * @since 1.0.5
 */
class JMapException extends Exception {
	/**
	 * Error level
	 * @access private
	 * @var string
	 */
	private $errorLevel;
	
	/**
	 * Error level accessor method
	 * @access public
	 * @return string
	 */
	public function getErrorLevel() {
		return $this->errorLevel;
	}
	
	/**
	 * Class constructor
	 * @access public
	 * @return Object&
	 */
	public function __construct($message, $level, $code = null) {
		parent::__construct($message, $code);
	
		// Set error level
		$this->errorLevel = $level;
	}
}