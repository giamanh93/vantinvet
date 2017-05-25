<?php
// namespace components\com_jmap\libraries\xml;
/**
 * @package JMAP::FRAMEWORK::components::com_jmap
 * @subpackage framework
 * @subpackage xml
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');
jimport ( 'joomla.utilities.date' );

/**
 * XML files splitter public responsibilities
 *
 * @package JMAP::FRAMEWORK::components::com_jmap
 * @subpackage framework
 * @subpackage xml
 * @since 1.0
 */
interface IJMapXmlSplitter {
	/**
	 * Chunks accessor method
	 *
	 * @access public
	 * @return mixed
	 */
	public function getChunks();
	
	/**
	 * Start chunking, given an input XML string
	 *
	 * @access public
	 * @param string $string
	 * @param string $tag
	 * @param number $howmany
	 * @return void
	 */
	public function chunkXMLString($xmlString, $tag = 'url', $howmany = 5, $precachedSitemapDirectFile = false);
}


/**
 * XML files splitter for sitemap chunking
 *
 * @package JMAP::FRAMEWORK::components::com_jmap
 * @subpackage libraries
 * @subpackage xml
 * @since 1.0
 */
class JMapXmlSplitter implements IJMapXmlSplitter {
	/**
	 * Chunks array container
	 * 
	 * @access private
	 * @var array
	 */
	private $chunkFiles;

	/**
	 * Chunks counter
	 *
	 * @access private
	 * @var int
	 */
	private $chunksCounter;

	/**
	 * Chunk payload
	 *
	 * @access private
	 * @var string
	 */
	private $chunkPayload;

	/**
	 * Target tag to extract chunks
	 *
	 * @access private
	 * @var string
	 */
	private $targetTag;

	/**
	 * Item count
	 *
	 * @access private
	 * @var int
	 */
	private $itemCount;

	/**
	 * Items limit to split chunk
	 *
	 * @access private
	 * @var int
	 */
	private $itemLimit;
	
	/**
	 * Items total nel file
	 *
	 * @access private
	 * @var int
	 */
	private $itemTotal;

	/**
	 * Format chunks file
	 * 
	 * @access private
	 * @var string
	 */
	private $format;
	
	/**
	 * Language chunks file if any, not required
	 *
	 * @access private
	 * @var string
	 */
	private $language;
	
	/**
	 * Dataset chunk if any, not required
	 *
	 * @access private
	 * @var string
	 */
	private $dataset;

	/**
	 * Itemid chunk if any, not required
	 *
	 * @access private
	 * @var string
	 */
	private $itemid;
	
	/**
	 * XML Root Node detection
	 *
	 * @access private
	 * @var string
	 */
	private $xmlRootNode;
	
	/**
	 * DOM Document instance
	 * 
	 * @access private
	 * @var Object
	 */
	private $doc;
	
	/**
	 * Live site string
	 * 
	 * @access private
	 * @var string
	 */
	private $liveSite;
	
	/**
	 * ISO date string
	 *
	 * @access private
	 * @var string
	 */
	private $ISO8601Date;

	/**
	 * Default hardcoded root nodes based on sitemap type
	 *
	 * @access private
	 * @var array
	 */
	private $defaultRootNodes;
	
	/**
	 * Component params
	 *
	 * @access private
	 * @var array
	 */
	private $cParams;

	/**
	 * Start tag elem processing
	 * 
	 * @access private
	 * @param string $xml
	 * @param string $tag
	 * @param array $attrs
	 * @return void
	 */
	private function startElement($xml, $tag, $attrs = array()) {
		if (!($this->chunksCounter || $this->itemCount))
			if ($this->targetTag == strtolower($tag))
				$this->chunkPayload = '';
		$this->chunkPayload .= "<$tag";
		foreach ($attrs as $k => $v)
			$this->chunkPayload .= " $k=" . '"' . addslashes($v) . '"';
		$this->chunkPayload .= '>';
	}

	/**
	 * End tag elem processing
	 *
	 * @access private
	 * @param string $xml
	 * @param string $tag
	 * @return void
	 */
	private function endElement($xml, $tag) {
		$this->chunkPayload .= "</$tag>";
		if ($this->targetTag == strtolower($tag)) {
			if (++$this->itemCount >= $this->itemLimit || ($this->itemTotal == 1 && ($this->itemCount <= $this->itemLimit))) {
				$this->processChunk($this->chunkPayload);
				$this->chunkPayload = '';
				$this->itemCount = 0;
			}
			$this->itemTotal -= 1;
		}
	}

