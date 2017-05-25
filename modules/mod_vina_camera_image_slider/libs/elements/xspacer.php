<?php 
/**
 * @package X Effect for Joomla! 2.5
 * @author http://Thecoders.vn
 * @copyright (C) 2011- Thecoders.vn
 * @license http://www.gnu.org/licenseses/gpl-2.0.html GNU/GPL
**/
// no direct access
defined('_JEXEC') or die;
/**
 * Get a collection of categories
 */
class JFormFieldXSpacer extends JFormField
{
	/*
	 * Category name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $type = 'xspacer'; 	
	
	/**
	 * fetch Element 
	 */
	protected function getInput()
	{		
		if(!defined ('TCVN_LOADMEDIACONTROL'))
		{
			define ('TCVN_LOADMEDIACONTROL', 1);
			$uri = str_replace(DS,"/",str_replace( JPATH_SITE, JURI::base (), dirname(__FILE__) ));
			$uri = str_replace("/administrator/", "", $uri);			
			JHTML::stylesheet($uri."/media/".'form.css');
			JHTML::script($uri."/media/".'form.js');
		}
		if($this->title=='end_form'){
			?>
            	<script type="text/javascript">
					var panels = $$("#module-form .pane-sliders  > .panel").fade("out").removeClass("panel").addClass("tcvn-panel");
					var div = new Element("div", {"class":"tcvn-wrapper"});
					var container = new Element("div", {"class":"tcvn-container"});
					container.innerHTML='<fieldset class="fs-form"><legend><?php echo JText::_("Module Setting")?></legend><div class="tcvn-toolbars"></div><div class="tcvn-fscontainer"></div></legend></fieldset>';
					var _toolbar = container.getElement(".tcvn-toolbars");
					var _container = container.getElement(".tcvn-fscontainer");
					$$("#module-form .pane-sliders").adopt(  div.adopt(container) );
					new TCVNForm(panels, _toolbar, _container );
				</script>
            <?php
		}
	}
		
	function getLabel(){
		return ;	
	}
}
?>