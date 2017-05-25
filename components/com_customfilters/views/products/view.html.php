<?php
/**
 *
 * Customfilters products view
 *
 * @package		customfilters
 * @author		Sakis Terz
 * @link		http://breakdesigns.net
 * @copyright	Copyright (c) 2010 - 2013 breakdesigns.net. All rights reserved.
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *				customfilters is free software. This version may have been modified
 *				pursuant to the GNU General Public License, and as distributed
 *				it includes or is derivative of works licensed under the GNU
 *				General Public License or other free or open source software
 *				licenses.
 * @version $Id: view.html.php 2013-11-21 18:27:00Z sakis $
 */

// No direct access
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'cfview.php';

class CustomfiltersViewProducts extends cfView{
	public $vm_version;


	public function display($tpl = null){

		$app=JFactory::getApplication();
		$this->addHelperPath(JPATH_VM_ADMINISTRATOR.DIRECTORY_SEPARATOR.'helpers');
		require_once(JPATH_VM_SITE.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'shopfunctionsf.php'); //dont remove that file it is actually in every view
		$this->show_prices  = VmConfig::get('show_prices',1);
		if($this->show_prices == '1'){
			if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'calculationh.php');
		}
		if (!class_exists('VirtueMartModelCategory')) require(JPATH_VM_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . 'category.php');

		$this->vm_version=VmConfig::getInstalledVersion();
		$this->showproducts=true;
		//get menu parameters
		$this->menuParams=cftools::getMenuparams();
		$vendorId = 1;
		$jinput=$app->input;
		$categories=$jinput->get('virtuemart_category_id',array(),'array');

		/*If there is only one category selected and is not zero, display children categories*/
		if(count($categories)==1 && isset($categories[0]) && $categories[0]>0){
			$this->categoryId=$categories[0];
			$category_haschildren=true;
		}else{
			$this->categoryId=0;
			$category_haschildren=false;
		}

		$categoryModel = VmModel::getModel('category');
		$category = $categoryModel->getCategory($this->categoryId);
		$categoryModel->addImages($category,1);
		$category->haschildren=$category_haschildren;

		//Virtuemart > 2.0.24a is using this method
		if($category_haschildren){
			if(version_compare($this->vm_version, '2.0.24a')>0){
				$category->children = $categoryModel->getChildCategoryList( $vendorId, $this->categoryId, $categoryModel->getDefaultOrdering(), $categoryModel->_selectedOrderingDir );
			}else{
				$cache = JFactory::getCache('com_virtuemart','callback');
				$category->children = $cache->call( array( 'VirtueMartModelCategory', 'getChildCategoryList' ),$vendorId, $this->categoryId );
			}
		}

		$categoryModel->addImages($category->children,1);


		if (VmConfig::get('enable_content_plugin', 0)) {
			//Virtuemart 2.0.24 and later
			if(method_exists('shopFunctionsF','triggerContentPlugin'))shopFunctionsF::triggerContentPlugin($category, 'category','category_description');
			//Virtuemart below 2.0.24
			else{
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('content');
				$category->text = $category->category_description;
				if(!class_exists('JParameter')) require(JPATH_LIBRARIES.DIRECTORY_SEPARATOR.'joomla'.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'parameter.php');

				$params = new JParameter('');
				$results = $dispatcher->trigger('onContentPrepare', array('com_virtuemart.category', &$category, &$params, 0));
				// More events for 3rd party content plugins
				// This do not disturb actual plugins, because we don't modify $product->text
				$res = $dispatcher->trigger('onContentAfterTitle', array('com_virtuemart.category', &$category, &$params, 0));
				$category->event->afterDisplayTitle = trim(implode("\n", $res));

				$res = $dispatcher->trigger('onContentBeforeDisplay', array('com_virtuemart.category', &$category, &$params, 0));
				$category->event->beforeDisplayContent = trim(implode("\n", $res));

				$res = $dispatcher->trigger('onContentAfterDisplay', array('com_virtuemart.category', &$category, &$params, 0));
				$category->event->afterDisplayContent = trim(implode("\n", $res));
				$category->category_description = $category->text;
			}
		}
		$this->category=$category;
		//load basic libraries before any other script
		$template = VmConfig::get('vmtemplate','default');
		if (is_dir(JPATH_THEMES.DIRECTORY_SEPARATOR.$template)) {
			$mainframe = JFactory::getApplication();
			$mainframe->set('setTemplate', $template);
		}
		$this->_prepareDocument();

		/*
		 * show base price variables
		 */
		$user = JFactory::getUser();

		$this->showBasePrice = ($user->authorise('core.admin','com_virtuemart') or $user->authorise('core.manage','com_virtuemart'));


		/*
		 * get the products from the cf model
		 */
		$productModel = VmModel::getModel('product');
		//rating
		$ratingModel = VmModel::getModel('ratings');
		$this->showRating = $ratingModel->showRating();
		$productModel->withRating = $this->showRating;	
		
		$ids=$this->get('ProductListing');
		$this->products=$productModel->getProducts($ids);		
		
