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
<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">
<Document>
<name><![CDATA[<?php echo JText::sprintf('COM_JMAP_GEOSITEMAP_LOCATION', $this->cparams->get('geositemap_name', $this->joomlaConfig->get('sitename')));?>]]></name>
<atom:author>
<atom:name><![CDATA[<?php echo $this->cparams->get('geositemap_author', $this->joomlaConfig->get('sitename'));?>]]></atom:name>
</atom:author>
<atom:link rel="related" href="<?php echo JUri::base();?>" />
<Folder>
<Placemark id="geomap">
<name><![CDATA[<?php echo $this->cparams->get('geositemap_name', $this->joomlaConfig->get('sitename'));?>]]></name>
<address><![CDATA[<?php echo $this->data->formatted_address;?>]]></address>
<description><![CDATA[<?php echo $this->cparams->get('geositemap_description', '');?>]]></description>
<Point>
<coordinates><?php echo $this->data->geometry->location->lng . ',' . $this->data->geometry->location->lat;?></coordinates>
</Point>
</Placemark>
</Folder>
</Document>
</kml>