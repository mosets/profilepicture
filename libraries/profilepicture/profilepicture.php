<?php
/**
 * @package     Mosets
 * @subpackage  ProfilePicture
 *
 * @copyright   Copyright (C) 2012-present Mosets Consulting, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.file');

DEFINE('PROFILEPICTURE_PATH_50', JPATH_ROOT.'/media/plg_user_profilepicture/images/50/');
DEFINE('PROFILEPICTURE_PATH_200', JPATH_ROOT.'/media/plg_user_profilepicture/images/200/');
DEFINE('PROFILEPICTURE_PATH_ORIGINAL', JPATH_ROOT.'/media/plg_user_profilepicture/images/original/');
DEFINE('PROFILEPICTURE_PATH_FILLER', JPATH_ROOT.'/media/plg_user_profilepicture/images/filler/');

DEFINE('PROFILEPICTURE_SIZE_FILLER', 'filler');
DEFINE('PROFILEPICTURE_SIZE_ORIGINAL', 'original');
DEFINE('PROFILEPICTURE_SIZE_50', 50);
DEFINE('PROFILEPICTURE_SIZE_200', 200);

/**
 * Class to retrieve profile picture of a user
 *
 * @package     Mosets
 * @subpackage  ProfilePicture
 * @since       1.0
 */
class ProfilePicture
{
	/**
	 * @var    int  User ID
	 * @since  1.0
	 */
	public $userId = null;
	
	/**
	 * @var    string  File name of the profile picture
	 * @since  1.0
	 */
	public $filename = null;
	
	/**
	 * @const  string
	 * @since  1.0
	 */
	const PROFILE_KEY = 'profilepicture.file';
	
	/**
	 * Class constructor.
	 *
	 * @param   int  $userId  User ID
	 *
	 * @since   1.0
	 */
	public function __construct($userId)
	{
		if( is_numeric($userId) )
		{
			$this->userId = $userId;
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Method to set User ID
	 *
	 * @param   int  $userId    User ID
	 *
	 * @since   1.0
	 */
	public function setUserId($userId)
	{
		$this->userId = $userId;
	}
	
	/**
	 * Method to set Profile Picture filename
	 *
	 * @param   str  $filename    Profile picture filename
	 *
	 * @since   1.0
	 */
	public function setFilename($filename)
	{
		$this->filename = $filename;
	}
	
	/**
	 * Get the user's profile picture filename.
	 *
	 * @return  str		The profile picture filename.
	 *
	 * @since   1.0
	 */
	public function getFilename()
	{
		if( !empty($this->filename) )
		{
			return $this->filename;
		}
		else
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)->select('profile_value')->from('#__user_profiles')
				->where('profile_key = ' . $db->quote(ProfilePicture::PROFILE_KEY))
				->where('user_id = '.(int) $this->userId);
			$db->setQuery($query);
			$filename = $db->loadResult();

			if( !is_null($filename) )
			{
				$this->filename = $filename;
				return $filename;
			} else {
				return false;
			}			
		}
	}

	/**
	 * Get the user's profile picture URL.
	 *
	 * @param int $size
	 *
	 * @return  str        The profile picture URL.
	 *
	 * @since   1.0
	 */
	public function getFillerURL($size=PROFILEPICTURE_SIZE_200)
	{
		return JURI::root().'media/plg_user_profilepicture/images/'.PROFILEPICTURE_SIZE_FILLER.'/'.$size.'.png';
	}

	/**
	 * Get the user's profile picture URL.
	 *
	 * @param int $size
	 *
	 * @return  str        The profile picture URL.
	 *
	 * @since   1.0
	 */
	public function getURL($size=PROFILEPICTURE_SIZE_200)
	{
		if( $filename = $this->getFilename() )
		{
			return JURI::root().'media/plg_user_profilepicture/images/'.$size.'/'.$this->getFilename();
		} else {
			return false;
		}
	}

	/**
	 * Get the user's profile picture path.
	 *
	 * @param int $size
	 *
	 * @return  str        The profile picture path.
	 *
	 * @since   1.0
	 */
	public function getPath($size=PROFILEPICTURE_SIZE_200)
	{
		if( $filename = $this->getFilename() )
		{
			return JPATH_SITE.'/media/plg_user_profilepicture/images/'.$size.'/'.$this->getFilename();
		} else {
			return false;
		}
	}

	/**
	 * Method to check if a profile picture of a certain size exists
	 *
	 * @param int $size
	 *
	 * @internal param \The $string size of the profile picture to check
	 *
	 * @return  boolean    True if the profile picture exists
	 *
	 * @since    1.0
	 */
	public function exists($size=PROFILEPICTURE_SIZE_200)
	{
		if( $filename = $this->getFilename() )
		{
			return JFile::exists($this->getPath($size));
		} else {
			return false;
		}
	}

	/**
	 * Render the IMG HTML element.
	 *
	 * @param int $size    The size of the rendered profile picture image
	 * @param string    $alt     The IMG element 'alt' attribute.
	 * @param array     $attribs Additional attributes to be inserted in to the rendered HTML
	 *
	 * @return  string The rendered IMG element.
	 *
	 * @since 1.0
	 */
	public function toHTML($size = PROFILEPICTURE_SIZE_200, $alt = '', $attribs = array())
	{
		if (is_array($attribs))
		{
			$attribs = JArrayHelper::toString($attribs);
		}

		$html = '';
		if( $this->exists() )
		{
			$html .= '<img src="' . $this->getURL($size).'" alt="' . $alt . '" ' . $attribs . '/>';
		} else {
			$html .= '<img src="' . $this->getFillerURL($size) . '" alt="' . $alt . '" ' . $attribs . '/>';
		}
		return $html;
	}
}
