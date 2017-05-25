<?php
/**
 * @package Helix Framework
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2013 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

$modChromes = array('sp_strong', 'sp_xhtml', 'sp_vinaXhtml', 'sp_flat', 'sp_raw', 'sp_menu', 'none'  );
function modChrome_sp_strong($module, $params, $attribs)
{ ?>
<div class="module <?php echo $params->get('moduleclass_sfx'); ?>">	
	<div class="mod-wrapper sp_strong clearfix">		
		<?php if ($module->showtitle != 0) { ?>
			<div class="container">
				<h3 class="header vina-title">		
					<span>
					<?php 
						$title= str_replace(array('{','}'), array('<strong>','</strong>'), $module->title);
						echo $title;
					?>
					</span>
				</h3>
			</div>
			<?php
				/*$modsfx=$params->get('moduleclass_sfx');
				if ($modsfx !='') echo '<span class="sp-badge ' . $modsfx . '"></span>';*/
			?>
			<?php } ?>
		<div class="mod-content clearfix">	
			<div class="mod-inner clearfix">
				<?php echo $module->content; ?>
			</div>
		</div>
	</div>
</div>
<div class="gap"></div>
<?php
}

function modChrome_sp_xhtml($module, $params, $attribs)
{ ?>
<div class="module <?php echo $params->get('moduleclass_sfx'); ?>">	
	<div class="mod-wrapper clearfix">		
		<?php if ($module->showtitle != 0) { ?>
			<h3 class="header">			
				<?php					
					$title= str_replace(array('[',']'), array('<span style="display: none;" class=" ','"> </span>'), $module->title);					
					echo '<span>'.$title.'</span>';					
				?>
			</h3>
			<?php
				$modsfx=$params->get('moduleclass_sfx');
				if ($modsfx !='') echo '<span class="sp-badge ' . $modsfx . '"></span>';
			?>
			<?php } ?>
		<div class="mod-content clearfix">	
			<div class="mod-inner clearfix">
				<?php echo $module->content; ?>
			</div>
		</div>
	</div>
</div>
<div class="gap"></div>
<?php
}

function modChrome_sp_vinaXhtml($module, $params, $attribs)
{ ?>
<div class="module <?php echo $params->get('moduleclass_sfx'); ?>">	
	<div class="mod-vina-wrapper clearfix">		
		<?php if ($module->showtitle != 0) { ?>
			<h3 class="vina-header">			
				<?php					
					$title= str_replace(array('[',']'), array('<span style="display: none;" class=" ','"> </span>'), $module->title);					
					echo '<span>'.$title.'</span>';					
				?>
			</h3>
			<?php
				$modsfx=$params->get('moduleclass_sfx');
				if ($modsfx !='') echo '<span class="sp-badge ' . $modsfx . '"></span>';
			?>
			<?php } ?>
		<div class="mod-vina-content clearfix">	
			<div class="mod-inner clearfix">
				<?php echo $module->content; ?>
			</div>
		</div>
	</div>
</div>
<div class="gap"></div>
<?php
}

function modChrome_sp_flat($module, $params, $attribs)
{ ?>
<div class="module <?php echo $params->get('moduleclass_sfx'); ?>">	
	<div class="mod-wrapper-flat clearfix">		
		<?php if ($module->showtitle != 0) { ?>
			<h3 class="header">			
				<?php 
					echo '<span>'.$module->title.'</span>';
				?>
			</h3>
			<?php
				$modsfx=$params->get('moduleclass_sfx');
				if ($modsfx !='') echo '<span class="sp-badge ' . $modsfx . '"></span>';
			?>
			<?php } ?>
		<?php echo $module->content; ?>
	</div>
</div>
<div class="gap"></div>
<?php
}

function modChrome_sp_raw($module, $params, $attribs)
{ 
	echo $module->content; 
}

function modChrome_sp_menu($module, $params, $attribs)
{ ?>
<div class="module <?php echo $params->get('moduleclass_sfx'); ?>">	
	<div class="mod-wrapper-menu clearfix">
		<?php echo $module->content; ?>
	</div>
</div>
<?php
}