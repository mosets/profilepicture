<?php
/**
 * @copyright	Copyright (C) 2012-present Mosets Consulting, Inc. All rights reserved.
 * @license	GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.image.image');
jimport('profilepicture.profilepicture');

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
	 * @var    boolean  True, to crop picture so that they are square.
	 * @since  1.0
	 */
	protected $square = true;

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

	/**
	 * Return the configured parameter for maximum allowed uploaded file size.
	 *
	 * @return    int
	 * @since    2.0
	 */
	protected function maxUploadSizeInBytes()
	{
		return $this->params->get('maxUploadSizeInBytes', 800000);
	}

	/**
	 * Check if the given file size exceeds the maxUploadSizeInBytes parameter.
	 *
	 * @param $bytes File size in bytes.
	 *
	 * @return    boolean
	 * @since    2.0
	 */
	protected function doesExceedFileSizeLimit($bytes)
	{
		if( $bytes > $this->maxUploadSizeInBytes() ) {
			return true;
		}

		return false;
	}

	function onUserAfterSave($data, $isNew, $result, $error)
	{
		$userId	= JArrayHelper::getValue($data, 'id', 0, 'int');
		$files	= JRequest::getVar( 'jform', null, 'files');
		$post	= JRequest::getVar( 'jform', null);

		$savedNewProfilePicture = false;

		// Save original picture, resized pictures and save them
		if( $files['error']['profilepicture']['file'] == 0 && !empty($files['tmp_name']['profilepicture']['file']) )
		{

			// Throw new exception if the uploaded file exceed the maximum allowed file size.
			if ( $this->doesExceedFileSizeLimit($files['size']['profilepicture']['file']) )
			{
				throw new Exception(JText::sprintf('PLG_USER_PROFILEPICTURE_ERROR_FILE_SIZE_TOO_BIG', ($this->maxUploadSizeInBytes()/1000)));
			}

			$profilepicture = new JImage($files['tmp_name']['profilepicture']['file']);
			$sourceWidth = $profilepicture->getWidth();
			$sourceHeight = $profilepicture->getHeight();
			
			if( 
				$sourceWidth < PROFILEPICTURE_SIZE_200
				||
				$sourceHeight < PROFILEPICTURE_SIZE_200
			) {
				throw new Exception(JText::_('PLG_USER_PROFILEPICTURE_ERROR_TOO_SMALL'));
			}
			
			$pp_filename = sha1($userId.uniqid()).'.'.plgUserProfilePicture::FILE_EXTENSION;
			
			foreach( $this->sizes AS $size )
			{
				if($size == PROFILEPICTURE_SIZE_ORIGINAL)
				{
					$profilepicture->toFile(PROFILEPICTURE_PATH_ORIGINAL.$pp_filename);
					$savedNewProfilePicture = true;
				}
				else
				{
					$ratio = max($sourceWidth, $sourceHeight) / $size;
					$ratio = max($ratio, 1.0);
					$resizedWidth = (int)($sourceWidth / $ratio);
					$resizedHeight = (int)($sourceHeight / $ratio);
					$left = 0;
					$top = 0;
					
					if($this->square && $sourceWidth > $size && $sourceHeight > $size)
					{
						if($sourceWidth > $sourceHeight)
						{
							$left = (int)($sourceWidth - $sourceHeight) / 2;
							$top = 0;
							$croppedWidth = $sourceHeight;
							$croppedHeight = $sourceHeight;
							$resizedHeight = $resizedWidth;
						}
						elseif($sourceHeight >= $sourceWidth)
						{
							$left = 0;
							$top = (int)(($sourceHeight - $sourceWidth) / 2);
							$croppedWidth = $sourceWidth;
							$croppedHeight = $sourceWidth;
							$resizedWidth = $resizedHeight;
						}

						$cropped = $profilepicture->crop($croppedWidth, $croppedHeight, $left, $top, true);
						$resized = $cropped->resize($resizedWidth, $resizedHeight, true, JImage::SCALE_OUTSIDE);
						$resized->toFile(constant('PROFILEPICTURE_PATH_'.$size).$pp_filename);

						$savedNewProfilePicture = true;
					}
					else
					{
						$resized = $profilepicture->resize($size, $size, true, JImage::SCALE_INSIDE);
						$resized->toFile(constant('PROFILEPICTURE_PATH_'.$size).$pp_filename);

						$savedNewProfilePicture = true;
					}
				}
			}
		}

		
		// Remove profile picture if an existing profile picture is 
		// checked for removal or a new picture has been uploaded
		// replacing the existing picture.
		if( 
			isset($userId)
			&&
			(
				!empty($post['profilepicture']['file']['remove'])
				||
				$savedNewProfilePicture
			)
		) 
		{
			$this->removeProfilePicture($userId);
		}

		if( $userId && $savedNewProfilePicture )
		{
			try
			{
				$db = JFactory::getDbo();
				$query	= $db->getQuery(true);

				$query->insert('#__user_profiles')
					->columns('user_id, profile_key, profile_value, ordering')
					->values(
						$userId.', '
						.$db->quote(plgUserProfilePicture::PROFILE_KEY).', '
						.$db->quote($pp_filename).', '
						.' 1');
				$db->setQuery($query);

				if (!$db->query())
				{
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
	 * @param    array   $user    Holds the user data
	 * @param    boolean $success True if user was succesfully stored in the database
	 * @param    string  $msg     Message
	 *
	 * @return bool
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
	 * @param    int $userId User ID
	 *
	 * @throws Exception
	 * @return    boolean    true if successfully removed profile picture.
	 *            false if failed or there is no profile picture
	 *            to remove.
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
				
				if( !empty($profile_value) )
				{
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

					if (!$db->query())
					{
						throw new Exception($db->getErrorMsg());
					}					
				}
				else
				{
					return false;
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
