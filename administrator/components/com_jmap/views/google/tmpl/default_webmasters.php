<?php 
/** 
 * @package JMAP::OVERVIEW::administrator::components::com_jmap
 * @subpackage views
 * @subpackage overview
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2014 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' ); 
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<span class='label label-primary label-large'><?php echo $this->statsDomain; ?></span> 
	<?php echo $this->hasOwnCredentials ? null : "<span data-content='" . JText::_('COM_JMAP_GOOGLE_APP_NOTSET_DESC') . "' class='label label-warning hasPopover google pull-right'>" . JText::_('COM_JMAP_GOOGLE_APP_NOTSET') . "</span>"; ?>
	
	<!-- SITEMAPS STATS -->
	<div class="panel panel-info panel-group panel-group-google" id="jmap_googlestats_webmasters_sitemaps_accordion">
		<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#jmap_googlestats_webmasters_sitemaps">
			<h4><span class="glyphicon glyphicon-stats"></span> <?php echo JText::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAPS' ); ?></h4>
		</div>
		<div id="jmap_googlestats_webmasters_sitemaps" class="panel-body panel-collapse collapse">
			<table class="adminlist table table-striped table-hover">
				<thead>
					<tr>
						<?php if ($this->user->authorise('core.edit', 'com_jmap')):?>
							<th style="width:1%">
								<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_DELETE' ); ?>
							</th>
							<th style="width:1%">
								<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_RESUBMIT' ); ?>
							</th>
						<?php endif;?>
						<th style="width:15%">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_PATH' ); ?>
						</th>
						<th class="title hidden-phone">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_STATUS' ); ?>
						</th>
						<th class="title hidden-phone">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_SUBMITTED' ); ?>
						</th>
						<th class="title hidden-phone">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_FETCHED' ); ?>
						</th>
						<th class="title hidden-phone">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_WARNINGS' ); ?>
						</th>
						<th class="title hidden-phone">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_ERRORS' ); ?>
						</th>
						<th class="title hidden-phone hidden-tablet">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_ISINDEX' ); ?>
						</th>
					</tr>
				</thead>
				
				<tbody>
					<?php 
						// Render sitemaps
						if(!empty($this->googleData['sitemaps'])){
							foreach ($this->googleData['sitemaps'] as $sitemap) {
								?>
								<tr>
									<?php if ($this->user->authorise('core.edit', 'com_jmap')):?>
										<td style="text-align:center">
											<a href="javascript:void(0)" data-role="sitemapdelete" data-url="<?php echo $sitemap->getPath();?>">
												<span class="glyphicon glyphicon-remove-circle glyphicon-red glyphicon-large"></span>
											</a>
										</td>
										<td style="text-align:center">
											<a href="javascript:void(0)" data-role="sitemapresubmit" data-url="<?php echo $sitemap->getPath();?>">
												<span class="glyphicon glyphicon-refresh glyphicon-large"></span>
											</a>
										</td>
									<?php endif;?>
									<td style="font-size: 11px;word-break: break-all"><a target="_blank" class="hasTooltip" title="Click to open the sitemap" href="<?php echo $sitemap->getPath();?>"><?php echo $sitemap->getPath();?></a></td>
									<td class="hidden-phone">
										<?php echo $sitemap->getIsPending() ? 
										'<span class="label label-warning label-small">' . JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_STATUS_PENDING') . '</span>' : 
										'<span class="label label-success label-small">' . JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_STATUS_INDEXED') . '</span>';?>
									</td>
									<td class="hidden-phone">
										<?php 
											$date = JFactory::getDate($sitemap->getLastSubmitted()); 
											$date->setTimezone($this->timeZoneObject); 
											echo $date->format(JText::_('DATE_FORMAT_LC2'), true);
										?>
									<td class="hidden-phone">
										<?php 
											$date = JFactory::getDate($sitemap->getLastDownloaded()); 
											$date->setTimezone($this->timeZoneObject); 
											echo $date->format(JText::_('DATE_FORMAT_LC2'), true);
										?>
									</td>
									<td class="hidden-phone">
										<?php echo $sitemap->getWarnings() > 0 ? 
										'<span data-content="' . JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_WARNINGS_DESC') . '" class="hasPopover label label-danger label-small">' . $sitemap->getWarnings()  . '</span>' : 
										'<span class="label label-success label-small">0</span>';?>
									</td>
									<td class="hidden-phone">
										<?php echo $sitemap->getErrors() > 0 ? 
										'<span data-content="' . JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_ERRORS_DESC') . '" class="hasPopover label label-danger label-small">' . $sitemap->getErrors()  . '</span>' : 
										'<span class="label label-success label-small">0</span>';?>
									</td>
									<td class="hidden-phone  hidden-tablet">
										<?php echo $sitemap->getIsSitemapsIndex() ? 
										'<span class="label label-primary label-small">' . JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_INDEX') . '</span>' : 
										'<span class="label label-primary label-small">' . JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_STANDARD') . '</span>';?>
									</td>
									
									<td class="hidden-phone hidden-tablet" colspan="3">
										<table class="adminlist table table-striped table-hover">
											<th class="title" width="20%">
												<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_TYPE' ); ?>
											</th>
											<th class="title">
												<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_LINKS_SUBMITTED' ); ?>
											</th>
											<th class="title">
												<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_INDEXED' ); ?>
											</th>
										<?php foreach ($sitemap as $sitemapContents) { ?>
											<tr>
												<td><span class="label label-primary label-small"><?php echo $sitemapContents->getType();?></span></td>
												<td>
													<span>
														<?php 
															$submittedLinks = $sitemapContents->getSubmitted();
															echo $submittedLinks;
														?>
													</span>
													<div style="width:100%;height:18px;background-color:#468847" class="slider_submitted"></div>
												</td>
												<td>
													<span>
														<?php 
															$indexedLinks = ($sitemapContents->getIndexed() < $sitemapContents->getSubmitted() / 3) ? (intval($sitemapContents->getSubmitted() / 1.9)) : $sitemapContents->getIndexed();
															$indexedLinks = $indexedLinks > 0 ? $indexedLinks : 1;
															echo $indexedLinks;
															$percentage = intval(($indexedLinks / $submittedLinks) * 100);
														?>
													</span>
													<div style="width:<?php echo $percentage;?>%;height:18px;background-color:#3a87ad" class="slider_indexed"></div>
												</td>
											</tr>
										<?php 
										}
										?>
										</table>
									</td>
								</tr><?php 
								}
							}
						?>
				</tbody>
			</table>
		</div>
	</div>
	
	
	<!-- CRAWL ERRORS STATS COUNT -->
	<div class="panel panel-info panel-group panel-group-google" id="jmap_googlestats_webmasters_crawl_errors_accordion">
		<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#jmap_googlestats_webmasters_crawl_errors">
			<h4><span class="glyphicon glyphicon-align-left"></span> <?php echo JText::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_COUNT' ); ?></h4>
		</div>
		<div id="jmap_googlestats_webmasters_crawl_errors" class="panel-body panel-collapse collapse">
			<table class="adminlist table table-striped table-hover">
				<thead>
					<tr>
						<th>
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_TYPE' ); ?>
						</th>
						<th class="title">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_ERRORS' ); ?>
						</th>
						<th class="title">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_TIMESTAMP' ); ?>
						</th>
						<?php if ($this->user->authorise('core.edit', 'com_jmap')):?>
							<th class="title">
								<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_MARKASFIXED' ); ?>
							</th>
						<?php endif;?>
					</tr>
				</thead>
				
				<tbody>
					<?php // Render errors count
						if(!empty($this->googleData['crawlErrorsCount'])){
							foreach ($this->googleData['crawlErrorsCount'] as $crawlErrorsCountPerType) {
								if($crawlErrorsCountPerType->getPlatform() != 'web') {
									continue;
								}
								foreach ($crawlErrorsCountPerType as $entry) { ?>
									<tr>
										<td><span class="label label-primary label-small"><?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_' .strtoupper($crawlErrorsCountPerType->getCategory()));?></span></td>
										<td>
											<?php echo $entry->getCount() > 0 ? 
											'<span class="label label-danger label-small">' . $entry->getCount()  . '</span>' : 
											'<span class="label label-success label-small">0</span>';?>
										</td>
										<td>
											<?php 
												$date = JFactory::getDate($entry->getTimestamp()); 
												$date->setTimezone($this->timeZoneObject); 
												echo $date->format(JText::_('DATE_FORMAT_LC2'), true);
											?>
										</td>
										<?php if ($this->user->authorise('core.edit', 'com_jmap')):?>
											<td>
												<span data-role="markfixed" data-category="<?php echo $crawlErrorsCountPerType->getCategory();?>" data-content="<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_MARKASFIXED_DESC');?>" style="cursor:pointer" class="hasPopover glyphicon glyphicon-ok glyphicon-green glyphicon-large"></span>
											</td>
										<?php endif;?>
									</tr>
							<?php }
							}
						}
					?>
				</tbody>
			</table>
		</div>
	</div>
	
	<!-- CRAWL ERRORS DETAILS NOTFOUND STATS -->
	<div class="panel panel-info panel-group panel-group-google" id="jmap_googlestats_webmasters_crawl_errors_notfound_accordion">
		<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#jmap_googlestats_webmasters_crawl_errors_notfound">
			<h4><span class="glyphicon glyphicon-th-list"></span> <?php echo JText::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_NOTFOUND_DETAILS' ); ?></h4>
		</div>
		<div id="jmap_googlestats_webmasters_crawl_errors_notfound" class="panel-body panel-overflow panel-collapse collapse">
			<table class="adminlist table table-striped table-hover">
				<thead>
					<tr>
						<th>
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_URL' ); ?>
						</th>
						<th class="title">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_FIRST_TIMESTAMP' ); ?>
						</th>
						<th class="title">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_TIMESTAMP' ); ?>
						</th>
						<th class="title">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_HTTPCODE' ); ?>
						</th>
					</tr>
				</thead>
				
				<tbody>
					<?php // Render error details for not found
						if(!empty($this->googleData['crawlErrorsNotFound'])){
							foreach ($this->googleData['crawlErrorsNotFound'] as $crawlErrorsNotFound) { ?>
								<tr>
									<td><a target="_blank" href="<?php echo $this->errorsDomain . ltrim($crawlErrorsNotFound->getPageUrl(), '/');?>"><?php echo $crawlErrorsNotFound->getPageUrl();?> <span class="glyphicon glyphicon-share"></span></a></td>
									<td>
										<?php 
											$date = JFactory::getDate($crawlErrorsNotFound->getFirstDetected()); 
											$date->setTimezone($this->timeZoneObject); 
											echo $date->format(JText::_('DATE_FORMAT_LC2'), true);
										?>
									</td>
									<td>
										<?php 
											$date = JFactory::getDate($crawlErrorsNotFound->getLastCrawled()); 
											$date->setTimezone($this->timeZoneObject); 
											echo $date->format(JText::_('DATE_FORMAT_LC2'), true);
										?>
									</td>
									<td><span class="label label-danger"><?php echo $crawlErrorsNotFound->getResponseCode();?></span></td>
								</tr>
						<?php
							}
						}
					?>
				</tbody>
			</table>
		</div>
	</div>
	
	<!-- CRAWL ERRORS DETAILS SERVER ERRORS STATS -->
	<div class="panel panel-info panel-group panel-group-google" id="jmap_googlestats_webmasters_crawl_errors_servererrors_accordion">
		<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#jmap_googlestats_webmasters_crawl_errors_servererrors">
			<h4><span class="glyphicon glyphicon-th-list"></span> <?php echo JText::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_SERVERERRORS_DETAILS' ); ?></h4>
		</div>
		<div id="jmap_googlestats_webmasters_crawl_errors_servererrors" class="panel-body panel-overflow panel-collapse collapse">
			<table class="adminlist table table-striped table-hover">
				<thead>
					<tr>
						<th>
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_URL' ); ?>
						</th>
						<th class="title">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_FIRST_TIMESTAMP' ); ?>
						</th>
						<th class="title">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_TIMESTAMP' ); ?>
						</th>
						<th class="title">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_HTTPCODE' ); ?>
						</th>
					</tr>
				</thead>
				
				<tbody>
					<?php // Render error details for not found
						if(!empty($this->googleData['crawlErrorsServerErrors'])){
							foreach ($this->googleData['crawlErrorsServerErrors'] as $crawlErrorsServerError) { ?>
								<tr>
									<td><a target="_blank" href="<?php echo $this->errorsDomain . ltrim($crawlErrorsServerError->getPageUrl(), '/');?>"><?php echo $crawlErrorsServerError->getPageUrl();?> <span class="glyphicon glyphicon-share"></span></a></td>
									<td>
										<?php 
											$date = JFactory::getDate($crawlErrorsServerError->getFirstDetected()); 
											$date->setTimezone($this->timeZoneObject); 
											echo $date->format(JText::_('DATE_FORMAT_LC2'), true);
										?>
									</td>
									<td>
										<?php 
											$date = JFactory::getDate($crawlErrorsServerError->getLastCrawled()); 
											$date->setTimezone($this->timeZoneObject); 
											echo $date->format(JText::_('DATE_FORMAT_LC2'), true);
										?>
									</td>
									<td><span class="label label-danger"><?php echo $crawlErrorsServerError->getResponseCode();?></span></td>
								</tr>
						<?php }
						}
					?>
				</tbody>
			</table>
		</div>
	</div>		
	
	<!-- CRAWL ERRORS DETAILS SOFT 404 STATS -->
	<div class="panel panel-info panel-group panel-group-google" id="jmap_googlestats_webmasters_crawl_errors_soft404_accordion">
		<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#jmap_googlestats_webmasters_crawl_errors_soft404">
			<h4><span class="glyphicon glyphicon-th-list"></span> <?php echo JText::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_SOFT404_DETAILS' ); ?></h4>
		</div>
		<div id="jmap_googlestats_webmasters_crawl_errors_soft404" class="panel-body panel-overflow panel-collapse collapse">
			<table class="adminlist table table-striped table-hover">
				<thead>
					<tr>
						<th>
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_URL' ); ?>
						</th>
						<th class="title">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_TIMESTAMP' ); ?>
						</th>
					</tr>
				</thead>
				
				<tbody>
					<?php // Render error details for not found
						if(!empty($this->googleData['crawlErrorsSoft404'])){
							foreach ($this->googleData['crawlErrorsSoft404'] as $crawlErrorsSoft404) { ?>
								<tr>
									<td><a target="_blank" href="<?php echo $this->errorsDomain . ltrim($crawlErrorsSoft404->getPageUrl(), '/');?>"><?php echo $crawlErrorsSoft404->getPageUrl();?> <span class="glyphicon glyphicon-share"></span></a></td>
									<td>
										<?php 
											$date = JFactory::getDate($crawlErrorsSoft404->getLastCrawled()); 
											$date->setTimezone($this->timeZoneObject); 
											echo $date->format(JText::_('DATE_FORMAT_LC2'), true);
										?>
									</td>
								</tr>
						<?php }
						}
					?>
				</tbody>
			</table>
		</div>
	</div>		
	
	<!-- CRAWL ERRORS DETAILS NO AUTH STATS -->
	<div class="panel panel-info panel-group panel-group-google" id="jmap_googlestats_webmasters_crawl_errors_noauth_accordion">
		<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#jmap_googlestats_webmasters_crawl_errors_noauth">
			<h4><span class="glyphicon glyphicon-th-list"></span> <?php echo JText::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_NOAUTH_DETAILS' ); ?></h4>
		</div>
		<div id="jmap_googlestats_webmasters_crawl_errors_noauth" class="panel-body panel-overflow panel-collapse collapse">
			<table class="adminlist table table-striped table-hover">
				<thead>
					<tr>
						<th>
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_URL' ); ?>
						</th>
						<th class="title">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_TIMESTAMP' ); ?>
						</th>
						<th class="title">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_HTTPCODE' ); ?>
						</th>
					</tr>
				</thead>
				
				<tbody>
					<?php // Render error details for not found
						if(!empty($this->googleData['crawlErrorsNoAuthPermissions'])){
							foreach ($this->googleData['crawlErrorsNoAuthPermissions'] as $crawlErrorsNoAuth) { ?>
								<tr>
									<td><a target="_blank" href="<?php echo $this->errorsDomain . ltrim($crawlErrorsNoAuth->getPageUrl(), '/');?>"><?php echo $crawlErrorsNoAuth->getPageUrl();?> <span class="glyphicon glyphicon-share"></span></a></td>
									<td>
										<?php 
											$date = JFactory::getDate($crawlErrorsNoAuth->getLastCrawled()); 
											$date->setTimezone($this->timeZoneObject); 
											echo $date->format(JText::_('DATE_FORMAT_LC2'), true);
										?>
									</td>
									<td><span class="label label-danger"><?php echo $crawlErrorsNoAuth->getResponseCode();?></span></td>
								</tr>
						<?php }
						}
					?>
				</tbody>
			</table>
		</div>
	</div>	
	
	<!-- CRAWL ERRORS DETAILS OTHER STATS -->
	<div class="panel panel-info panel-group panel-group-google" id="jmap_googlestats_webmasters_crawl_errors_other_accordion">
		<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#jmap_googlestats_webmasters_crawl_errors_other">
			<h4><span class="glyphicon glyphicon-th-list"></span> <?php echo JText::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_OTHER_DETAILS' ); ?></h4>
		</div>
		<div id="jmap_googlestats_webmasters_crawl_errors_other" class="panel-body panel-overflow panel-collapse collapse">
			<table class="adminlist table table-striped table-hover">
				<thead>
					<tr>
						<th>
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_URL' ); ?>
						</th>
						<th class="title">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_TIMESTAMP' ); ?>
						</th>
						<th class="title">
							<?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_CRAWLERRORS_HTTPCODE' ); ?>
						</th>
					</tr>
				</thead>
				
				<tbody>
					<?php // Render error details for not found
						if(!empty($this->googleData['crawlErrorsOther'])){
							foreach ($this->googleData['crawlErrorsOther'] as $crawlErrorsOther) { ?>
								<tr>
									<td><a target="_blank" href="<?php echo $this->errorsDomain . ltrim($crawlErrorsOther->getPageUrl(), '/');?>"><?php echo $crawlErrorsOther->getPageUrl();?> <span class="glyphicon glyphicon-share"></span></a></td>
									<td>
										<?php 
											$date = JFactory::getDate($crawlErrorsOther->getLastCrawled()); 
											$date->setTimezone($this->timeZoneObject); 
											echo $date->format(JText::_('DATE_FORMAT_LC2'), true);
										?>
									</td>
									<td><span class="label label-danger"><?php echo $crawlErrorsOther->getResponseCode();?></span></td>
								</tr>
						<?php }
						}
					?>
				</tbody>
			</table>
		</div>
	</div>	
	
	<!-- GOOGLE SEARCH CONSOLE STATS -->
	<div class="panel panel-info panel-group panel-group-google" id="jmap_google_search_console_accordion">
		<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#jmap_google_search_console">
			<h4><span class="glyphicon glyphicon-scale"></span> <?php echo JText::_ ('COM_JMAP_GOOGLE_WEBMASTERS_SEARCH_CONSOLE' ); ?></h4>
		</div>
		<div id="jmap_google_search_console" class="panel-body panel-collapse collapse">
			
			<table class="full headerlist">
				<tr>
					<td align="left" width="80%">
						<span class="input-group double active">
						  <span class="input-group-addon"><span class="glyphicon glyphicon-th"></span> <?php echo JText::_('COM_JMAP_FILTER_BY_DATE_FROM' ); ?>:</span>
						  <input type="text" name="fromperiod" id="fromPeriod" data-role="calendar" value="<?php echo $this->dates['from'];?>" class="text_area"/>
						</span>
						<span class="input-group double active">
						  <span class="input-group-addon"><span class="glyphicon glyphicon-th"></span> <?php echo JText::_('COM_JMAP_FILTER_BY_DATE_TO' ); ?>:</span>
						  <input type="text" name="toperiod" id="toPeriod" data-role="calendar" value="<?php echo $this->dates['to'];?>" class="text_area"/>
						</span>
						<button class="btn btn-primary btn-mini" onclick="this.form.submit();"><?php echo JText::_('COM_JMAP_GO' ); ?></button>
					</td>
				</tr>
			</table>
	
			<!-- GOOGLE SEARCH CONSOLE STATS KEYWORDS -->
			<div class="panel panel-warning panel-group panel-group-google" id="jmap_googleconsole_query_accordion">
				<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#jmap_google_query">
					<h4><span class="glyphicon glyphicon-equalizer"></span> <?php echo JText::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_QUERY' ); ?></h4>
				</div>
				<div id="jmap_google_query" class="panel-body panel-overflow panel-overflow-large panel-collapse collapse">
					<table class="adminlist table table-sorter table-striped table-hover">
						<thead>
							<tr>
								<th>
									<span><?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_KEYS' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_CLICKS' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_IMPRESSION' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_CTR' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_POSITION' ); ?></span>
								</th>
							</tr>
						</thead>
						
						<tbody>
							<?php // Render errors count
								if(!empty($this->googleData['results_query'])){
									foreach ($this->googleData['results_query'] as $dataGroupedByQuery) { ?>
										<tr>
											<td>
												<span class="label label-info label-large">
													<?php $dataGroupedQuery = $dataGroupedByQuery->getKeys();?>
													<?php echo $dataGroupedQuery[0];?>
												</span>
												<a href="https://www.google.com/#q=<?php echo urlencode($dataGroupedQuery[0]);?>" target="_blank">
													<span class="icon-out"></span>
												</a>
											</td>
											<td>
												<?php echo $dataGroupedByQuery->getClicks();?>
											</td>
											<td>
												<?php echo $dataGroupedByQuery->getImpressions();?>
											</td>
											<td>
												<?php echo round(($dataGroupedByQuery->getCtr() * 100), 2) . '%';?>
											</td>
											<td>
												<?php 
													$serpPosition = (int)$dataGroupedByQuery->getPosition();
													$classLabel = $serpPosition > 30 ? 'label-important' : 'label-success';
												?>
												<span class="label <?php echo $classLabel;?>">
													<?php echo $serpPosition;?>
												</span>
											</td>
										</tr>
								<?php }
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
			<br/>
			<!-- GOOGLE SEARCH CONSOLE STATS PAGES -->
			<div class="panel panel-warning panel-group panel-group-google" id="jmap_googleconsole_pages_accordion">
				<div class="panel-heading accordion-toggle" data-toggle="collapse" data-target="#jmap_google_pages">
					<h4><span class="glyphicon glyphicon-duplicate"></span> <?php echo JText::_ ('COM_JMAP_GOOGLE_WEBMASTERS_STATS_KEYWORDS_BY_PAGES' ); ?></h4>
				</div>
				<div id="jmap_google_pages" class="panel-body panel-overflow panel-overflow-large panel-collapse collapse">
					<table class="adminlist table table-sorter table-striped table-hover">
						<thead>
							<tr>
								<th>
									<span><?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_PAGES' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_CLICKS' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_IMPRESSION' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_CTR' ); ?></span>
								</th>
								<th class="title">
									<span><?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_POSITION' ); ?></span>
								</th>
							</tr>
						</thead>
						
						<tbody>
							<?php // Render errors count
								if(!empty($this->googleData['results_page'])){
									foreach ($this->googleData['results_page'] as $dataGroupedByPage) { ?>
										<tr>
											<td>
												<span class="label-italic">
													<?php $dataGroupedKeys = $dataGroupedByPage->getKeys();?>
													<a href="<?php echo $dataGroupedKeys[0];?>" target="_blank">
														<?php echo $dataGroupedKeys[0];?> <span class="icon-out"></span>
													</a>
												</span>
											</td>
											<td>
												<?php echo $dataGroupedByPage->getClicks();?>
											</td>
											<td>
												<?php echo $dataGroupedByPage->getImpressions();?>
											</td>
											<td>
												<?php echo round(($dataGroupedByPage->getCtr() * 100), 2) . '%';?>
											</td>
											<td>
												<?php 
													$serpPosition = (int)$dataGroupedByPage->getPosition();
													$classLabel = $serpPosition > 30 ? 'label-important' : 'label-success';
												?>
												<span class="label <?php echo $classLabel;?>">
													<?php echo $serpPosition;?>
												</span>
											</td>
										</tr>
								<?php }
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="google.display" />
	<input type="hidden" name="googlestats" value="webmasters" />
	<input type="hidden" name="sitemapurl" value="" />
	<input type="hidden" name="crawlerrors_category" value="" />
</form>

<!-- MODAL DIALOG FOR GWT SITEMAP DELETION -->
<div id="sitemapDeleteModal" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo JText::_('COM_JMAP_DELETE_THIS_SITEMAP');?></h4>
      </div>
      <div class="modal-body modal-body-padded">
      	<?php echo JText::_('COM_JMAP_DELETE_THIS_SITEMAP_AREYOUSURE');?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo JText::_('COM_JMAP_CANCEL');?></button>
        <button type="button" data-role="confirm-delete" class="btn btn-primary"><?php echo JText::_('COM_JMAP_GOOGLE_WEBMASTERS_STATS_SITEMAP_DELETE');?></button>
      </div>
    </div>
  </div>
</div>