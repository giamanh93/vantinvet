<?php
/**
 * Installation file for CustomFilters
 *
 * @package 	customfilters.install
 * @author 		Sakis Terzis
 * @link 		http://www.epahali.com
 * @copyright 	Copyright (c) 2012 breakdesigns. All rights reserved.
 * @license		GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @version 	$Id: script.php 2012-10-29 $
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die;

/**
 * Load the ePahali installer
 *
 * @copyright
 * @author 		Sakis Terz
 * @see 		http://docs.joomla.org/Developing_a_Model-View-Controller_%28MVC%29_Component_for_Joomla!1.6_-_Part_15
 * @access 		public
 * @param
 * @return
 * @since 		1.5.3
 */
class com_customfiltersInstallerScript {

	/**
	 * Installation routine
	 *
	 * @copyright
	 * @author 		Sakis Terz
	 * @access 		public
	 * @param
	 * @return
	 * @since 		2.0
	 */
	public function install($parent) {

	}

	/**
	 * Update routine
	 *
	 * @copyright
	 * @author 		Sakis Terzis
	 * @todo
	 * @see
	 * @access 		public
	 * @param
	 * @return
	 * @since 		2.0
	 */
	public function update($parent) {
		// $parent is the class calling this method
		echo JText::_('COM_CUSTOMFILTERS_UPDATED');
		//$parent->getParent()->setRedirectURL('index.php?option=com_customfilters');
	}

	/**
	 * Uninstallation routine
	 *
	 * @copyright
	 * @author 		Sakis Terzis
	 * @todo
	 * @see
	 * @access 		public
	 * @param
	 * @return
	 * @since 		2.0
	 */
	public function uninstall($parent) {

	}

	/**
	 * Preflight routine executed before install and update
	 *
	 * @copyright
	 * @author 		Sakis Terzis
	 * @todo
	 * @see
	 * @access 		public
	 * @param 		$type	string	type of change (install, update or discover_install)
	 * @return
	 * @since 		2.0
	 */
	public function preflight($type, $parent) {

		jimport('joomla.filesystem.file');

		if($type=='update'){

			$messages=array();			
            $milestone_versions=array_keys($messages);


			$this->printed_messages=array();

			$oldRelease=$this->getParam('version');
			$new_release=$parent->get( "manifest" )->version;
		   	foreach ($milestone_versions as $m_v){
				if(version_compare($oldRelease, $m_v)==-1){
					$this->printed_messages[]=$messages[$m_v];
				}
			}
		}

		//delete the latest version ini , to create a new one for the updated version
		$version_ini_path=JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_customfilters'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'lastversion.ini';
		if(JFile::exists($version_ini_path))JFile::delete($version_ini_path);
	}

