<?php
/**
 * @package     Mosets
 * @subpackage  Profile Picture
 *
 * @copyright   Copyright (C) 2012 Mosets Consulting, Inc. All rights reserved.
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
			$path = JURI::root().'media'.DS.'plg_user_profilepicture'.DS.'images'.DS.'200'.DS.$this->value;
			$profilepicture = '<img src="'.$path.'" style="float:left;clear:left;margin:6px 0" />';
			
			$remove_pp = '<input type="checkbox" id="'.$this->id.'remove" name="'.$this->name.'[remove]" value="'.$this->value.'" style="float:left;clear:left;width:auto" />';
			$remove_pp .= '<label for="'.$this->id.'remove" style="float:left;clear:none">';
			$remove_pp .= JText::_('PLG_USER_PROFILEPICTURE_FIELD_PICTURE_REMOVE');
			$remove_pp .= '</label>';
		}

		return '<input type="file" name="' . $this->name . '" id="' . $this->id . '"' . ' value=""' . $accept . $disabled . $class . $size
			. $onchange . ' />'.$profilepicture.$remove_pp;
	}
}
