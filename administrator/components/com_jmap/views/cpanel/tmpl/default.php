<?php 
/** 
 * @package JMAP::CPANEL::administrator::components::com_jmap
 * @subpackage views
 * @subpackage cpanel
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
?>

<div class="row-fluid">
	<div class="span12">
		<div class="row no-margin">
			<!-- CPANEL ICONS -->
			<div class="panel-group span6" id="accordion_cpanel_icons">
				<div class="panel panel-default">
				    <div class="panel-heading accordion-toggle accordion_lightblue noaccordion">
						<h4 class="panel-title">
							<span class="glyphicon glyphicon-tasks"></span>
							<?php echo JText::_('COM_JMAP_ICONS');?>
						</h4>
				    </div>
				    <div id="jmap_icons"  class="panel-collapse collapse in">
						<div class="panel-body">
							<?php echo $this->icons; ?>
							<div id="updatestatus">
								<?php 
								if(is_object($this->updatesData)) {
									if(version_compare($this->updatesData->latest, $this->currentVersion, '>')) { ?>
										<a href="http://storejextensions.org/extensions/jsitemap_professional.html" target="_blank" alt="storejoomla link">
											<label data-content="<?php echo JText::sprintf('COM_JMAP_GET_LATEST', $this->currentVersion, $this->updatesData->latest, $this->updatesData->relevance);?>" class="label label-danger hasPopover">
												<label class="glyphicon glyphicon-warning-sign"></label>
												<?php echo JText::sprintf('COM_JMAP_OUTDATED', $this->updatesData->latest);?>
											</label>
										</a>
									<?php } else { ?>
										<label data-content="<?php echo JText::sprintf('COM_JMAP_YOUHAVE_LATEST', $this->currentVersion);?>" class="label label-success hasPopover">
											<label class="glyphicon glyphicon-ok-sign"></label>
											<?php echo JText::sprintf('COM_JMAP_UPTODATE', $this->updatesData->latest);?>
										</label>	
									<?php }
									}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<!-- RIGHT ACCORDION -->
			<div class="panel-group span6" id="accordion_cpanel">
				<!-- SEO STATS -->
				<?php if($this->componentParams->get('seostats_enabled', '1')): ?>
				<div class="panel panel-default">
				    <div class="panel-heading accordion-toggle" data-toggle="collapse" data-parent="#accordion_cpanel" data-target="#seo_stats">
						<h4 class="panel-title">
							<span class="glyphicon glyphicon-stats"></span>
							<?php echo JText::sprintf('COM_JMAP_SEO_STATS', $this->componentParams->get('seostats_custom_link', JUri::root()));?>
						</h4>
				    </div>
				    <div id="seo_stats"  class="panel-collapse collapse">
						<div class="panel-body">
							<!-- PAGERANKS STATUS INDICATOR -->
							<div class="single_stat_container">
								<div class="statcircle">
									<span class="glyphicon glyphicon-align-left glyphicon-large"></span>
								</div>
								<ul class="subdescription_stats">
									<li data-bind="{alexa_rank}" class="es-stat-no"></li>
									<li class="es-stat-title"><?php echo JText::_('COM_JMAP_ALEXA_PAGE_RANK');?></li>
								</ul>
							</div>
							<div class="single_stat_container">
								<div class="statcircle">
									<span class="glyphicon glyphicon-align-left glyphicon-large"></span>
								</div>
								<ul class="subdescription_stats">
									<li data-bind="{google_pagerank}" class="es-stat-no"></li>
									<li class="es-stat-title"><?php echo JText::_('COM_JMAP_GOOGLE_PAGE_RANK');?></li>
								</ul>
							</div>
							<div class="single_stat_container">
								<div class="statcircle">
									<span class="glyphicon glyphicon-hdd glyphicon-large"></span>
								</div>
								<ul class="subdescription_stats">
									<li data-bind="{google_indexed_links}" class="es-stat-no"></li>
									<li class="es-stat-title"><?php echo JText::_('COM_JMAP_GOOGLE_INDEXED_LINKS');?></li>
								</ul>
							</div>
							<div class="single_stat_container">
								<div class="statcircle">
									<span class="glyphicon glyphicon-transfer glyphicon-large"></span>
								</div>
								<ul class="subdescription_stats">
									<li data-bind="{alexa_backlinks}" class="es-stat-no"></li>
									<li class="es-stat-title"><?php echo JText::_('COM_JMAP_ALEXA_BACKLINKS');?></li>
								</ul>
							</div>
							<div class="single_stat_container">
								<div class="statcircle">
									<span class="glyphicon glyphicon-dashboard glyphicon-large"></span>
								</div>
								<ul class="subdescription_stats">
									<li data-bind="{alexa_pageload_time}" class="es-stat-no stats-small"></li>
									<li class="es-stat-title"><?php echo JText::_('COM_JMAP_ALEXA_PAGELOADTIME');?></li>
								</ul>
							</div>
							<div class="single_stat_container alexachart">
								<ul class="subdescription_stats alexachart">
									<li data-bind="{alexa_graph}" class="es-stat-no fancybox-image"></li>
									<li class="es-stat-title"><?php echo JText::_('COM_JMAP_ALEXA_GRAPH');?></li>
								</ul>
							</div>

							<div class="single_stat_rowseparator"></div>
								
							<!-- SEMRush row -->
							<div class="single_stat_container">
								<div class="statcircle">
									<span class="glyphicon glyphicon-equalizer glyphicon-large"></span>
								</div>
								<ul class="subdescription_stats">
									<li data-bind="{semrush_rank}" class="es-stat-no"></li>
									<li class="es-stat-title"><?php echo JText::_('COM_JMAP_SEMRUSH_RANK');?></li>
								</ul>
							</div>
							
							<div class="single_stat_container">
								<div class="statcircle">
									<span class="glyphicon glyphicon-tag glyphicon-large"></span>
								</div>
								<ul class="subdescription_stats semrush_popovers">
									<li></li>
									<li data-bind="{semrush_keywords}" class="es-stat-no hasClickPopover"><?php echo JText::_('COM_JMAP_SEMRUSH_KEYWORDS');?></li>
								</ul>
							</div>
							
							<div class="single_stat_container">
								<div class="statcircle">
									<span class="glyphicon glyphicon-globe glyphicon-large"></span>
								</div>
								<ul class="subdescription_stats semrush_popovers">
									<li></li>
									<li data-bind="{semrush_competitors}" class="es-stat-no hasClickPopover"><?php echo JText::_('COM_JMAP_SEMRUSH_COMPETITORS');?></li>
								</ul>
							</div>
							
							<div class="single_stat_container alexachart">
								<ul class="subdescription_stats alexachart">
									<li data-bind="{semrush_graph}" class="es-stat-no fancybox-image"></li>
									<li class="es-stat-title"><?php echo JText::_('COM_JMAP_SEMRUSH_GRAPH');?></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<?php endif; ?>

				<!-- SITEMAP STATS -->
				<div class="panel panel-default">
				    <div class="panel-heading accordion-toggle" data-toggle="collapse" data-parent="#accordion_cpanel" data-target="#jmap_status">
						<h4 class="panel-title">
							<span class="glyphicon glyphicon-info-sign"></span>
							<?php echo JText::_('COM_JMAP_QUICK_STATS');?>
						</h4>
				    </div>
				    <div id="jmap_status"  class="panel-collapse collapse">
						<div class="panel-body">
							<!-- COMPONENT STATUS INDICATOR -->
							<ul class="cpanelinfo nav nav-pills">
							  <li class="active">
							    <a href="javascript:void(0);">
							      <span class="badge pull-right"><?php echo $this->infodata['publishedDataSource']?></span>
							      <?php echo JText::_('COM_JMAP_NUM_PUBLISHED_DATA_SOURCES');?>
							    </a>
							  </li>
							 
							  <li class="active">
							    <a href="javascript:void(0);">
							      <span class="badge pull-right"><?php echo $this->infodata['totalDataSource']?></span>
							      <?php echo JText::_('COM_JMAP_NUM_TOTAL_DATA_SOURCES');?>
							    </a>
							  </li>
							  
							  <li class="active">
							    <a href="javascript:void(0);">
							      <span class="badge pull-right"><?php echo $this->infodata['menuDataSource']?></span>
							      <?php echo JText::_('COM_JMAP_NUM_MENU_DATA_SOURCES');?>
							    </a>
							  </li>
							  
							  <li class="active">
							    <a href="javascript:void(0);">
							      <span class="badge pull-right"><?php echo $this->infodata['userDataSource']?></span>
							      <?php echo JText::_('COM_JMAP_NUM_USER_DATA_SOURCES');?>
							    </a>
							  </li>
							  
							  <li class="active">
							    <a href="javascript:void(0);">
							      <span class="badge pull-right"><?php echo $this->infodata['datasets']?></span>
							      <?php echo JText::_('COM_JMAP_NUM_PUBLISHED_DATASETS');?>
							    </a>
							  </li>
							</ul>
							
							<canvas id="chart_canvas"></canvas>
						</div>
				    </div>
				</div>
				
				<!-- ABOUT-->
				<div class="panel panel-default">
				    <div class="panel-heading accordion-toggle" data-toggle="collapse" data-parent="#accordion_cpanel" data-target="#jmap_about">
						<h4 class="panel-title">
							<span class="glyphicon glyphicon-question-sign"></span>
							<?php echo JText::_('COM_JMAP_ABOUT');?>
						</h4>
				    </div>
				    <div id="jmap_about"  class="panel-collapse collapse">
						<div class="panel-body">
							<div class="single_container">
						 		<label class="label label-warning"><?php echo JText::sprintf('COM_JMAP_VERSION', $this->currentVersion);?></label>
					 		</div>
					 		
					 		<div class="single_container">
						 		<label class="label label-info"><?php echo JText::_('COM_JMAP_AUTHOR_COMPONENT');?></label>
					 		</div>
					 		
					 		<div class="single_container">
						 		<label class="label label-info"><?php echo JText::_('COM_JMAP_SUPPORTLINK');?></label>
					 		</div>
					 		
					 		<div class="single_container">
						 		<label class="label label-info"><?php echo JText::_('COM_JMAP_DEMOLINK');?></label>
					 		</div>
						</div>
				    </div>
				</div>
			</div>
		</div>
		<div class="row no-margin">
			<!-- SEO CONTROL PANEL -->
			<div class="panel-group" id="accordion_cpanel_seo">
				<div class="panel panel-default">
				    <div class="panel-heading accordion-toggle accordion_lightblue noaccordion">
						<h4 class="panel-title">
							<span class="glyphicon glyphicon-dashboard"></span>
							<?php echo JText::_('COM_JMAP_JMAP_INFO_STATUS');?>
						</h4>
				    </div>
				    <div id="jmap_seo"  class="panel-collapse collapse in">
						<div class="panel-body">
							<!-- COMPONENT LINKS -->
							<div class="single_container">
					 			<label class="label label-primary"><?php echo JText::_('COM_JMAP_HTML_LINK')?></label>
					 			<?php if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
					 				<input data-role="sitemap_links" data-html="1" class="sitemap_links" type="text" value="<?php echo JFilterOutput::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap');?>" />
					 			<?php else:?>
					 				<input data-role="sitemap_links_sef" data-html="1" class="sitemap_links" type="text" data-valuenosef="<?php echo JFilterOutput::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap');?>" value="<?php echo $this->livesite . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=sitemap&format=html' . $this->siteItemid));?>"/>
					 			<?php endif;?>
					 		</div>
				 			<div class="single_container xmlcontainer">
						 		<label class="label label-primary hasPopover" data-content="<?php echo JText::_('COM_JMAP_APPEND_LANG_PARAM');?>"><?php echo JText::_('COM_JMAP_XML_LINK')?></label>
						 		<?php if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
						 			<input data-role="sitemap_links" class="sitemap_links" type="text" value="<?php echo JFilterOutput::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=xml');?>" />
						 		<?php else:?>
						 			<input data-role="sitemap_links_sef" class="sitemap_links" type="text" data-valuenosef="<?php echo JFilterOutput::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=xml');?>" value="<?php echo $this->livesite . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=sitemap&format=xml' . $this->siteItemid));?>" />
						 		<?php endif;?>
						 		<?php 
						 			$concatenatePingXmlFormat = "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://www.google.com/webmasters/tools/ping?sitemap=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=xml') . "'>" . JText::_('COM_JMAP_PING_GOOGLE') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://www.bing.com/webmaster/ping.aspx?siteMap=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=xml') . "'>" . JText::_('COM_JMAP_PING_BING') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://blogs.yandex.ru/pings/?status=success&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=xml') . "'>" . JText::_('COM_JMAP_PING_YANDEX') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger glyphicon glyphicon-flash' href='http://ping.baidu.com/ping/RPC2?" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=xml') . "'>" . JText::_('COM_JMAP_PING_BAIDU') . "</a>";
						 		?>
						 		<label class="glyphicon glyphicon-flash hasClickPopover hasTooltip" title="<?php echo JText::_('COM_JMAP_PING_SITEMAP');?>" data-content="<?php echo $concatenatePingXmlFormat;?>"></label>
						 		<label class="glyphicon glyphicon-pencil hasTooltip" title="<?php echo JText::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" data-role="saveentity"></label>
								<?php if($this->componentParams->get('enable_precaching', 0)):?>
									<label class="glyphicon glyphicon-download-alt hasTooltip" title="<?php echo JText::_('COM_JMAP_START_PRECACHING');?>" data-role="startprecaching"></label>
									<span class="label label-danger hasTooltip" title="<?php echo JText::_('COM_JMAP_PRECACHING_STATUS');?>"><?php echo JText::_('COM_JMAP_PRECACHING_NOT_CACHED');?></span>
						 		<?php endif;?>
					 		</div>
					 		
					 		<div class="single_container xmlcontainer">
						 		<label class="label label-primary hasPopover" data-content="<?php echo JText::_('COM_JMAP_APPEND_LANG_PARAM');?>"><?php echo JText::_('COM_JMAP_XML_IMAGES_LINK')?></label>
						 		<?php if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
						 			<input data-role="sitemap_links" class="sitemap_links" type="text" value="<?php echo JFilterOutput::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=images');?>" />
						 		<?php else:?>
						 			<input data-role="sitemap_links_sef" class="sitemap_links" type="text" data-valuenosef="<?php echo JFilterOutput::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=images');?>" value="<?php echo $this->livesite . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=sitemap&format=images' . $this->siteItemid));?>" />
						 		<?php endif;?>
						 		<?php 
						 			$concatenatePingXmlFormat = "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://www.google.com/webmasters/tools/ping?sitemap=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=images') . "'>" . JText::_('COM_JMAP_PING_GOOGLE') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://www.bing.com/webmaster/ping.aspx?siteMap=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=images') . "'>" . JText::_('COM_JMAP_PING_BING') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://blogs.yandex.ru/pings/?status=success&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=images') . "'>" . JText::_('COM_JMAP_PING_YANDEX') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger glyphicon glyphicon-flash' href='http://ping.baidu.com/ping/RPC2?" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=images') . "'>" . JText::_('COM_JMAP_PING_BAIDU') . "</a>";
						 		?>
						 		<label class="glyphicon glyphicon-flash hasClickPopover hasTooltip" title="<?php echo JText::_('COM_JMAP_PING_SITEMAP');?>" data-content="<?php echo $concatenatePingXmlFormat;?>"></label>
						 		<label class="glyphicon glyphicon-pencil hasTooltip" title="<?php echo JText::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" data-role="saveentity"></label>
					 			<?php if($this->componentParams->get('enable_precaching', 0)):?>
									<label class="glyphicon glyphicon-download-alt hasTooltip" title="<?php echo JText::_('COM_JMAP_START_PRECACHING');?>" data-role="startprecaching"></label>
									<span class="label label-danger hasTooltip" title="<?php echo JText::_('COM_JMAP_PRECACHING_STATUS');?>"><?php echo JText::_('COM_JMAP_PRECACHING_NOT_CACHED');?></span>
						 		<?php endif;?>
					 		</div>
					 		
					 		<div class="single_container xmlcontainer">
						 		<label class="label label-primary hasPopover" data-content="<?php echo JText::_('COM_JMAP_APPEND_LANG_PARAM');?>"><?php echo JText::_('COM_JMAP_XML_GNEWS_LINK')?></label>
						 		<?php if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
						 			<input data-role="sitemap_links" class="sitemap_links" type="text" value="<?php echo JFilterOutput::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=gnews');?>" />
						 		<?php else:?>	
						 			<input data-role="sitemap_links_sef" class="sitemap_links" type="text" data-valuenosef="<?php echo JFilterOutput::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=gnews');?>" value="<?php echo $this->livesite . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=sitemap&format=gnews' . $this->siteItemid));?>" />
						 		<?php endif;?>
						 		<?php 
						 			$concatenatePingXmlFormat = "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://www.google.com/webmasters/tools/ping?sitemap=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=gnews') . "'>" . JText::_('COM_JMAP_PING_GOOGLE') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://www.bing.com/webmaster/ping.aspx?siteMap=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=gnews') . "'>" . JText::_('COM_JMAP_PING_BING') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://blogs.yandex.ru/pings/?status=success&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=gnews') . "'>" . JText::_('COM_JMAP_PING_YANDEX') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger glyphicon glyphicon-flash' href='http://ping.baidu.com/ping/RPC2?" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=gnews') . "'>" . JText::_('COM_JMAP_PING_BAIDU') . "</a>";
						 		?>
						 		<label class="glyphicon glyphicon-flash hasClickPopover hasTooltip" title="<?php echo JText::_('COM_JMAP_PING_SITEMAP');?>" data-content="<?php echo $concatenatePingXmlFormat;?>"></label>
						 		<label class="glyphicon glyphicon-pencil hasTooltip" title="<?php echo JText::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" data-role="saveentity"></label>
					 			<?php if($this->componentParams->get('enable_precaching', 0)):?>
									<label class="glyphicon glyphicon-download-alt hasTooltip" title="<?php echo JText::_('COM_JMAP_START_PRECACHING');?>" data-role="startprecaching"></label>
									<span class="label label-danger hasTooltip" title="<?php echo JText::_('COM_JMAP_PRECACHING_STATUS');?>"><?php echo JText::_('COM_JMAP_PRECACHING_NOT_CACHED');?></span>
						 		<?php endif;?>
					 		</div> 
					 		
					 		<div class="single_container xmlcontainer">
						 		<label class="label label-primary hasPopover" data-content="<?php echo JText::_('COM_JMAP_XML_MOBILE_DISCLAIMER');?>"><?php echo JText::_('COM_JMAP_XML_MOBILE_LINK')?></label>
						 		<?php if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
						 			<input data-role="sitemap_links" class="sitemap_links" type="text" value="<?php echo JFilterOutput::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=mobile');?>" />
						 		<?php else:?>
						 			<input data-role="sitemap_links_sef" class="sitemap_links" type="text" data-valuenosef="<?php echo JFilterOutput::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=mobile');?>" value="<?php echo $this->livesite . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=sitemap&format=mobile' . $this->siteItemid));?>" />
						 		<?php endif;?>
						 		<?php 
						 			$concatenatePingXmlFormat = "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://www.google.com/webmasters/tools/ping?sitemap=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=mobile') . "'>" . JText::_('COM_JMAP_PING_GOOGLE') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://www.bing.com/webmaster/ping.aspx?siteMap=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=mobile') . "'>" . JText::_('COM_JMAP_PING_BING') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://blogs.yandex.ru/pings/?status=success&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=mobile') . "'>" . JText::_('COM_JMAP_PING_YANDEX') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger glyphicon glyphicon-flash' href='http://ping.baidu.com/ping/RPC2?" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=mobile') . "'>" . JText::_('COM_JMAP_PING_BAIDU') . "</a>";
						 		?>
						 		<label class="glyphicon glyphicon-flash hasClickPopover hasTooltip" title="<?php echo JText::_('COM_JMAP_PING_SITEMAP');?>" data-content="<?php echo $concatenatePingXmlFormat;?>"></label>
						 		<label class="glyphicon glyphicon-pencil hasTooltip" title="<?php echo JText::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" data-role="saveentity"></label>
					 			<?php if($this->componentParams->get('enable_precaching', 0)):?>
									<label class="glyphicon glyphicon-download-alt hasTooltip" title="<?php echo JText::_('COM_JMAP_START_PRECACHING');?>" data-role="startprecaching"></label>
									<span class="label label-danger hasTooltip" title="<?php echo JText::_('COM_JMAP_PRECACHING_STATUS');?>"><?php echo JText::_('COM_JMAP_PRECACHING_NOT_CACHED');?></span>
						 		<?php endif;?>
					 		</div> 
					 		
					 		<div class="single_container xmlcontainer">
						 		<label class="label label-primary hasPopover" data-content="<?php echo JText::_('COM_JMAP_APPEND_LANG_PARAM');?>"><?php echo JText::_('COM_JMAP_XML_VIDEOS_LINK')?></label>
						 		<?php if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
						 			<input data-role="sitemap_links" class="sitemap_links" type="text" value="<?php echo JFilterOutput::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=videos');?>" />
						 		<?php else:?>
						 			<input data-role="sitemap_links_sef" class="sitemap_links" type="text" data-valuenosef="<?php echo JFilterOutput::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=videos');?>" value="<?php echo $this->livesite . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=sitemap&format=videos' . $this->siteItemid));?>" />
						 		<?php endif;?>
						 		<?php 
						 			$concatenatePingXmlFormat = "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://www.google.com/webmasters/tools/ping?sitemap=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=videos') . "'>" . JText::_('COM_JMAP_PING_GOOGLE') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://www.bing.com/webmaster/ping.aspx?siteMap=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=videos') . "'>" . JText::_('COM_JMAP_PING_BING') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://blogs.yandex.ru/pings/?status=success&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=videos') . "'>" . JText::_('COM_JMAP_PING_YANDEX') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger glyphicon glyphicon-flash' href='http://ping.baidu.com/ping/RPC2?" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=videos') . "'>" . JText::_('COM_JMAP_PING_BAIDU') . "</a>";
						 		?>
						 		<label class="glyphicon glyphicon-flash hasClickPopover hasTooltip" title="<?php echo JText::_('COM_JMAP_PING_SITEMAP');?>" data-content="<?php echo $concatenatePingXmlFormat;?>"></label>
						 		<label class="glyphicon glyphicon-pencil hasTooltip" title="<?php echo JText::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" data-role="saveentity"></label>
					 			<?php if($this->componentParams->get('enable_precaching', 0)):?>
									<label class="glyphicon glyphicon-download-alt hasTooltip" title="<?php echo JText::_('COM_JMAP_START_PRECACHING');?>" data-role="startprecaching"></label>
									<span class="label label-danger hasTooltip" title="<?php echo JText::_('COM_JMAP_PRECACHING_STATUS');?>"><?php echo JText::_('COM_JMAP_PRECACHING_NOT_CACHED');?></span>
						 		<?php endif;?>
					 		</div>

							<div class="single_container xmlcontainer">
						 		<label class="label label-primary hasPopover" data-content="<?php echo JText::_('COM_JMAP_XML_HREFLANG_DISCLAIMER');?>"><?php echo JText::_('COM_JMAP_XML_HREFLANG_LINK')?></label>
						 		<?php if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
						 			<input data-role="sitemap_links" data-language="1" class="sitemap_links" type="text" value="<?php echo JFilterOutput::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=hreflang');?>" />
						 		<?php else:?>
						 			<input data-role="sitemap_links_sef" data-language="1" class="sitemap_links" type="text" data-valuenosef="<?php echo JFilterOutput::ampReplace($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=hreflang');?>" value="<?php echo $this->livesite . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=sitemap&format=hreflang' . $this->siteItemid));?>" />
						 		<?php endif;?>
						 		<?php 
						 			$concatenatePingXmlFormat = "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://www.google.com/webmasters/tools/ping?sitemap=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=hreflang') . "'>" . JText::_('COM_JMAP_PING_GOOGLE') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://www.bing.com/webmaster/ping.aspx?siteMap=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=hreflang') . "'>" . JText::_('COM_JMAP_PING_BING') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://blogs.yandex.ru/pings/?status=success&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=hreflang') . "'>" . JText::_('COM_JMAP_PING_YANDEX') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger glyphicon glyphicon-flash' href='http://ping.baidu.com/ping/RPC2?" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=sitemap&format=hreflang') . "'>" . JText::_('COM_JMAP_PING_BAIDU') . "</a>";
						 		?>
						 		<label class="glyphicon glyphicon-flash hasClickPopover hasTooltip" title="<?php echo JText::_('COM_JMAP_PING_SITEMAP');?>" data-content="<?php echo $concatenatePingXmlFormat;?>"></label>
						 		<label class="glyphicon glyphicon-pencil hasTooltip" title="<?php echo JText::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" data-role="saveentity"></label>
					 			<?php if($this->componentParams->get('enable_precaching', 0)):?>
									<label class="glyphicon glyphicon-download-alt hasTooltip" title="<?php echo JText::_('COM_JMAP_START_PRECACHING');?>" data-role="startprecaching"></label>
									<span class="label label-danger hasTooltip" title="<?php echo JText::_('COM_JMAP_PRECACHING_STATUS');?>"><?php echo JText::_('COM_JMAP_PRECACHING_NOT_CACHED');?></span>
						 		<?php endif;?>
					 		</div>
					 		
					 		<?php if($this->componentParams->get('geositemap_enabled', 0) && trim($this->componentParams->get('geositemap_address', null))):?>
					 		<div class="single_container xmlcontainer">
						 		<label class="label label-primary hasPopover" data-content="<?php echo JText::_('COM_JMAP_XML_GEOSITEMAP_DISCLAIMER');?>"><?php echo JText::_('COM_JMAP_XML_GEOSITEMAP_LINK')?></label>
						 		<?php if(!$this->showSefLinks || !$this->joomlaSefLinks):?>
						 			<input data-role="sitemap_links" data-language="1" class="sitemap_links" type="text" value="<?php echo JFilterOutput::ampReplace($this->livesite . '/index.php?option=com_jmap&view=geositemap&format=xml');?>" />
						 		<?php else:?>
						 			<input data-role="sitemap_links_sef" data-language="1" class="sitemap_links" type="text" data-valuenosef="<?php echo JFilterOutput::ampReplace($this->livesite . '/index.php?option=com_jmap&view=geositemap&format=xml');?>" value="<?php echo $this->livesite . preg_replace('/(\/[A-Za-z0-9_-~]*)*\/administrator\//i', '/', $this->siteRouter->build('index.php?option=com_jmap&view=geositemap&format=xml' . $this->siteItemid));?>" />
						 		<?php endif;?>
						 		<?php 
						 			$concatenatePingXmlFormat = "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://www.google.com/webmasters/tools/ping?sitemap=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=geositemap&format=xml') . "'>" . JText::_('COM_JMAP_PING_GOOGLE') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://www.bing.com/webmaster/ping.aspx?siteMap=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=geositemap&format=xml') . "'>" . JText::_('COM_JMAP_PING_BING') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' class='pinger glyphicon glyphicon-flash' href='http://blogs.yandex.ru/pings/?status=success&url=" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=geositemap&format=xml') . "'>" . JText::_('COM_JMAP_PING_YANDEX') . "</a>";
						 			$concatenatePingXmlFormat .= "<a data-role='pinger' data-type='rpc' class='pinger glyphicon glyphicon-flash' href='http://ping.baidu.com/ping/RPC2?" . rawurlencode($this->livesite . '/index.php?option=com_jmap&view=geositemap&format=xml') . "'>" . JText::_('COM_JMAP_PING_BAIDU') . "</a>";
						 		?>
						 		<label class="glyphicon glyphicon-flash hasClickPopover hasTooltip" title="<?php echo JText::_('COM_JMAP_PING_SITEMAP');?>" data-content="<?php echo $concatenatePingXmlFormat;?>"></label>
						 		<label class="glyphicon glyphicon-pencil hasTooltip" title="<?php echo JText::_('COM_JMAP_ROBOTS_SITEMAP_ENTRY');?>" data-role="saveentity"></label>
						 		<a class="glyphicon glyphicon-map-marker hasTooltip fancybox" title="<?php echo JText::_('COM_JMAP_OPEN_GEOLOCATION_MAP');?>" href="#gmap" data-role="opengmap"></a><div id="gmap"></div>
					 		</div>
					 		<?php endif;?>

					 		<!-- LANGUAGE SELECT LIST -->
					 		<?php if($this->lists['languages']):?>
					 		<div class="single_container language">
						 		<label class="label label-primary hasPopover" data-content="<?php echo JText::_('COM_JMAP_CHOOSE_LANGUAGE');?>"><?php echo JText::_('COM_JMAP_CHOOSE_LANG')?></label>
						 		<?php echo $this->lists['languages'];?>
					 		</div>
					 		<?php endif;?>
					 		
					 		<!-- DATASETS SELECT LIST -->
					 		<?php if($this->lists['datasets_filters']):?>
					 		<div class="single_container language">
						 		<label class="label label-primary hasPopover" data-content="<?php echo JText::_('COM_JMAP_CHOOSE_DATASET_DESC');?>"><?php echo JText::_('COM_JMAP_CHOOSE_DATASET')?></label>
						 		<?php echo $this->lists['datasets_filters'];?>
					 		</div>
					 		<?php endif;?>
					 		
					 		<!-- MENU FILTERS SELECT LIST -->
					 		<?php if($this->lists['menu_datasource_filters']):?>
					 		<div class="single_container language">
						 		<label class="label label-primary hasPopover" data-content="<?php echo JText::_('COM_JMAP_CHOOSE_MENU_DESC');?>"><?php echo JText::_('COM_JMAP_CHOOSE_MENU')?></label>
						 		<?php echo $this->lists['menu_datasource_filters'] ;?>
					 		</div>
					 		<?php endif;?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<form name="adminForm" id="adminForm" action="index.php">
		<input type="hidden" name="option" value="<?php echo $this->option;?>"/>
		<input type="hidden" name="task" value=""/>
	</form>
</div>