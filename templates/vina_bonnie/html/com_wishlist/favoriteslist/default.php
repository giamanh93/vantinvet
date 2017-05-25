<?php // no direct access 
defined('_JEXEC') or die('Restricted access');

//Loading Main Component Stylesheet
JHtml::stylesheet(VM_THEMEURL.'theme.css', array(), false);
JHtml::stylesheet('components/com_wishlist/template.css', array(), false);

//Loading Font Awesome
JHtml::stylesheet('http://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css', array(), false);

$my_page =& JFactory::getDocument();
$conf =& JFactory::getConfig();
$user =& JFactory::getUser();
$sitename = $conf->get('config.sitename');
$my_page->setTitle($sitename. ' - ' .JText::_( 'VM_FAVORITE_LIST' )); 
?>
<h2 class="fav_title"><?php echo JText::_( 'VM_FAVORITE_LIST' ); ?></h2>
<?php
$itemid = JRequest::getInt('Itemid',  1);
$prod_name = JRequest::getString('prod_name',  "");
$mode = JRequest::getString('mode',  "");
if ($prod_name != "" && $mode == "delete") { 
		JError::raiseNotice( 100, JText::_('VM_DELETED_TITLE').'<strong> '.$prod_name.' </strong>'.JText::_('VM_DELETED_TITLE2'));
}
if (empty( $this->data )){ ?>
	<h2 class='fav_header'><?php echo JText::_('VM_FAVORITE_EMPTY') ?></h2>
	<?php	}
