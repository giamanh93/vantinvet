<?php
/** 
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage views
 * @subpackage sitemap
 * @subpackage tmpl
 * @subpackage videos
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

if($this->apiJsonResponse->pageInfo->totalResults):
$videoItemObject = $this->apiJsonResponse->items[0];
$this->videoTitle = $videoItemObject->snippet->title;
$this->loadTemplate('videos_filtering');
// Only valid videos to insert in the sitemap
if($this->validVideo):
?>
<video:video>
<video:thumbnail_loc>http://i.ytimg.com/vi/<?php echo $this->videoID;?>/hqdefault.jpg</video:thumbnail_loc>
<video:title><?php echo htmlspecialchars($this->videoTitle, ENT_COMPAT, 'UTF-8');?></video:title>
<video:description><![CDATA[<?php echo substr($videoItemObject->snippet->description, 0, 2048);?>]]></video:description>
<video:player_loc allow_embed="yes" autoplay="ap=1"><?php echo "https://www.youtube.com/v/" . $this->videoID;?></video:player_loc>
<?php if(class_exists('DateInterval')):?>
<video:duration><?php $interval = new DateInterval($videoItemObject->contentDetails->duration); echo strtotime("1970-01-01 " . $interval->format('%H:%I:%S') . " UTC");?></video:duration>
<?php endif;?>
<video:publication_date><?php $dateObj = new JDate($videoItemObject->snippet->publishedAt); $dateObj->setTimezone(new DateTimeZone('UTC'));echo $dateObj->toISO8601(true);?></video:publication_date>
<video:live>no</video:live>
</video:video> 
<?php endif;?>
<?php endif;?>