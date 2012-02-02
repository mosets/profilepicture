Profile Picture
===============

Profile Picture is a Joomla package that enables your users to upload their profile picture to your Joomla website. It includes the following extensions: 

+ **Profile Picture User Plugin** -
This is the main plugin that allows your users to upload, remove and change their profile picture. Administrator too can manage these pictures in Joomla's Users Manager.

+ **Profile Picture Library** - 
Profile Picture library provides API for third party developers to access and display users' profile picture.

How to use
----------
Here's an example code that displays the currently logged in user's profile picture.

	jimport('mosets.profilepicture.profilepicture');
	
	$user = JFactory::getUser();
	$profilepicture = new ProfilePicture($user->get('id'));
	
	if( $profilepicture->exists() )
	{
		echo '<img src="'.$profilepicture->getURL().'" alt="'.$user->get('name').'" />';
	}

Requirements
------------

Joomla 2.5 and GD image library support for PHP.

Author
-------

**CY Lee**

+ http://twitter.com/cheryeong
+ http://github.com/cheryeong

Copyright and license
---------------------

Copyright (c) 2012 Mosets Consulting

Licensed under the MIT License.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.