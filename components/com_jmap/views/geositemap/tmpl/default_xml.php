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
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:geo="http://www.google.com/geo/schemas/sitemap/1.0">
<url>
<loc><?php echo $this->liveSite . ($this->cparams->get('sitemap_links_sef', 0) ? JRoute::_($this->kmlLink) : '/' . $this->kmlLink); ?></loc>
</url>
</urlset>