	/**
	 * Postflight routine executed after install and update
	 *
	 * @copyright
	 * @author 		Sakis Terzis
	 * @todo
	 * @see
	 * @access 		public
	 * @param 		$type	string	type of change (install, update or discover_install)
	 * @return
	 * @since 		2.0
	 */
	public function postflight($type, $parent) {
		$db = JFactory::getDBO();
		$status = new stdClass;
		$status->modules = array();
		$status->plugins = array();
		$src = $parent->getParent()->getPath('source');
		$manifest = $parent->getParent()->manifest;
		$plugins = $manifest->xpath('plugins/plugin');

		foreach ($plugins as $plugin)
		{
			$name = (string)$plugin->attributes()->plugin;
			$group = (string)$plugin->attributes()->group;
			$path = $src.'/plugins/'.$group;
			if (JFolder::exists($src.'/plugins/'.$group.'/'.$name))
			{
				$path = $src.'/plugins/'.$group.'/'.$name;
			}
			$installer = new JInstaller;
			$result = $installer->install($path);

			$query = "UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND element=".$db->Quote($name)." AND folder=".$db->Quote($group);
			$db->setQuery($query);
			$db->query();
			$status->plugins[] = array('name' => $name, 'group' => $group, 'result' => $result);
		}
		$modules = $manifest->xpath('modules/module');
		foreach ($modules as $module)
		{
			$name = (string)$module->attributes()->module;
			$client = (string)$module->attributes()->client;
			if (is_null($client))
			{
				$client = 'site';
			}
			($client == 'administrator') ? $path = $src.'/administrator/modules/'.$name : $path = $src.'/modules/'.$name;

			if($client == 'administrator')
			{
				$db->setQuery("SELECT id FROM #__modules WHERE `module` = ".$db->quote($name));
				$isUpdate = (int)$db->loadResult();
			}

			$installer = new JInstaller;
			$result = $installer->install($path);

			$status->modules[] = array('name' => $name, 'client' => $client, 'result' => $result);
			if($client == 'administrator' && !$isUpdate)
			{
				$position ='cpanel';
				$db->setQuery("UPDATE #__modules SET `position`=".$db->quote($position).",`published`='1' WHERE `module`=".$db->quote($name));
				$db->query();

				$db->setQuery("SELECT id FROM #__modules WHERE `module` = ".$db->quote($name));
				$id = (int)$db->loadResult();

				$db->setQuery("INSERT IGNORE INTO #__modules_menu (`moduleid`,`menuid`) VALUES (".$id.", 0)");
				$db->query();
			}
		}

		if($type=='update'){
			$db=JFactory::getDbo();
			$query="SHOW COLUMNS FROM `#__cf_customfields`";
			$db->setQuery($query);
			$columns=$db->loadColumn();

			if(!in_array('params',$columns)){
				$query="ALTER IGNORE TABLE `#__cf_customfields` ADD `params` TEXT NOT NULL";
				$db->setQuery($query);
				$db->query();
			}
		}
		//copy the files to the joomla images folder
		$src_dir=JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_customfilters'.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'category_tree';
		$dst_dir=JPATH_SITE.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'stories'.DIRECTORY_SEPARATOR.'customfilters';
		$this->recurse_copy($src_dir, $dst_dir);

		$this->installationResults($status,$type);
	}


	/**
	 * copy all $src to $dst folder and remove it
	 *
	 * @author Max Milbers-Sakis Terz
	 * @param String $src path
	 * @param String $dst path
	 * @param String $type modules, plugins, languageBE, languageFE
	 */
	private function recurse_copy($src,$dst ) {
		$dst_exist=JFolder::exists($dst);
		jimport( 'joomla.filesystem.folder' );
		if(!$dst_exist)$dst_exist=JFolder::create($dst);
		$dir = opendir($src);

		if(is_resource($dir) && $dst_exist){
			jimport( 'joomla.filesystem.file' );
			while(false !== ( $file = readdir($dir)) ) {
				if (( $file != '.' ) && ( $file != '..' )) {
					if ( is_dir($src .DIRECTORY_SEPARATOR. $file) ) {
						$this->recurse_copy($src .DIRECTORY_SEPARATOR. $file,$dst .DIRECTORY_SEPARATOR. $file);
					}
					else {
						if(JFile::exists($dst .DIRECTORY_SEPARATOR. $file)){

						}
						if(!JFile::move($src .DIRECTORY_SEPARATOR. $file,$dst .DIRECTORY_SEPARATOR. $file)){
							//$app = JFactory::getApplication();
							//$app -> enqueueMessage('Couldnt move '.$src .DIRECTORY_SEPARATOR. $file.' to '.$dst .DIRECTORY_SEPARATOR. $file);
						}
					}
				}
			}
		}
		if(is_resource($dir))closedir($dir);
		if (is_dir($src)) JFolder::delete($src);
	}

    /**
	 * get a variable from the manifest file (actually, from the manifest cache).
	 */
	function getParam( $name ) {
		$db = JFactory::getDbo();
		$db->setQuery('SELECT manifest_cache FROM #__extensions WHERE element = "com_customfilters"');
		$manifest = json_decode( $db->loadResult(), true );
		return $manifest[ $name ];
	}

