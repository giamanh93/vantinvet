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

$this->videoTitle = $this->apiJsonResponse[0]->title;
$this->loadTemplate('videos_filtering');
// Only valid videos to insert in the sitemap
if($this->validVideo):
?>
<video:video>
<video:thumbnail_loc><?php echo htmlspecialchars($this->apiJsonResponse[0]->thumbnail_large, ENT_COMPAT, 'UTF-8');?></video:thumbnail_loc>
<video:title><?php echo htmlspecialchars($this->videoTitle, ENT_COMPAT, 'UTF-8');?></video:title>
<video:description><![CDATA[<?php echo substr($this->apiJsonResponse[0]->description, 0, 2048);?>]]></video:description>
<video:player_loc allow_embed="yes" autoplay="ap=1"><?php echo "http://player.vimeo.com/video/" . $this->videoID;?></video:player_loc>
<video:duration><?php echo $this->apiJsonResponse[0]->duration;?></video:duration>
<?php if(count($this->apiJsonResponse[0]->stats_number_of_plays)):?>
<video:view_count><?php echo $this->apiJsonResponse[0]->stats_number_of_plays;?></video:view_count>
<?php endif;?>
<video:publication_date><?php $dateObj = new JDate($this->apiJsonResponse[0]->upload_date); $dateObj->setTimezone(new DateTimeZone('UTC'));echo $dateObj->toISO8601(true);?></video:publication_date>
<video:uploader><?php echo htmlspecialchars($this->apiJsonResponse[0]->user_name, ENT_COMPAT, 'UTF-8'); ?></video:uploader>
<video:live>no</video:live>
</video:video> 
<?php endif;?>