	/**
	 * Data handler concatenate del payload
	 *
	 * @access private
	 * @param string $xml
	 * @param string $tag
	 * @return void
	 */
	private function dataHandler($xml, $data) {
		$this->chunkPayload .= $data;
	}

	/**
	 * Default handler
	 * 
	 * @access private
	 * @param string $xml
	 * @param string $tag
	 * @return void
	 */
	private function defaultHandler($xml, $data) {
		// a.k.a. Wild Text Fallback Handler, or WTFHandler for short.
	}

	/**
	 * Setting create del parser XML
	 *
	 * @access private
	 * @param string $CHARSET
	 * @param boolean $bareXML
	 * @return Resource
	 */
	private function createXMLParser($CHARSET, $bareXML = false) {
		$CURRXML = xml_parser_create($CHARSET);
		xml_parser_set_option($CURRXML, XML_OPTION_CASE_FOLDING, false);
		xml_parser_set_option($CURRXML, XML_OPTION_TARGET_ENCODING, $CHARSET);
		xml_set_element_handler($CURRXML, array($this, 'startElement'), array($this, 'endElement'));
		xml_set_character_data_handler($CURRXML, array($this, 'dataHandler'));
		xml_set_default_handler($CURRXML, array($this, 'defaultHandler'));
		if ($bareXML)
			xml_parse($CURRXML, '<?xml version="1.0"?>', 0);
		return $CURRXML;
	}

	/**
	 * Processa il punto di arrivo di un chunk memorizzandolo
	 *
	 * @access private
	 * @param string $xmlstring
	 * @return void
	 */
	private function processChunk($xmlstring) {
		// Init chunk file with data and name
		$data = null;
		$data .= "<?xml version='1.0' encoding='UTF-8'?>\n";
		$data .= "<" . $this->xmlRootNode['rootNodeName'] . " " . $this->xmlRootNode['rootNodeAttributes'] . ">\n";
		$data .= JFilterOutput::ampReplace(trim($xmlstring, "\n"));
		$data .= "\n</" . $this->xmlRootNode['rootNodeName'] .">";

		$name = "sitemap_" . $this->format . $this->language . $this->dataset . $this->itemid . '_' . $this->chunksCounter . '.xml';

		$file = array('data' => $data, 'name' => $name);

		// Assign chunk to container
		$this->chunkFiles[] = $file;

		// Increment counter for chunks
		$this->chunksCounter++;
	}
 
	/**
	 * function getXMLRootNode
	 * @param string An xml string
	 * @return string Return XML root node name
	 */

	private function getXMLRootNode($xmlstr) { 
		// Load the XML string
		if (!$this->doc->loadXML($xmlstr)) {
			throw new JMapException('Unable to parse XML string', 'warning');
		}

		// If default root nodes need to be retrieved hardcoded get it and avoid parsing
		if($this->cParams->get('splitting_hardcoded_rootnode', true)) {
			return $this->defaultRootNodes[$this->format];
		}

		// Find the root tag name
		$root = $this->doc->documentElement;
		 
		if (!isset($root)) {
			throw new JMapException('Unable to find XML root node', 'warning');
		}

		if (!isset($root->nodeName)) {
			throw new JMapException('Unable to find XML root node name', 'warning');
		}

		if($root->hasAttributes()) {
			$attributes = array();
			foreach ($root->attributes as $attr) {
				$attributes[] = $attr->nodeName . '="' . $attr->nodeValue . '" ';
			}
		}
		
		$xpath = new DOMXPath($this->doc);
		foreach( $xpath->query('namespace::*', $root) as $ns ) {
			if($ns->nodeName == 'xmlns:xml') {
				continue;
			}
			$attributes[] = $ns->nodeName . '="' . $ns->nodeValue . '" ';
		}
		$attributes = array_reverse($attributes);
		
		$rootNodeInfo = array('rootNodeName'=>$root->nodeName, 'rootNodeAttributes'=>trim(implode('', $attributes)));
		
		return $rootNodeInfo;
	}
	
