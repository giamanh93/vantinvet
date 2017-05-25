<?php 
/**
 * @package X Toolbar for Joomla! 2.5
 * @author http://Thecoders.vn
 * @copyright (C) 2011- Thecoders.vn
 * @license http://www.gnu.org/licenseses/gpl-2.0.html GNU/GPL
**/
 
// no direct access
defined('_JEXEC') or die;
/**
 * Get a collection of categories
 */
class JFormFieldXToolbar extends JFormField
{
	/*
	 * Category name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $type = 'xtoolbar'; 	
	
	/**
	 * fetch Element 
	 */
	protected function getInput()
	{		
?> 
	<ul class="tcvn-toolbar-items">
    	<?php foreach( $this->element->children() as $option ) { ?>
    	<li><?php echo $option->data();?></li>
        <?php } ?>
    </ul>	
<?php 
	}
	function getLabel(){
		return ;	
	}
}
?>