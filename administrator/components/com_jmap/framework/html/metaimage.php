<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;
jimport('joomla.form.field');

/**
 * Form Field class for the Joomla CMS.
 * Provides a modal media selector including upload mechanism
 *
 * @since  1.6
 */
class JMapHtmlMetaimage extends JFormField {
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'Metaimage';

	/**
	 * The initialised state of the document object.
	 *
	 * @var    boolean
	 */
	protected static $initialised = false;

	/**
	 * The link.
	 *
	 * @var    string
	 */
	protected $link;

	/**
	 * The authorField.
	 *
	 * @var    string
	 */
	protected $preview;

	/**
	 * The preview.
	 *
	 * @var    string
	 */
	protected $directory;
	
	/**
	 * The data ajax identifier.
	 *
	 * @var    int
	 */
	protected $dataIdentifier;

	/**
	 * The previewWidth.
	 *
	 * @var    int
	 */
	protected $previewWidth;

	/**
	 * The previewHeight.
	 *
	 * @var    int
	 */
	protected $previewHeight;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'link':
			case 'preview':
			case 'directory':
			case 'previewWidth':
			case 'previewHeight':
			case 'dataIdentifier':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'link':
			case 'preview':
			case 'directory':
				$this->$name = (string) $value;
				break;

			case 'previewWidth':
			case 'previewHeight':
			case 'dataIdentifier':
				$this->$name = (int) $value;
				break;

			default:
				$reflectionClass = new ReflectionClass($this);
				$parentClass = $reflectionClass->getParentClass();
				try {
					if($parentClass->getMethod('__set')) {
						parent::__set($name, $value);
					}
				} catch(Exception $e){}
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see 	JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$result = parent::setup($element, $value, $group);