	/**
	 * Generate index XML file for sitemap chunks
	 * 
	 * @access private
	 * @return void
	 */
	private function generateIndexFile() {
		// Get current chunk files element in array
		
		// For every chunks generate an XML entry with lastmod date
		$data = null;
		$data .= "<?xml version='1.0' encoding='UTF-8'?>\n";
		$data .= "<sitemapindex xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>\n";
		
		foreach ($this->chunkFiles as $file) {
			$data .= "<sitemap>\n" .
					 	"<loc>" . $this->liveSite . $file['name'] . "</loc>\n" .
					 	"<lastmod>" . $this->ISO8601Date . "</lastmod>\n" .
					 "</sitemap>\n";
		}
		$data .= "</sitemapindex>";
		
		// Finally add itself as a file to chunk files array
		$name = "sitemapindex_" . $this->format . $this->language . $this->dataset . $this->itemid . '.xml';
		$file = array('data' => $data, 'name' => $name);
		
		// Assign chunk to container
		$this->chunkFiles[] = $file;
		
		// Increment counter for chunks
		$this->chunksCounter++;
	}

	/**
	 * Chunks accessor method
	 *
	 * @access public
	 * @return mixed
	 */
	public function getChunks() {
		if (!$this->chunksCounter) {
			return false;
		}
	
		return $this->chunkFiles;
	}
	
	/**
	 * Start chunking, given an input XML string
	 * 
	 * @access public
	 * @param string $string
	 * @param string $tag
	 * @param number $howmany
	 * @return void
	 */
	public function chunkXMLString($xmlString, $tag = 'url', $howmany = 5, $precachedSitemapDirectFile = false) {
		$this->targetTag = $tag;
		$this->itemLimit = (int)$howmany;

		// Extract del root node contestuale alla mappa
		$this->xmlRootNode = $this->getXMLRootNode($xmlString);
		$this->itemTotal = $this->doc->getElementsByTagName('url')->length;
		 
		$xml = $this->createXMLParser('UTF-8', false);
		if(!$precachedSitemapDirectFile) {
			$fp = fopen('data://text/plain,' . urlencode($xmlString), 'r');
		} else {
			$fp = fopen($precachedSitemapDirectFile, 'r');
		}

		while (!feof($fp)) {
			$chunk = fgets($fp, 10240);
			xml_parse($xml, $chunk, feof($fp));
		}
		xml_parser_free($xml);
		
		// Finally add the index to the bunch of chunks to be included in zip
		$this->generateIndexFile();
	}

	/**
	 * Class constructor
	 * 
	 * @access public
	 * @param string $format
	 * @param string $language
	 * @param int $dataset
	 * @param int $itemid
	 * @return Object&
	 */
	public function __construct($format, $language, $dataset, $itemid) {
		// Init properties
		$this->format = $format;
		$this->language = $language;
		$this->dataset = $dataset;
		$this->itemid = $itemid;
		$this->cParams = JComponentHelper::getParams('com_jmap');
		
		// Default root nodes
		$this->defaultRootNodes = array(
				'xml'=>array('rootNodeName'=>'urlset', 'rootNodeAttributes'=>'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"'),
				'images'=>array('rootNodeName'=>'urlset', 'rootNodeAttributes'=>'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"'),
				'gnews'=>array('rootNodeName'=>'urlset', 'rootNodeAttributes'=>'xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"'),
				'mobile'=>array('rootNodeName'=>'urlset', 'rootNodeAttributes'=>'xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"'),
				'videos'=>array('rootNodeName'=>'urlset', 'rootNodeAttributes'=>'xmlns:video="http://www.google.com/schemas/sitemap-video/1.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"'),
				'hreflang'=>array('rootNodeName'=>'urlset', 'rootNodeAttributes'=>'xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"')
		);
		
		// Date format for index XML file
		$dateObj = new JDate(); 
		$globalConfig = JFactory::getConfig();
		$dateObj->setTimezone(new DateTimeZone($globalConfig->get('offset')));
		$this->ISO8601Date = $dateObj->toISO8601(true);
		
		// Live site for index XML file
		$this->liveSite = JUri::root(false);
		
		// Create DOM model
		$this->doc = new DOMDocument();

		$this->chunkFiles = array();
		$this->targetTag = null;
		$this->chunksCounter = 0;
		$this->itemLimit = 5;
	}
}