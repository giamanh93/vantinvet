<?php
/**
 *
 * Show the product details page
 *
 * @package	VirtueMart
 * @subpackage
 * @author Max Milbers, Valerie Isaksen

 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_manufacturer.php 8610 2014-12-02 18:53:19Z Milbo $
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>
<div class="manufacturer">
    <?php
    $link = JRoute::_('index.php?option=com_virtuemart&view=manufacturer&virtuemart_manufacturer_id=' . $this->product->virtuemart_manufacturer_id[0] . '&tmpl=component', FALSE);
    $text = $this->product->mf_name;

    /* Avoid JavaScript on PDF Output */
    if (strtolower(vRequest::getCmd('output')) == "pdf") {
	echo JHtml::_('link', $link, $text);
    } else {
	?>
        <span class="bold"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_DETAILS_MANUFACTURER_LBL').': '; ?></span><a class="manuModal" href="<?php echo $link ?>"><?php echo $text ?></a>
    <?php } ?>
</div>