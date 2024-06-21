![55784cf84644c_resizeDown1200px525px16](https://github.com/mosets/profilepicture/assets/1192565/60dc0934-fb54-443a-b003-5c552ebe0033)

Profile Picture
===============

Profile Picture is a Joomla package that enables your users to upload their profile picture to your Joomla website. It includes the following extensions: 

+ **Profile Picture User Plugin** -
This is the main plugin that allows your users to upload, remove and change their profile picture. Administrator too can manage these pictures in Joomla's Users Manager.

+ **Profile Picture Library** - 
Profile Picture library provides API for third party developers to access and display users' profile picture.

How to use
----------
It's super easy to use profile picture in your existing codes. Make sure to load the ProfilePicture library first:

```php
jimport('profilepicture.profilepicture');
```

Create a ProfilePicture instance and pass the user ID:

```php
$profilepicture = new ProfilePicture(1);
```

Use the **toHTML** method to output the IMG element for the user's profile picture. If profile picture for the user does not exists, the filler image will be shown:

```php
echo $profilepicture->toHTML();
```

There are 3 sizes defined as constants in Profile Picture:
+ **PROFILEPICTURE_SIZE_ORIGINAL**
+ **PROFILEPICTURE_SIZE_50**
+ **PROFILEPICTURE_SIZE_200**
	
By default, **toHTML** will output using **PROFILEPICTURE_SIZE_200**. You may specify the profile picture size by passing the size constant to **toHTML** method:

```php
echo $profilepicture->toHTML(PROFILEPICTURE_SIZE_50);
```

The toHTML method also allows you to specify the ALT attribute and additional attributes for the IMG element:

```php
echo $profilepicture->toHTML(PROFILEPICTURE_SIZE_50, 'Lee', ['class' => 'profile', 'id' => 'lee-profile-picture']);
```

Here's a full example that displays the currently logged in user's profile picture.

```php
jimport('profilepicture.profilepicture');

$user = JFactory::getUser();

$profilepicture = new ProfilePicture($user->get('id'));

echo $profilepicture->toHTML(PROFILEPICTURE_SIZE_200, $user->get('name'));
```

The library also includes the following methods: **getFillerURL**, **getURL**, **getPath** and **exists**. You can pass the size constant to refer to a specific size, otherwise it will defaults to **PROFILEPICTURE_SIZE_200**.

Requirements
------------

Joomla 3.4 and GD image library support for PHP.

Author
-------

**CY Lee**

+ http://twitter.com/cheryeong
+ http://github.com/cheryeong

Copyright and license
---------------------

Copyright (c) 2012-present Mosets Consulting

Licensed under the MIT License.

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
