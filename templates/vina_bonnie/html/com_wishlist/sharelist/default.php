<?php // no direct access
defined('_JEXEC') or die('Restricted access');

//Addding Main CSS/JS VM_Theme files to header
JHtml::stylesheet(VM_THEMEURL.'theme.css', array(), false);
JHtml::stylesheet('components/com_wishlist/template.css', array(), false);

$my_page =& JFactory::getDocument();
$conf =& JFactory::getConfig();
$sitename = $conf->get('config.sitename');
$option = JRequest::getString('option',  "");
$view = JRequest::getString('view',  "");
$user_id = JRequest::getInt('user_id');
$itemid = JRequest::getInt('Itemid', 1);
if (empty( $this->data )){ ?>
	<h2 class="fav_title"><?php echo JText::_( 'VM_SHARELIST_ERROR' ); ?></h2>
	<h2 class='fav_header'><?php echo JText::_('VM_SHARELIST_DENY'); ?> </h2>
<?php
	}
else
{
	//Loading Global Options
	$params = &JComponentHelper::getParams( 'com_wishlist' );
	$tmpl_favdate_enabled = $params->get( 'tmpl_favdate_enabled' );
	$tmpl_favimage_width = $params->get( 'tmpl_favimage_width' );
	$hdr_data = $this->data[0];
	$share_pass = JRequest::getString('share_pass',  "");
	$form_link = JRoute::_('index.php?option='.$option.'&view='.$view.'&user_id='.$user_id.'&Itemid='.$itemid);
	$form_share_link = JRoute::_('index.php?option='.$option.'&view='.$view.'&user_id='.$user_id.'&Itemid='.$itemid, true, -1);
	if ($hdr_data->share_pass == "" || $hdr_data->share_pass == md5($share_pass))
	{
		$uname = $hdr_data->name;
		$uid = $hdr_data->user_id;
		$shdate = $hdr_data->share_date;
		$shtitle = $hdr_data->share_title;
		$shdesc = $hdr_data->share_desc;
		$iswishlist = $hdr_data->isWishList;
		$my_page->setTitle($sitename. ' - ' .$uname." ". JText::_( 'VM_WISHLIST' ).' | ' . $shtitle);
		//Social Sharing Links
		if (!empty( $this->data ))
		{
			$form_social_fb = '<div id="fb-root"></div><script src="http://connect.facebook.net/en_US/all.js#appId=143548565733526&amp;xfbml=1"></script><fb:like href="'.$form_share_link.'" send="true" layout="button_count" width="100" show_faces="false" action="recommend"></fb:like>';
			$form_social_tw ='<a href="http://twitter.com/share" class="twitter-share-button" data-url="'.$form_share_link.'" data-text="'.$uname. ' - ' .$shtitle.'" data-count="horizontal">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';
			$form_social_gp ='<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script><g:plusone size="medium" href="'.$form_share_link.'"></g:plusone>';
			echo '<h3>'. JText::_( 'VM_SOCIAL_SHARE' ). '</h3>';
			echo $form_social_fb;
			echo $form_social_tw;
			echo $form_social_gp;
		}
		?>
		<p><h2 class="fav_title">
		<?php
			if (!$iswishlist) {
				echo $uname." ".JText::_('VM_FAVORITES'); 
				$my_page->setTitle($sitename. ' - ' .$uname." ".JText::_('VM_FAVORITES').' | ' .$shtitle); 
			}
			else {
				echo $uname." ".JText::_('VM_WISHLIST'); 
				$my_page->setTitle($sitename. ' - ' .$uname." ".JText::_('VM_WISHLIST').' | ' .$shtitle);
			}
		?>
		</h2></p>
		<h2 class="fav_header"><?php echo $shtitle." - ".$shdesc;  ?></h2>
		<?php
		//Initialize the Virtuemart Product Model Class
		$productModel = new VirtueMartModelProduct();
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
			
			//Initialize Variables
			$product_qty = $dataItem->product_qty;
			$url_vm = JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='
			.$product->virtuemart_product_id.'&virtuemart_category_id='
			.$product->virtuemart_category_id);
			
			echo "<div class='fav_row'>";
				//Display Linked Product Image
				if (!empty($product->images[0]) ) $image = $product->images[0]->displayMediaThumb('width="'.$tmpl_favimage_width.'" border="0"',false) ;
				else $image = '';
				echo "<div class='fav_col'>";
				echo "<p>".JHTML::_('link', JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$product->virtuemart_product_id.'&virtuemart_category_id='.$product->virtuemart_category_id),$image,array('title' => $product->product_name) )."</p>";
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
					echo "<a href='".$url_vm."'><h3 class='prod_name'>".$product->product_name."</h3></a>";
					//Display Product Price
					$currency = CurrencyDisplay::getInstance( );
					if (!empty($product->prices['salesPrice'] ) ) echo "<h4 class='prod_price'>".$currency->createPriceDiv('salesPrice','',$product->prices,true)."</h4>";
					//if (!empty($product->prices['salesPriceWithDiscount']) ) echo $currency->createPriceDiv('salesPriceWithDiscount','',$product->prices,true);
					//Display Availability
					echo "<p><h4>";
					if ($iswishlist && $product_qty > -1) echo "<strong>".JText::_('VM_WISHLIST_AVAILABLE').":</strong> ".sprintf($product_qty);
					else if ($iswishlist && $product_qty <= -1) echo "<strong>".JText::_('VM_WISHLIST_AVAILABLE').": </strong>".JText::_('VM_WISHLIST_UNLIMITED');
					echo "</h4></p>";
				echo "</div>";	
				
				echo "<div class='fav_col'>";
					//Display Add To Cart Form
					if ($iswishlist && $product_qty <> 0) FavoritesModelSharelist::addtocart($product,JText::_('VM_WISHLIST_GIFTIT'),$iswishlist,$uname);
					else if ($iswishlist && $product_qty == 0) echo "<h2 class=\"wish_alert\">".JText::_('VM_WISHLIST_UNAVAILABLE')."</h2>";
					else FavoritesModelSharelist::addtocart($product,JText::_('COM_VIRTUEMART_CART_ADD_TO'),$iswishlist,$uname);
				echo "</div>";
			echo "</div>";
		}
		echo "</div>";
		?>
		<div class="pagination">
			<?php echo str_replace('</ul>', '<li class="counter">'.$this->pagination->getPagesCounter().'</li></ul>', $this->pagination->getPagesLinks()); ?>
		</div>
		<?php
	}
	else
	{
		if ($share_pass != "" && $hdr_data->share_pass != md5($share_pass)) $wrongpass_txt = JText::_( 'VM_PASSWORD_WRONG' );
		?>
			<h2 class="fav_title"><?php echo JText::_( 'VM_PASSWORD_ACCESS' ); ?></h2>
			<h2 class="fav_header"><?php echo JText::_( 'VM_PASSWORD_PROMPT' ); ?></h2>
		<?php
		echo"<form action=\"".$form_link."\" method=\"POST\" name=\"share\" id=\"share\">\n
				<input id=\"share_pass\" type=\"password\" class=\"inputbox\" size=\"35\" maxlength=\"32\" name=\"share_pass\" />\n
				<input type=\"hidden\" name=\"option\" value=\"$option\" />\n
				<input type=\"hidden\" name=\"user_id\" value=\"$user_id\" />\n
				<input type=\"hidden\" name=\"view\" value=\"$view\" />\n
				<input type=\"hidden\" name=\"Itemid\" value=\"$itemid\" />\n
				<p>".$wrongpass_txt."</p>
				<p style=\"padding:20px 0 20px 0\">
				<input type=\"button\" class=\"modns button art-button art-button addtocart_button\" value=\"".JText::_( 'VM_SHARELIST_BACK' )."\" title=\"".JText::_( 'VM_SHARELIST_BACK' )."\" onclick=\"javascript:history.back()\" />
				<input type=\"submit\" class=\"modns button art-button art-button\" value=\"".JText::_('VM_ACCESS_BUTTON')."\" title=\"".JText::_('VM_SHARE_BUTTON')."\" />
				</p>
			  </form>";
	}
}
vmJsApi::jQuery();
vmJsApi::jPrice();
vmJsApi::cssSite();
?>