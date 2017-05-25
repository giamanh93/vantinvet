<?php 
/** 
 * @package JMAP::METAINFO::administrator::components::com_jmap
 * @subpackage views
 * @subpackage metainfo
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

$delimiter = ';';
$enclosure = '"';

// Clean dirty buffer
ob_end_clean();
// Open buffer
ob_start();
// Open out stream
$outstream = fopen("php://output", "w");
// Funzione di scrittura nell'output stream
function __jmapoutputCSV(&$vals, $index, $userData) {
	// Export records only if metainfo are assigned
	if(property_exists($vals, 'metainfos')) {
		// Translate published status field
		$metainfosArray = (array)$vals->metainfos;
		unset($metainfosArray['id']);
		fputcsv($userData[0], $metainfosArray, $userData[1], $userData[2]);
	}
}

// Echo delle intestazioni
fputcsv ( $outstream, array (
		JText::_ ( 'COM_JMAP_METAINFO_LINK' ),
		JText::_ ( 'COM_JMAP_METATITLE' ),
		JText::_ ( 'COM_JMAP_METADESC' ),
		JText::_ ( 'COM_JMAP_METAIMAGE' ),
		JText::_ ( 'COM_JMAP_METAROBOTS' ),
		JText::_ ( 'COM_JMAP_METASTATUS' ),
		JText::_ ( 'COM_JMAP_METAEXCLUSION' )
), $delimiter, $enclosure );

// Output di tutti i records
array_walk($this->items, "__jmapoutputCSV", array($outstream, $delimiter, $enclosure));
fclose($outstream);

// Recupero output buffer content
$contents = ob_get_clean();
$size = strlen($contents);

header ( 'Pragma: public' );
header ( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
header ( 'Expires: ' . gmdate ( 'D, d M Y H:i:s' ) . ' GMT' );
header ( 'Content-Disposition: attachment; filename="metainfo_pg' . $this->pagination->pagesCurrent . '.csv"' );
header ( 'Content-Type: text/plain' );
header ( "Content-Length: " . $size );
echo $contents;
	
exit ();