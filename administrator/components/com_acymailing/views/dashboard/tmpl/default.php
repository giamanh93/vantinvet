<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	4.9.2
 * @author	acyba.com
 * @copyright	(C) 2009-2015 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><style type="text/css">
#acymailingcpanel div.icon {
	border:1px solid #F0F0F0;
	color:#666666;
	display:block;
	float:left;
	text-decoration:none;
	vertical-align:middle;
	width:100%;
	float:left;
	margin-bottom:5px;
	margin-right:5px;
	text-align:center;
	width: 100%;
}

#acymailingcpanel ul{
	padding-left:10px;
}

#acymailingcpanel div.icon:hover {
	-moz-background-clip:border;
	-moz-background-inline-policy:continuous;
	-moz-background-origin:padding;
	background:#F9F9F9 none repeat scroll 0 0;
	border-color:#EEEEEE #CCCCCC #CCCCCC #EEEEEE;
	border-style:solid;
	border-width:1px;
	color:#0B55C4;
	cursor:pointer;
}

#acymailingcpanel span {
	display:block;
	text-align:center;
}

#acymailingcpanel img {
	margin:0 auto;
	padding:10px 0;
}

</style>
<?php if(!empty($this->geoloc_details)){ ?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script language="JavaScript" type="text/javascript">
	var mapOptions = {
		legend: 'none',
		height: 300,
		displayMode: 'markers',
		sizeAxis:{minSize: 2,  maxSize: 10, minValue:1, maxValue:15},
		enableRegionInteractivity:'true',
		region: '<?php echo $this->geoloc_region; ?>'
	};
 	google.load('visualization', '1', {'packages': ['geochart']});
	google.setOnLoadCallback(drawMarkersMap);
	var chart;
	var data;
	function drawMarkersMap() {
		data = new google.visualization.DataTable();
		data.addColumn('string', 'City');
		data.addColumn('number', 'Color');
		data.addColumn('number', 'Size');
		data.addColumn({type: 'string', role: 'tooltip'});
		<?php
		$myData = array();
		foreach($this->geoloc_city as $key => $city){
			$toolTipTxt = str_replace("'", "\'", JText::_('GEOLOC_NB_USERS')) . ': ' . $this->geoloc_details[$key];
			$lineData = "['" . str_replace("'", "\'", $city) . "', 1, " . $this->geoloc_details[$key] . ", '" . $toolTipTxt . "']";
			array_push($myData, $lineData);
		}
		echo "data.addRows([" . implode(", ", $myData) . "]);";
		?>
		chart = new google.visualization.GeoChart(document.getElementById('mapGeoloc_div'));
		chart.draw(data, mapOptions);
	};

</script>
<?php } ?>

<div id="acy_content" >
	<div id="iframedoc"></div>
<table class="adminform" width="100%">
	<?php if(!empty($this->geoloc_details)){ ?>
	<tr>
		<td>
			<div id="mapGeoloc_div" ></div>
		</td>
		<td>
			<?php
			if(acymailing_isAllowed($this->config->get('acl_subscriber_manage','all'))){
				include(dirname(__FILE__).DS.'userstats.php');
			}
			?>
		</td>
	</tr>
	<?php } ?>
	<tr>
		<td width="45%" valign="top">
			<div id="acymailingcpanel">
				<?php
					foreach($this->buttons as $oneButton){
						echo $oneButton;
					}
					?>
			</div>
		</td>
		<td valign="top">
			<?php
			if(empty($this->geoloc_details) && acymailing_isAllowed($this->config->get('acl_subscriber_manage','all'))){
				include(dirname(__FILE__).DS.'userstats.php');
			}

			if(acymailing_isAllowed($this->config->get('acl_subscriber_manage','all')) || acymailing_isAllowed($this->config->get('acl_statistics_manage','all'))){

				if(!ACYMAILING_J16) echo '<div style="float:right;"><a style="border:0px;text-decoration:none;" href="'.ACYMAILING_REDIRECT.'update-acymailing-'.$this->config->get('level').'" title="Your version is not up to date... click here to download the latest version, you won\'t lose data during the update." target="_blank"><img src="'.ACYMAILING_UPDATEURL.'check&version='.$this->config->get('version').'&level='.$this->config->get('level').'&component=acymailing" /></a></div>';

				echo $this->tabs->startPane( 'dash_tab');

				if(acymailing_isAllowed($this->config->get('acl_subscriber_manage','all'))){
					echo $this->tabs->startPanel( JText::_( 'USERS' ), 'dash_users');
					include(dirname(__FILE__).DS.'users.php');
					echo $this->tabs->endPanel();
				}

				if(acymailing_isAllowed($this->config->get('acl_statistics_manage','all'))){
					echo $this->tabs->startPanel( JText::_( 'STATISTICS' ), 'dash_stats');
					include(dirname(__FILE__).DS.'stats.php');
					echo $this->tabs->endPanel();
				}

				echo $this->tabs->endPane();
			}
			?>
		</td>
	</tr>
</table>
</div>
