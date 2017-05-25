<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Coupon
* @author RickG
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default.php 8534 2014-10-28 10:23:03Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

AdminUIHelper::startAdminArea($this);

?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div id="editcell">
	    <table class="adminlist table table-striped" cellspacing="0" cellpadding="0">
	    <thead>
		<tr>
		    <th class="admin-checkbox">
			<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
		    </th>
		    <th width="25%">
			<?php echo vmText::_('COM_VIRTUEMART_COUPON_CODE'); ?>
		    </th>
		    <th width="16%">
			<?php echo vmText::_('COM_VIRTUEMART_COUPON_PERCENT_TOTAL'); ?>
		    </th>
		    <th width="16%">
			<?php echo vmText::_('COM_VIRTUEMART_COUPON_TYPE'); ?>
		    </th>
		    <th width="16%">
			<?php echo vmText::_('COM_VIRTUEMART_VALUE'); ?>
		    </th>
		    <th min-width="130px" width="18%">
			<?php echo vmText::_('COM_VIRTUEMART_COUPON_VALUE_VALID_AT'); ?>
		    </th>
			<th min-width="100px" width="18%">
				<?php echo vmText::_('COM_VIRTUEMART_COUPON_USED'); ?>
			</th>
		     <th><?php echo $this->sort('virtuemart_coupon_id', 'COM_VIRTUEMART_ID')  ?></th>
		</tr>
	    </thead>
	    <?php
	    $k = 0;
	    for ($i=0, $n=count($this->coupons); $i < $n; $i++) {
		$row = $this->coupons[$i];

		$checked = JHtml::_('grid.id', $i, $row->virtuemart_coupon_id);
		$editlink = JROUTE::_('index.php?option=com_virtuemart&view=coupon&task=edit&cid[]=' . $row->virtuemart_coupon_id);
		?>
	    <tr class="row<?php echo $k; ?>">
		<td class="admin-checkbox">
			<?php echo $checked; ?>
		</td>
		<td align="left">
		    <a href="<?php echo $editlink; ?>"><?php echo $row->coupon_code; ?></a>
		</td>
		<td>
			<?php echo vmText::_('COM_VIRTUEMART_COUPON_'.strtoupper($row->percent_or_total)); ?>
		</td>
		<td align="left">
			<?php echo vmText::_('COM_VIRTUEMART_COUPON_TYPE_'.strtoupper($row->coupon_type)); ?>
		</td>
		<td>
			<?php echo vmText::_($row->coupon_value); ?>
		    <?php if ( $row->percent_or_total=='percent') echo '%' ;
		    else echo $this->vendor_currency;   ?>
		</td>
		<td align="left">
			<?php echo vmText::_($row->coupon_value_valid); ?> <?php echo $this->vendor_currency; ?>
		</td>
		    <td align="center">
			    <?php
			    if( $row->coupon_type=='gift'){
				    if ($row->coupon_used ) {
					    echo vmText::_('COM_VIRTUEMART_YES');
				    } else  {
					    echo vmText::_('COM_VIRTUEMART_NO');
				    }
			     }
			    ?>
		    </td>
		<td align="left">
			<?php echo vmText::_($row->virtuemart_coupon_id); ?>
		</td>
	    </tr>
		<?php
		$k = 1 - $k;
	    }
	    ?>
	    <tfoot>
		<tr>
		    <td colspan="10">
			<?php echo $this->pagination->getListFooter(); ?>
		    </td>
		</tr>
	    </tfoot>
	</table>
    </div>

    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="controller" value="coupon" />
    <input type="hidden" name="view" value="coupon" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo JHtml::_( 'form.token' ); ?>
</form>



<?php AdminUIHelper::endAdminArea(); ?>