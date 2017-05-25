<?php
// namespace administrator\components\com_jmap\models;
/**
 * @package JMAP::HTACCESS::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
jimport('joomla.filesystem.file');

/**
 * Htaccess model concrete implementation
 *
 * @package JMAP::HTACCESS::administrator::components::com_jmap
 * @subpackage models
 * @since 3.0
 */
class JMapModelHtaccess extends JMapModel {
	/**
	 * Load entity from ORM table
	 *
	 * @access public
	 * @param int $id
	 * @return Object&
	 */
	public function loadEntity($id) {
		try {
			// Load htaccess file, finding it
			$targetHtaccess = null;
			// Try to check for an active htaccess file
			if(JFile::exists(JPATH_ROOT . '/.htaccess')) {
				$targetHtaccess = JPATH_ROOT . '/.htaccess';
			} elseif (JFile::exists(JPATH_ROOT . '/htaccess.txt')) { // Fallback on txt dummy version
				$targetHtaccess = JPATH_ROOT . '/htaccess.txt';
				$this->setState('htaccess_version', 'textual');
			} else {
				throw new JMapException(JText::_('COM_JMAP_HTACCESS_NOTFOUND'), 'error');
			}
				
			// htaccess found!
			if($targetHtaccess !== false) {
				// If file permissions ko
				if(!$htaccessContents = JFile::read($targetHtaccess)) {
					throw new JMapException(JText::_('COM_JMAP_ERROR_READING_HTACCESS'), 'error');
				}
			}
				
		} catch(JMapException $e) {
			$this->setError($e);
			return false;
		}  catch(Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->setError($jmapException);
			return false;
		}
		
		return $htaccessContents;
	}
	
	/**
	 * Storing entity by ORM table
	 *
	 * @access public
	 * @return boolean
	 */
	public function storeEntity($buffer = null) {
		try {
			// Data posted required, otherwise avoid write anything
			if(!$buffer) {
				throw new JMapException(JText::_('COM_JMAP_HTACCESS_NO_DATA'), 'error');
			}
			
			$targetHtaccess = null;
			// Find htaccess file
			if(JFile::exists(JPATH_ROOT . '/.htaccess')) {
				$targetHtaccess = JPATH_ROOT . '/.htaccess';
			} elseif (JFile::exists(JPATH_ROOT . '/htaccess.txt')) { // Fallback on txt dummy version
				$targetHtaccess = JPATH_ROOT . '/htaccess.txt';
				$this->setState('htaccess_version', 'textual');
			} else {
				throw new JMapException(JText::_('COM_JMAP_HTACCESS_NOTFOUND'), 'error');
			}
			
			// If file permissions ko on rewrite updated contents
			$originalPermissions = null;
			if(!is_writable($targetHtaccess)) {
				$originalPermissions = intval(substr(sprintf('%o', fileperms($targetHtaccess)), -4), 8);
				@chmod($targetHtaccess, 0755);
			}
			if(@!JFile::write($targetHtaccess, $buffer)) {
				throw new JMapException(JText::_('COM_JMAP_ERROR_WRITING_HTACCESS'), 'error');
			}
			
			// Check if permissions has been changed and recover the original in that case
			if($originalPermissions) {
				@chmod($targetHtaccess, $originalPermissions);
			}
		} catch(JMapException $e) {
			$this->setError($e);
			return false;
		}  catch(Exception $e) {
			$jmapException = new JMapException($e->getMessage(), 'error');
			$this->setError($jmapException);
			return false;
		}
		return true;
	}
}