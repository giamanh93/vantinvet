<?php
/**
 * @package 	customfilters
 * @author		Sakis Terz
 * @link		http://breakdesigns.net
 * @copyright	Copyright (c) 2012 - 2014 breakdesigns.net. All rights reserved.
 * @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *				customfilters is free software. This version may have been modified
 *				pursuant to the GNU General Public License, and as distributed
 *				it includes or is derivative of works licensed under the GNU
 *				General Public License or other free or open source software
 *				licenses.
 * @version 	$Id: default 1 2014-06-06 19:35 sakis $
 * @since		1.0
 */

// no direct access
defined('_JEXEC') or die;
JHtml::_('behavior.modal');

if (version_compare(JVERSION, '2.5.19', 'lt')): ?>
<div class="alert alert-info">
<?php echo JText::_('COM_CUSTOMFILTERS_UPDATE_JOOMLA_VERSION'); ?>
</div>
<?php endif; ?>


<div class="info_pane">
<p><?php echo JText::_('STARTER_DESCR_1'),' Custom Filters <b>Starter</b> edition.'?></p>
<p><?php echo JText::_('STARTER_DESCR_2')?></p>
<p><?php echo JText::_('STARTER_DESCR_3')?>
<a href="http://breakdesigns.net/extensions/custom-filters" target="_blank">Custom Filters</a>
<?php echo JText::_('COM_CUSTOMFILTERS_PAGE')?>
</p>
</div>
<?php echo $this->loadTemplate('update'); ?>