	private function installationResults($status,$type)
	{
		$language = JFactory::getLanguage();
		$language->load('com_customfilters');
		$rows = 0;
		if($type=='update'){
			$status_type=JText::_('COM_CUSTOMFILTERS_UPDATE_STATUS');
			$success_msg=JText::_('COM_CUSTOMFILTERS_UPDATED_SUCEESS');
			$fail_msg=JText::_('COM_CUSTOMFILTERS_NOT_UPDATED');
		} else{
			$status_type=JText::_('COM_CUSTOMFILTERS_INSTALLATION_STATUS');
			$success_msg=JText::_('COM_CUSTOMFILTERS_INSTALLED');
			$fail_msg=JText::_('COM_CUSTOMFILTERS_NOT_INSTALLED');
		}
		?>
<img src="<?php echo JURI::root(true); ?>/administrator/components/com_customfilters/assets/images/cf_logo_48.png"
	alt="CustomFilters" align="left" />

<?php
//if update messages
if(!empty($this->printed_messages)){?>
<div class="clr"></div>
<h3><?php echo JText::_('COM_CUSTOMFILTERS_UPDATE_MESSAGES');?></h3>
<div id="system-message-container">
<dl id="system-message">
<dt class="message">Message</dt>
<dd class="message message">
<ul>
<?php
foreach ($this->printed_messages as $message){?>
<li><?php echo $message?></li>
<?php }?>
</ul>
</dd>
</dl>
</div>
<?php }?>
<div class="clr clearfix"></div>
<span style="display: inline-block; margin-top:10px; border-radius:0.25em; color: #ffffff; background-color: #5bc0de; padding:8px; font-weight: 700;" class="label label-info">Please read the <a href="http://breakdesigns.net/custom-filters-manual/18-basic-steps-after-the-installation" target="_blank">Bascic Steps After the Installation</a>, before proceeding</span>
<table  class="adminlist table table-striped" width="100%">
	<thead>
		<tr>
			<th class="title" colspan="2"><?php echo JText::_('COM_CUSTOMFILTERS_EXTENSION'); ?></th>
			<th width="30%"><?php echo JText::_('COM_CUSTOMFILTERS_STATUS'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="3"></td>
		</tr>
	</tfoot>
	<tbody>
		<tr class="row0">
			<td class="key" colspan="2"><?php echo 'CustomFilters '.JText::_('COM_CUSTOMFILTERS_COMPONENT'); ?>
			</td>
			<td><strong><?php echo $success_msg; ?> </strong></td>
		</tr>
		<?php if (count($status->modules)): ?>
		<tr>
			<th><?php echo JText::_('COM_CUSTOMFILTERS_MODULE'); ?></th>
			<th><?php echo JText::_('COM_CUSTOMFILTERS_CLIENT'); ?></th>
			<th></th>
		</tr>
		<?php foreach ($status->modules as $module): ?>
		<tr class="row<?php echo(++$rows % 2); ?>">
			<td class="key"><?php echo $module['name']; ?></td>
			<td class="key"><?php echo ucfirst($module['client']); ?></td>
			<td><strong><?php echo ($module['result'])?$success_msg:$fail_msg; ?>
			</strong></td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
		<?php if (count($status->plugins)): ?>
		<tr>
			<th><?php echo JText::_('COM_CUSTOMFILTERS_PLUGIN'); ?></th>
			<th><?php echo JText::_('COM_CUSTOMFILTERS_GROUP'); ?></th>
			<th></th>
		</tr>
		<?php foreach ($status->plugins as $plugin): ?>
		<tr class="row<?php echo(++$rows % 2); ?>">
			<td class="key"><?php echo ucfirst($plugin['name']); ?></td>
			<td class="key"><?php echo ucfirst($plugin['group']); ?></td>
			<td><strong><?php echo ($plugin['result'])?$success_msg:$fail_msg; ?>
			</strong></td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
		<?php
	}
}
?>