else { 
	//Loading Global Options
	$params = &JComponentHelper::getParams('com_wishlist');
	$tmpl_favbtn_image = $params->get('tmpl_favbtn_image');
	$tmpl_favdate_enabled = $params->get('tmpl_favdate_enabled');
	$tmpl_afaq_enabled = $params->get('tmpl_afaq_enabled');
	$tmpl_favimage_width = $params->get('tmpl_favimage_width');
	//Initialize the Virtuemart Product Model Class
	$productModel = new VirtueMartModelProduct();
	$afaq_message = JText::_('VM_AFAQ_MESSAGE');
	echo "<div class='fav_table'>";
		echo "<div class='fav_heading'>";
			echo "<div class='fav_col' style='min-width:20%'>";
				echo JText::_('VM_FAVHEADER_PRODIMAGE');
			echo "</div>";
			echo "<div class='fav_col'>";
				echo JText::_('VM_FAVHEADER_DETAILS');
			echo "</div>";
			echo "<div class='fav_col'>";
				echo JText::_('VM_FAVHEADER_OPTIONS');
			echo "</div>";
		echo "</div>";
		foreach($this->data as $dataItem)
		{
			$product = $productModel->getProduct($dataItem->product_id);
			$productModel->addImages($product);
			$product_qty = $dataItem->product_qty;
			$product_ord = $product_qty > 0 ? $product_qty : 1;
			$url_favlist = JRoute::_("index.php?option=com_wishlist&view=favoriteslist&Itemid={$itemid}");
			$afaq_message .= "<a href='".JURI::base()."index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=".$dataItem->product_id."' target='_blank'>".$product_ord."x <b>".$product->product_name."</b></a>, ";
			//generate button to remove from favorites list
			$form_deletefavorite = '<form action="'. $url_favlist .'" method="POST" name="deletefavo" id="'. uniqid('deletefavo_') .'">';
			//<input type='submit' class='modns button art-button art-button' value='".JText::_('VM_REMOVE_FAVORITE')."' title='".JText::_('VM_REMOVE_FAVORITE')."' onclick=\"return confirm('".JText::_('VM_REMOVEFAV_CONFIRM')."')\" />
			$form_deletefavorite .= '<button class="modns button art-button art-button" title="'.JText::_('VM_REMOVE_FAVORITE').'" >';
			if ($tmpl_favbtn_image) $form_deletefavorite .= '<i class="fa fa-trash-o"></i>';
			$form_deletefavorite .= JText::_('VM_REMOVE_FAVORITE').'</button>';
			$form_deletefavorite .= '<input type="hidden" name="mode" value="delete" />';
			$form_deletefavorite .= '<input type="hidden" name="fav_id" value="'. $dataItem->fav_id .'" />';
			$form_deletefavorite .= '<input type="hidden" name="prod_name" value="'. $product->product_name .'" />';
			$form_deletefavorite .= '</form>';
			
			echo "<div class='fav_row'>";
				//Display Linked Product Image
				if (!empty($product->images[0]) ) $image = $product->images[0]->displayMediaThumb('width="'.$tmpl_favimage_width.'" border="0"',false) ;
				else $image = '';	
				echo "<div class='fav_col'>";
				echo "<p>".JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id),$image,array('title' => $product->product_name) )."</p>";
				//Display Delete Favorite Form
				echo $form_deletefavorite;
				echo "</div>";
					
				echo "<div class='fav_col'>";
				//Display Favorite Date
				if ($tmpl_favdate_enabled)
				{
					echo "<h4 class='prod_date'>";
					echo JHtml::date($dataItem->fav_date, JText::_('DATE_FORMAT_LC4'));
					echo "</h4>";
				}
				//Display Linked Product Name
				$url_vm = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.
				$product->virtuemart_category_id);
				echo "<a href='".$url_vm."'><h3 class='prod_name'>".$product->product_name."</h3></a>";
				//Display Product Price
				$currency = CurrencyDisplay::getInstance( );
				if (!empty($product->prices['salesPrice'] ) ) echo "<h4 class='prod_price'>".$currency->createPriceDiv('salesPrice','',$product->prices,true)."</h4>";
				//if (!empty($product->prices['salesPriceWithDiscount']) ) echo $currency->createPriceDiv('salesPriceWithDiscount','',$product->prices,true);
				echo "</div>";	
				
				echo "<div class='fav_col'>";
				//Display Add To Cart Form
				FavoritesModelFavoriteslist::addtocart($product, JText::_('VINA_VMART_ADDTOCART_BUTTON'),$product_ord);
				echo "</div>";
			echo "</div>";
		}
	echo "</div>";
	?>
	<div class="pagination">
			<?php echo str_replace('</ul>', '<li class="counter">'.$this->pagination->getPagesCounter().'</li></ul>', $this->pagination->getPagesLinks()); ?>
	</div>
	<?php
	//Ask for a Quote Form
	if (!$user->guest && $tmpl_afaq_enabled)
	{
		echo "<p><div align=\"left\">\n<form action=\"".$url_favlist."\" method=\"POST\" name=\"sendmail\" id=\"sendmail\">\n
		".JText::_('VM_SHARE_DESC')."<br /><textarea id=\"afaq_desc\" class=\"inputbox\" cols=\"50\" rows=\"3\" name=\"afaq_desc\" onkeypress=\"return imposeMaxLength(this, 100);\"></textarea><br /><br />\n
		<input type=\"hidden\" name=\"mode\" value=\"sendmail\" />\n
		<input type=\"hidden\" id=\"email_to\" name=\"email_to\" value=\"".$conf->get('mailfrom')."\" />\n
		<input type=\"hidden\" id=\"email_subj\" name=\"email_subj\" value=\"".JText::_('VM_AFAQ_SUBJECT')."\" />\n
		<input type=\"hidden\" id=\"email_body\" name=\"email_body\" value=\"".$afaq_message."\" />\n
		<input type=\"submit\" class=\"modns button art-button art-button\" value=\"".JText::_('VM_AFAQ_BUTTON')."\" title=\"".JText::_('VM_AFAQ_BUTTON')."\" />
		</form>\n</div>\n</p>\n";
	}
}
vmJsApi::jQuery();
vmJsApi::jPrice();
vmJsApi::cssSite();
?>
