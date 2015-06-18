<?php
/**
 * @package     Mosets
 * @subpackage  Profile Picture
 *
 * @copyright   Copyright (C) 2012-present Mosets Consulting, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for Mosets Profile Picture.
 * Provides an input field for profile picture
 *
 * @package     Mosets
 * @subpackage  Profile Picture
 * @link        http://www.w3.org/TR/html-markup/input.file.html#input.file
 * @since       1.0
 */
class JFormFieldProfilePicture extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $type = 'ProfilePicture';

	/**
	 * The Profile Picture plugin parameter
	 *
	 * @var    JRegistry
	 * @since  1.0
	 */
	public $params;

	/**
	 * The Profile Picture plugin parameter
	 *
	 * @var    Integer
	 * @since  1.0
	 */
	public $maxUploadSizeInBytes;

	/**
	 * The inline CSS style for IMG element
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $inlineStyleImg = 'float:left;clear:left;margin:6px 0';

	/**
	 * The inline CSS style for LABEL element
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $inlineStyleLabel = 'float:left;clear:none';

	/**
	 * The inline CSS style for INPUT element
	 *
	 * @var    string
	 * @since  2.0
	 */
	public $inlineStyleInput = 'float:left;clear:left;width:auto';

	/**
	 * Override by loading Profile Picture parameters.
	 *
	 * @param   JForm  $form  The form to attach to the form field object.
	 *
	 * @since   2.0
	 */
	public function __construct($form = null)
	{
		$plugin = JPluginHelper::getPlugin('user', 'profilepicture');
		$this->params = new JRegistry($plugin->params);

		$this->maxUploadSizeInBytes = $this->params->get('maxUploadSizeInBytes', 800000);

		parent::__construct($form);
	}

	/**
	 * Method to get the field input markup for the file field.
	 * Field attributes allow specification of a maximum file size and a string
	 * of accepted file extensions.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.0
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$accept = $this->element['accept'] ? ' accept="' . (string) $this->element['accept'] . '"' : '';
		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';
		
		$profilepicture = '';
		$remove_pp = '';

		if( !empty($this->value) ) {
			$path = JURI::root().'media/plg_user_profilepicture/images/200/'.$this->value;

			// <IMG> element
			$profilepicture .= '<img src="'.$path.'" ';

			if( !empty($this->inlineStyleImg) )
			{
				$profilepicture .= 'style="' . $this->inlineStyleImg . '" ';
			}

			$profilepicture .= '/>';

			// <INPUT> element for removing profile picture
			$remove_pp .= '<input type="checkbox" ';
			$remove_pp .= 'id="'.$this->id.'remove" ';
			$remove_pp .= 'name="'.$this->name.'[remove]" ';
			$remove_pp .= 'value="'.$this->value.'" ';

			if( !empty($this->inlineStyleInput) )
			{
				$remove_pp .= 'style="' . $this->inlineStyleInput . '" ';
			}

			$remove_pp .= '/>';

			// <LABEL> element
			$remove_pp .= '<label ';
			$remove_pp .= 'for="'.$this->id.'remove" ';

			if( !empty($this->inlineStyleLabel) )
			{
				$remove_pp .= 'style="' . $this->inlineStyleLabel . '" ';
			}

			$remove_pp .= '>';
			$remove_pp .= JText::_('PLG_USER_PROFILEPICTURE_FIELD_PICTURE_REMOVE');
			$remove_pp .= '</label>';
		}

		$message = '<p>';

		if ( !$this->isProfilePictureLibraryEnabled() )
		{
			$message .= '<span class="text-warning">';
			$message .= JText::_('PLG_USER_PROFILEPICTURE_FIELD_ERROR_LIBRARY_NOT_LOADED');
			$message .= '</span>';
			$message .= '<br>';
		}

		$message .= JText::sprintf('PLG_USER_PROFILEPICTURE_FIELD_MAXUPLOADSIZEINBYTES_MESSAGE', $this->maxUploadSizeInKB());
		$message .= '<br>';
		$message .= JText::_('PLG_USER_PROFILEPICTURE_FIELD_MINDIMENSION_MESSAGE');
		$message .= '</p>';

		$return = '<input type="file" name="' . $this->name . '" id="' . $this->id . '"' . ' value=""' . $accept . $disabled . $class . $size
			. $onchange;
		$return .= (!$this->isProfilePictureLibraryEnabled()) ? 'disabled ' : '';
		$return .= ' />'.$message.$profilepicture.$remove_pp;

		return $return;
	}


	/**
	 * Check if the Profile Picture library is enabled
	 *
	 * @return bool
	 */
	protected function isProfilePictureLibraryEnabled()
	{
		if ( JLibraryHelper::isEnabled( 'profilepicture' ) )
		{
			return true;
		}
		return false;
	}

	/**
	 * Return the configured parameter for maximum allowed uploaded file size.
	 *
	 * @return    int
	 * @since    2.0
	 */	protected function maxUploadSizeInKB()
	{
		return $this->maxUploadSizeInBytes / 1000;
	}
}
