<?php
/** 
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage views
 * @subpackage sitemap
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
echo "<?xml version='1.0' encoding='UTF-8'?>" . PHP_EOL;
if($this->xslt) {
	echo "<?xml-stylesheet type='text/xsl' href='" . JURI::root() . "components/com_jmap/xslt/xml-rss-feed.xsl'?>" . PHP_EOL;
}
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title><?php echo htmlspecialchars($this->cparams->get('rss_channel_name', $this->joomlaConfig->get('sitename', null)), ENT_COMPAT, 'UTF-8');?></title>
<link><?php echo JUri::base();?></link>
<description><![CDATA[<?php echo $this->cparams->get('rss_channel_description', null);?>]]></description>
<language><?php echo $this->rssLang;?></language>
<webMaster><?php echo htmlspecialchars($this->cparams->get('rss_webmaster_email', $this->joomlaConfig->get('mailfrom', null)) . ' ' .
									   '(' . $this->cparams->get('rss_webmaster_name', $this->joomlaConfig->get('fromname', null)) . ')', ENT_COMPAT, 'UTF-8');?></webMaster>
<pubDate><?php $dateObj = new JDate(); $dateObj->setTimezone(new DateTimeZone($this->globalConfig->get('offset')));echo htmlspecialchars($dateObj->toRFC822(true), ENT_COMPAT, 'UTF-8');?></pubDate>
<generator>JSitemap Pro</generator>
<atom:link rel="self" type="application/rss+xml" href="<?php $current = JUri::getInstance(); echo htmlspecialchars($current->toString(array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query')), ENT_COMPAT, 'UTF-8');?>"/>
<?php if($channelImage = $this->cparams->get('rss_channel_image', null)):?>
<image>
	<url><?php echo $this->liveSite . '/' . $channelImage;?></url>
	<link><?php echo str_replace(' ', '%20', JUri::base()); ?></link>
	<title><?php echo htmlspecialchars($this->cparams->get('rss_channel_name', $this->joomlaConfig->get('sitename', null)), ENT_COMPAT, 'UTF-8');?></title>
</image>
<?php
endif;
foreach ( $this->data as $source ) {	
	// Strategy pattern source type template visualization
	if ($source->type) {
		$this->source = $source;
		$this->sourceparams = $source->params;
		$this->asCategoryTitleField = $this->findAsCategoryTitleField($source);
		if($this->sourceparams->get('rssinclude', 1)) {
			$subTemplateName = $this->_layout . '_rss_' . $source->type . '.php';
			if (file_exists ( JPATH_COMPONENT_SITE . '/views/sitemap/tmpl/' . $subTemplateName )) {
				echo $this->loadTemplate ( 'rss_' . $source->type );
			}
		}
	}
}
?>
</channel>
</rss>