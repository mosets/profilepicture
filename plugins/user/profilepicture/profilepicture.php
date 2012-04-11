<?php
/**
 * @copyright	Copyright (C) 2012 Mosets Consulting, Inc. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.image.image');
jimport('mosets.profilepicture.profilepicture');

/**
 * Mosets Profile Picture Plugin.
 *
 * @package	Mosets
 * @subpackage	Profile Picture
 * @version	1.0
 */
class plgUserProfilePicture extends JPlugin
{
	/**
	 * @const  string
	 * @since  1.0
	 */
	const PROFILE_KEY = 'profilepicture.file';
	
	/**
	 * @var    array  The list of sizes that will be saved.
	 * @since  1.0
	 */
	protected $sizes = array(
				PROFILEPICTURE_SIZE_ORIGINAL,
				PROFILEPICTURE_SIZE_50,
				PROFILEPICTURE_SIZE_200
				);

	/**
	 * @const  string	Profile Picture file extension
	 * @since  1.0
	 */
	const FILE_EXTENSION = 'jpg';
	
	/**
	 * @param	string	$context	The context for the data
	 * @param	object	$data		The user data
	 *
	 * @return	boolean
	 * @since	1.0
	 */
	function onContentPrepareData($context, $data)
	{
		$app	= JFactory::getApplication();

		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile','com_users.user', 'com_users.registration'))) {
			return true;
		}

		if (is_object($data))
		{
			$userId = isset($data->id) ? $data->id : 0;

			// Load the profile picture from the database.
			$db = JFactory::getDbo();
			$query	= $db->getQuery(true);
			
			$query->select('profile_value');
			$query->from('#__user_profiles');
			$query->where('user_id = '.(int) $userId);
			$query->where('profile_key = '.$db->quote(plgUserProfilePicture::PROFILE_KEY));
			$db->setQuery($query);
			
			$result = $db->loadResult();

			// Check for a database error.
			if ($db->getErrorNum())
			{
				$this->_subject->setError($db->getErrorMsg());
				return false;
			}

			// Merge the pictureprofile data.
			$data->profilepicture['file'] = $result;

		}

		return true;
	}

	/**
	 * @param	JForm	$form	The form to be altered.
	 * @param	array	$data	The associated data for the form.
	 *
	 * @return	boolean
	 * @since	1.0
	 */
	function onContentPrepareForm($form, $data)
	{
		// Load user_profile plugin language
		$lang = JFactory::getLanguage();
		$lang->load('plg_user_profilepicture', JPATH_ADMINISTRATOR);

		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;
		}

		// Check we are manipulating a valid form.
		if (!in_array($form->getName(), array('com_admin.profile','com_users.user', 'com_users.registration','com_users.profile'))) {
			return true;
		}

		// Add the registration fields to the form.
		JForm::addFormPath(dirname(__FILE__).'/profiles');
		$form->loadFile('profilepicture', false);

		return true;
	}

	function onUserAfterSave($data, $isNew, $result, $error)
	{
		$userId	= JArrayHelper::getValue($data, 'id', 0, 'int');
		$files	= JRequest::getVar( 'jform', null, 'files');
		$post	= JRequest::getVar( 'jform', null);
		
		// Remove profile picture
		if( !empty($post['profilepicture']['file']['remove']) && isset($userId) ) 
		{
			$this->removeProfilePicture($userId);
		}

		// Save original picture, resize and save it
		if($files['error']['profilepicture']['file'] == 0 && isset($files['name']['profilepicture']['file']) && (!empty($files['name']['profilepicture']['file'])))
		{
			$profilepicture = new JImage($files['tmp_name']['profilepicture']['file']);

			if( 
				$profilepicture->getWidth() < PROFILEPICTURE_SIZE_200
				||
				$profilepicture->getHeight() < PROFILEPICTURE_SIZE_200
			) {
				throw new Exception(JText::_('PLG_USER_PROFILEPICTURE_ERROR_TOO_SMALL'));
			}
			
			$pp_filename = sha1($userId.uniqid()).'.'.plgUserProfilePicture::FILE_EXTENSION;
			
			foreach( $this->sizes AS $size )
			{
				if($size == PROFILEPICTURE_SIZE_ORIGINAL)
				{
					$profilepicture->toFile(PROFILEPICTURE_PATH_ORIGINAL.$pp_filename);
				} else {
					$resized = $profilepicture->resize($size, $size, true, JImage::SCALE_INSIDE);
					$resized->toFile(constant('PROFILEPICTURE_PATH_'.$size).$pp_filename);
				}
			}
		}

		if ($userId && $result && isset($files['name']['profilepicture']['file']) && (!empty($files['name']['profilepicture']['file'])))
		{
			try
			{
				$db = JFactory::getDbo();
				$query	= $db->getQuery(true);
				
				$query->delete('#__user_profiles')
					->where('user_id = '.$userId)
					->where('profile_key = '.$db->quote(plgUserProfilePicture::PROFILE_KEY));
				$db->setQuery($query);

				if (!$db->query()) {
					throw new Exception($db->getErrorMsg());
				}

				$query	= $db->getQuery(true);
				$query->insert('#__user_profiles')
					->columns('user_id, profile_key, profile_value, ordering')
					->values(
						$userId.', '
						.$db->quote(plgUserProfilePicture::PROFILE_KEY).', '
						.$db->quote($pp_filename).', '
						.' 1');
				$db->setQuery($query);
			 
				if (!$db->query()) {
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}
		return true;
	}

	/**
	 * Remove profile picture
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param	array		$user		Holds the user data
	 * @param	boolean		$success	True if user was succesfully stored in the database
	 * @param	string		$msg		Message
	 */
	function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success) {
			return false;
		}

		$userId	= JArrayHelper::getValue($user, 'id', 0, 'int');

		return $this->removeProfilePicture($userId);
	}
	
	/**
	 * Remove profile picture's file and table record
	 *
	 * @param	int		$userId		User ID
	 */
	function removeProfilePicture($userId)
	{
		if ($userId)
		{
			try
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				
				$query->select('profile_value');
				$query->from('#__user_profiles');
				$query->where('user_id = '.(int) $userId);
				$query->where('profile_key = '.$db->quote(plgUserProfilePicture::PROFILE_KEY));

				$db->setQuery($query, 0, 1);
				$profile_value = $db->loadResult();

				jimport('joomla.filesystem.file');
				foreach( $this->sizes AS $size )
				{
					if($size == PROFILEPICTURE_SIZE_ORIGINAL)
					{
						JFile::delete(PROFILEPICTURE_PATH_ORIGINAL.$profile_value);
					} else {
						JFile::delete(constant('PROFILEPICTURE_PATH_'.$size).$profile_value);
					}
				}
				
				$query	= $db->getQuery(true);
				
				$query->delete('#__user_profiles')
					->where('user_id = '.$userId)
					->where('profile_key = '.$db->quote(plgUserProfilePicture::PROFILE_KEY));
				$db->setQuery($query);

				if (!$db->query()) {
					throw new Exception($db->getErrorMsg());
				}
			}
			catch (JException $e)
			{
				$this->_subject->setError($e->getMessage());
				return false;
			}
		}

		return true;
	}
}
?>