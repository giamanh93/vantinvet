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

$this->videoTitle = $this->apiJsonResponse->title;
$this->loadTemplate('videos_filtering');
// Only valid videos to insert in the sitemap
if($this->validVideo):
?>
<video:video>
<video:thumbnail_loc><?php echo htmlspecialchars($this->apiJsonResponse->thumbnail_360_url, ENT_COMPAT, 'UTF-8');?></video:thumbnail_loc>
<video:title><?php echo htmlspecialchars($this->videoTitle, ENT_COMPAT, 'UTF-8');?></video:title>
<video:description><![CDATA[<?php echo substr($this->apiJsonResponse->description, 0, 2048);?>]]></video:description>
<video:player_loc allow_embed="yes" autoplay="ap=1"><?php echo "http://www.dailymotion.com/embed/video/" . $this->videoID;?></video:player_loc>
<video:duration><?php echo $this->apiJsonResponse->duration;?></video:duration>
<video:live>no</video:live>
</video:video> 
<?php endif;?>