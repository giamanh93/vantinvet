<?php
// namespace components\com_jmap\controllers;
/**
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Main controller class
 *
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage controllers
 * @since 3.5
 */
class JMapControllerGeositemap extends JMapController {
	/**
	 * Display the Sitemap
	 *
	 * @access public
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		// Get REQUEST vars all used to makeId for cache handler
		$option = $this->option;
		$format = $this->app->input->get ( 'format', 'xml' );
		$language = JMapLanguageMultilang::getCurrentSefLanguage();
		
		// Get sitemap model and view core
		$document = JFactory::getDocument ();
		$viewType = $document->getType ();
		$coreName = $this->getNames ();
		$viewLayout = $this->app->input->get ( 'layout', 'default' );
		
		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath 
		) );
		
		// Get/Create the model
		if ($model = $this->getModel ( $coreName, 'JMapModel')) {
			// Push the model into the view (as default)
			$view->setModel ( $model, true );
		}
		
		// Set model state
		$model->setState ( 'format', $format );
		$model->setState ( 'language', $language );
		
		// Get an instance of the HTTP client
		$HTTPClient = new JMapHttp();
		$view->set('httpclient', $HTTPClient);
		
		// Set the layout
		$view->setLayout ( $viewLayout );
		$view->display ( $format );
	}
}