		return $result;
	}

	/**
	 * Method to get the field input markup for a media selector.
	 * Use attributes to identify specific created_by and asset_id fields
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		if (!self::$initialised)
		{
			// Load the modal behavior script.
			JHtml::_('behavior.modal');

			// Include jQuery
			JHtml::_('jquery.framework');

			// Build the script.
			$script = array();
			$script[] = '	function jInsertFieldValue(value, id) {';
			$script[] = '		var $ = jQuery.noConflict();';
			$script[] = '		var old_value = $("#" + id).val();';
			$script[] = '		if (old_value != value) {';
			$script[] = '			var $elem = $("#" + id);';
			$script[] = '			$elem.val(value);';
			$script[] = '			$elem.trigger("change");';
			$script[] = '			if (typeof($elem.get(0).onchange) === "function") {';
			$script[] = '				$elem.get(0).onchange();';
			$script[] = '			}';
			$script[] = '			jMediaRefreshPreview(id);';
			$script[] = '			var parentRow = $("#" + id).parents("tr");';
			$script[] = '			JMapMetainfo.refreshRowStatus(parentRow, $elem.data("mediaidentifier"));';
			$script[] = '		}';
			$script[] = '	}';

			$script[] = '	function jMediaRefreshPreview(id) {';
			$script[] = '		var $ = jQuery.noConflict();';
			$script[] = '		var value = $("#" + id).val();';
			$script[] = '		if(!value)return;';
			$script[] = '		value = value.replace(new RegExp("^[/]+"), "")';
			$script[] = '		value = value.match(/http/g) ? value : "'. JUri::root() . '" + value';
			$script[] = '		var $img = $("#" + id + "_preview");';
			$script[] = '		if ($img.length) {';
			$script[] = '			if (value) {';
			$script[] = '				$img.attr("src", value);';
			$script[] = '				$("#" + id + "_preview_empty").hide();';
			$script[] = '				$("#" + id + "_preview_img").show()';
			$script[] = '			} else { ';
			$script[] = '				$img.attr("src", "");';
			$script[] = '				$("#" + id + "_preview_empty").show();';
			$script[] = '				$("#" + id + "_preview_img").hide();';
			$script[] = '			} ';
			$script[] = '		} ';
			$script[] = '	}';

			$script[] = '	function jMediaRefreshPreviewTip(tip)';
			$script[] = '	{';
			$script[] = '		var $ = jQuery.noConflict();';
			$script[] = '		var $tip = $(tip);';
			$script[] = '		var $img = $tip.find("img.media-preview");';
			$script[] = '		$tip.find("div.tip").css("max-width", "none");';
			$script[] = '		var id = $img.attr("id");';
			$script[] = '		id = id.substring(0, id.length - "_preview".length);';
			$script[] = '		jMediaRefreshPreview(id);';
			$script[] = '		$tip.show();';
			$script[] = '	}';

			// Add the script to the document head.
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

			self::$initialised = true;
		}

		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="input-small ' . $this->class . '"' : ' class="input-small"';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';

		// The text field.
		$html[] = '<div class="input-prepend input-append ' . $this->class . '">';

		// The Preview.
		$showPreview = true;
		$showAsTooltip = true;
		$options = array(
			'onShow' => 'jMediaRefreshPreviewTip',
		);
		JHtml::_('behavior.tooltip', '.hasTipPreview', $options);

		if ($showPreview)
		{
			if ($this->value && file_exists(JPATH_ROOT . '/' . $this->value))
			{
				$src = JUri::root() . $this->value;
			}
			else
			{
				$src = '';
			}

			$width = $this->previewWidth;
			$height = $this->previewHeight;
			$style = '';
			$style .= ($width > 0) ? 'max-width:' . $width . 'px;' : '';
			$style .= ($height > 0) ? 'max-height:' . $height . 'px;' : '';

			$imgattr = array(
				'id' => $this->id . '_preview',
				'class' => 'media-preview',
				'style' => $style,
			);

			$img = JHtml::image($src, JText::_('JLIB_FORM_MEDIA_PREVIEW_ALT'), $imgattr);
			$previewImg = '<div id="' . $this->id . '_preview_img"' . ($src ? '' : ' style="display:none"') . '>' . $img . '</div>';
			$previewImgEmpty = '<div id="' . $this->id . '_preview_empty"' . ($src ? ' style="display:none"' : '') . '>'
				. JText::_('JLIB_FORM_MEDIA_PREVIEW_EMPTY') . '</div>';

			$html[] = '<div class="media-preview add-on">';
			$tooltip = $previewImgEmpty . $previewImg;
			$options = array(
				'title' => JText::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE'),
				'text' => '<span class="icon-eye"></span>',
				'class' => 'hasTipPreview'
			);

			$html[] = JHtml::tooltip($tooltip, $options);
			$html[] = '</div>';
		}

		$html[] = '	<input type="text" data-mediaidentifier="' . $this->dataIdentifier . '" name="' . $this->name . '" id="' . $this->id . '" value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $attr . ' />';

		if ($this->value && file_exists(JPATH_ROOT . '/' . $this->value))
		{
			$folder = explode('/', ltrim($this->value, '/'));
			$folder = array_diff_assoc($folder, explode('/', JComponentHelper::getParams('com_media')->get('image_path', 'images')));
			array_pop($folder);
			$folder = implode('/', $folder);
		}
		elseif (file_exists(JPATH_ROOT . '/' . JComponentHelper::getParams('com_media')->get('image_path', 'images') . '/' . $this->directory))
		{
			$folder = $this->directory;
		}
		else
		{
			$folder = '';
		}

		// The button.
		if ($this->disabled != true)
		{
			JHtml::_('bootstrap.tooltip');

			$html[] = '<a class="modal btn" title="' . JText::_('JLIB_FORM_BUTTON_SELECT') . '" href="'
				. ($this->readonly ? ''
				: ($this->link ? $this->link
					: 'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;author='
					. 'jmap') . '&amp;fieldid=' . $this->id . '&amp;folder=' . $folder) . '"'
				. ' rel="{handler: \'iframe\', size: {x: 1024, y: 640}}">';
			$html[] = JText::_('JLIB_FORM_BUTTON_SELECT') . '</a><a class="btn hasTooltip" title="'
				. JText::_('JLIB_FORM_BUTTON_CLEAR') . '" href="#" onclick="';
			$html[] = 'jInsertFieldValue(\'\', \'' . $this->id . '\');';
			$html[] = 'return false;';
			$html[] = '">';
			$html[] = '<span class="icon-remove"></span></a>';
		}

		$html[] = '</div>';

		return implode("\n", $html);
	}
}
