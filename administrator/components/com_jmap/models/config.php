<?php
// namespace administrator\components\com_jmap\models;
/**
 *
 * @package JMAP::CONFIG::administrator::components::com_jmap
 * @subpackage models
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
jimport ( 'joomla.application.component.modelform' );

/**
 * Config model responsibilities
 *
 * @package JMAP::CONFIG::administrator::components::com_jmap
 * @subpackage models
 * @since 1.0
 */
interface IJMapModelConfig {
	
	/**
	 * Ottiene i dati di configurazione da db params field record component
	 *
	 * @access public
	 * @return Object
	 */
	public function &getData();
	
	/**
	 * Effettua lo store dell'entity config
	 *
	 * @access public
	 * @return boolean
	 */
	public function storeEntity();
}

/**
 * Config model concrete implementation
 *
 * @package JMAP::CONFIG::administrator::components::com_jmap
 * @subpackage models
 * @since 1.0
 */
class JMapModelConfig extends JModelForm implements IJMapModelConfig {
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
	 * Clean the cache
	 * 
	 * @param string $group
	 *        	The cache group
	 * @param integer $client_id
	 *        	The ID of the client
	 * @return void
	 * @since 11.1
	 */
	private function cleanComponentCache($group = null, $client_id = 0) {
		// Initialise variables;
		$conf = JFactory::getConfig ();
		$dispatcher = JDispatcher::getInstance ();
		
		$options = array (
				'defaultgroup' => ($group) ? $group : $this->app->input->get('option'),
				'cachebase' => ($client_id) ? JPATH_ADMINISTRATOR . '/cache' : $conf->get ( 'cache_path', JPATH_SITE . '/cache' ) 
		);
		
		$cache = JCache::getInstance ( 'callback', $options );
		$cache->clean ();
		
		// Trigger the onContentCleanCache event.
		$dispatcher->trigger ( 'onContentCleanCache', $options );
	}
	
	/**
	 * Ottiene i dati di configurazione da db params field record component
	 *
	 * @access public
	 * @return Object
	 */
	private function &getConfigData() {
		$instance = JComponentHelper::getParams ( 'com_jmap' );
		return $instance;
	}
	
	/**
	 * Effettua lo storing dell'asset delle permissions sul component level
	 *
	 * @access protected
	 * @return boolean
	 */
	protected function storePermissionsAsset($data) {
		// Save the rules.
		if (isset ( $data ['params'] ) && isset ( $data ['params'] ['rules'] )) {
			$form = $this->getForm ( $data );
			// Validate the posted data.
			$postedRules = $this->validate ( $form, $data ['params'] );
			
			$rules = new JAccessRules ( $postedRules ['rules'] );
			$asset = JTable::getInstance ( 'asset' );
			
			if (! $asset->loadByName ( $data ['option'] )) {
				$root = JTable::getInstance ( 'asset' );
				$root->loadByName ( 'root.1' );
				$asset->name = $data ['option'];
				$asset->title = $data ['option'];
				$asset->setLocation ( $root->id, 'last-child' );
			}
			$asset->rules = ( string ) $rules;
			
			if (! $asset->check () || ! $asset->store ()) {
				$this->setError ( $asset->getError () );
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Method to get a form object.
	 *
	 * @param array $data
	 *        	the form.
	 * @param boolean $loadData
	 *        	the form is to load its own data (default case), false if not.
	 *        	
	 * @return mixed JForm object on success, false on failure
	 * @since 1.6
	 */
	public function getForm($data = array(), $loadData = true) {
		jimport ( 'joomla.form.form' );
		JForm::addFormPath ( JPATH_ADMINISTRATOR . '/components/com_jmap' );
		
		// Get the form.
		$form = $this->loadForm ( 'com_jmap.component', 'config', array (
				'control' => 'params',
				'load_data' => $loadData 
		), false, '/config' );
		
		if (empty ( $form )) {
			return false;
		}
		
		return $form;
	}
	
	/**
	 * Ottiene i dati di configurazione del componente
	 *
	 * @access public
	 * @return Object
	 */
	public function &getData() {
		return $this->getConfigData ();
	}
	
	/**
	 * Effettua lo store dell'entity config
	 *
	 * @access public
	 * @return boolean
	 */
	public function storeEntity() {
		$table = JTable::getInstance ( 'extension' );
		// Replace SEF images links
		$base = JUri::root(true) . '/';
		$protocols = '[a-zA-Z0-9]+:';
		$regex = '#(src|href|poster)="(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
		
		try {
			// Found as installed extension
			if (! $extensionID = $table->find ( array (
					'element' => 'com_jmap' 
			) )) {
				throw new JMapException ( $table->getError (), 'error' );
			}
			
			$table->load ( $extensionID );
			
			// Translate posted jform array to params for ORM table binding
			$post = $this->app->input->post;
			
			if (! $table->bind ( $post->getArray ( $this->requestArray[$this->requestName] ) )) {
				throw new JMapException ( $table->getError (), 'error' );
			}
			
			// Unserialize and replace HTML param as RAW no filter
			$unserializedParams = json_decode($table->params);
			$unserializedParams->custom_404_page_text = $this->requestArray[$this->requestName]['params']['custom_404_page_text'];
			$unserializedParams->custom_404_page_text = preg_replace($regex, "$1=\"$base\$2\"", $unserializedParams->custom_404_page_text);
			$table->params = json_encode($unserializedParams);
			
			// pre-save checks
			if (! $table->check ()) {
				throw new JMapException ( $table->getError (), 'error' );
			}
			
			// save the changes
			if (! $table->store ()) {
				throw new JMapException ( $table->getError (), 'error' );
			}
			
			// save the changes
			if (! $this->storePermissionsAsset ( $post->getArray ( $this->requestArray[$this->requestName] ) )) {
				throw new JMapException ( JText::_ ( 'COM_JMAP_ERROR_STORING_PERMISSIONS' ), 'error' );
			}
		} catch ( JMapException $e ) {
			$this->setError ( $e );
			return false;
		} catch ( Exception $e ) {
			$jmapException = new JMapException ( $e->getMessage (), 'error' );
			$this->setError ( $jmapException );
			return false;
		}
		
		// Clean the cache.
		$this->cleanComponentCache ( '_system', 0 );
		$this->cleanComponentCache ( '_system', 1 );
		return true;
	}
	
	/**
	 * Class contructor
	 *
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array()) {
		parent::__construct ( $config );
		
		// App reference
		$this->app = JFactory::getApplication ();
		$this->requestArray = &$GLOBALS;
		$this->requestName = '_' . strtoupper('post');
	}
}