		$productModel->addImages($this->products,1);
		$model=$this->getModel();
		//add stock
		foreach($this->products as $product){
			$product->stock = $productModel->getStockIndicator($product);
		}

		//currency
		if ($this->products) {
			if(!class_exists('CurrencyDisplay'))require(JPATH_VM_ADMINISTRATOR.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'currencydisplay.php');
			$currency = CurrencyDisplay::getInstance( );
			$this->assignRef('currency', $currency);

			//vm3 is loading the custom fields to the category page
			if(version_compare($this->vm_version, '2.9','ge')>0){
				$customfieldsModel = VmModel::getModel ('Customfields');
				if (!class_exists ('vmCustomPlugin'))require(JPATH_VM_PLUGINS . DIRECTORY_SEPARATOR . 'vmcustomplugin.php');

				foreach($this->products as $i => $productItem){

					if (!empty($productItem->customfields)) {
						$product = clone($productItem);
						$customfields = array();
						foreach($productItem->customfields as $cu){
							$customfields[] = clone ($cu);
						}

						$customfieldsSorted = array();
						$customfieldsModel -> displayProductCustomfieldFE ($product, $customfields);

						foreach ($customfields as $k => $custom) {
							if (!empty($custom->layout_pos)  ) {
								$customfieldsSorted[$custom->layout_pos][] = $custom;
								unset($customfields[$k]);
							}
						}
						$customfieldsSorted['normal'] = $customfields;
						$product->customfieldsSorted = $customfieldsSorted;
						unset($product->customfields);
						$this->products[$i] = $product;
					}
				}
			}
		}
		$productsLayout = VmConfig::get('productsublayout','products');
		if(empty($productsLayout)) $productsLayout = 'products';
		$this->productsLayout =$productsLayout;	
		
		

		//Pagination
		$u=JFactory::getURI();
		$query=$u->getQuery();
		$this->search=false;
		$this->searchcustom = '';
		$this->searchCustomValues = '';
		$this->keyword='';
		$this->vmPagination = $model->getPagination(true); //my model's pagination
		$this->perRow=$this->menuParams->get('prod_per_row',3);
		$this->orderByList = $this->get('OrderByList');

		parent::display($tpl);
        if(empty($this->products))
		echo '<span class="cf_results-msg">'.JText::_('COM_CUSTOMFILTERS_NO_PRODUCTS').'</span>';
	}



	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$document=JFactory::getDocument();
		$app=JFactory::getApplication();
		$joomla_conf=JFactory::getConfig();
		$this->setCanonical();
		
		/*
		 * Add meta data
		 */
		if($this->categoryId>0){
			if (!empty($this->category->metadesc)) {
				$document->setDescription($this->category->metadesc );
			}
			if (!empty($this->category->metakey)) {
				$document->setMetaData('keywords', $this->category->metakey);
			}
			if (!empty($this->category->metarobot)) {
				$document->setMetaData('robots', $this->category->metarobot);
			}

			if ($joomla_conf->get('MetaAuthor') == true && !empty($this->category->metaauthor)) {
				$document->setMetaData('author',$this->category->metaauthor);
			}
		}


		/*
		 * Load scripts and styles
		 */
		cftools::loadScriptsNstyles();

		//layout
		$this->_setPath('template',(JPATH_BASE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'category'.DIRECTORY_SEPARATOR.'tmpl'));
		$layout=$this->menuParams->get('cfresults_layout');
		$this->setLayout($layout);

		//load the virtuemart language files
		if(method_exists('VmConfig', 'loadJLang'))VmConfig::loadJLang('com_virtuemart',true);
		else{
			$language=JFactory::getLanguage();
			$language->load('com_virtuemart');
		}
	}

	/**
	 *
	 * Add canonical urls to the head of the pages
	 * If there is another canonical replaces it with a new one
	 *
	 * @since	2.2.0
	 */
	function setCanonical(){
		$document=JFactory::getDocument();
		$inputs=CfInput::getInputs();
		if(count($inputs)==1){
			if(!empty($inputs['virtuemart_category_id'])){
				$currentlink='&virtuemart_category_id='.(int)$inputs['virtuemart_category_id'][0];
			}
			else if(!empty($inputs['virtuemart_manufacturer_id'])){
				$currentlink='&virtuemart_manufacturer_id='.(int)$inputs['virtuemart_manufacturer_id'][0];
			}
		}

		if(!empty($currentlink)){ 
			$canonical_url=JRoute::_('index.php?option=com_virtuemart&view=category'.$currentlink);
			
			$links= $document->_links;
			foreach($links as $key=>$link){
				if(is_array($link)){
					if(array_key_exists('relation', $link) && !empty($link['relation']) && $link['relation']=='canonical'){
						//found it - delete the old						
						unset($document->_links[$key]);
					}
				}
			}
			//add a new one
			$document->_links[$canonical_url]=array('relType'=>'rel','relation'=>'canonical','attribs'=>'');
		}
	}
}