#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
BASE=${DIR}/dist/build
PLG=plg_user_profilepicture
PLG_CFG=plugins/user/profilepicture/profilepicture.xml
LIB=lib_profilepicture
LIB_CFG=libraries/profilepicture/profilepicture.xml

SRCPATH=${DIR}/dist/src
mkdir -p $SRCPATH
git archive --format=tar HEAD | (cd $SRCPATH && tar xf -)
cd $SRCPATH

# Plugin version
PLG_VERSION=`xpath $PLG_CFG extension/version`
PLG_VERSION=${PLG_VERSION#<version>}
PLG_VERSION=${PLG_VERSION%</version>}
printf %s\\n "Plugin version is $PLG_VERSION"

# Library version
LIB_VERSION=`xpath $LIB_CFG extension/version`
LIB_VERSION=${LIB_VERSION#<version>}
LIB_VERSION=${LIB_VERSION%</version>}
printf %s\\n "Library version is $LIB_VERSION"

if [ -d "dist" ]; then
	rm -rf dist
fi

# Zip plg_user_profilepicture plugin
mkdir -p ${BASE}/${PLG}-${PLG_VERSION}
cp -r plugins/user/profilepicture/* ${BASE}/${PLG}-${PLG_VERSION}
cp -r media ${BASE}/${PLG}-${PLG_VERSION}/

cd ${BASE}/${PLG}-${PLG_VERSION}
zip -r ../${PLG}-${PLG_VERSION}.zip *

# Zip lib_profilepicture library
mkdir -p ${BASE}/${LIB}-${LIB_VERSION}
cp $SRCPATH/libraries/profilepicture/* ${BASE}/${LIB}-${LIB_VERSION}/
cp -r $SRCPATH/language/en-GB/en-GB.lib_profilepicture.sys.ini ${BASE}/${LIB}-${LIB_VERSION}/

cd ${BASE}/${LIB}-${LIB_VERSION}
zip -r ../${LIB}-${LIB_VERSION}.zip *

cd ${BASE}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" > ${BASE}/pkg_profilepicture.xml
echo "<extension type=\"package\" version=\"${PLG_VERSION}\" method=\"upgrade\">" >> ${BASE}/pkg_profilepicture.xml
echo "	<name>Profile Picture Package</name>" >> ${BASE}/pkg_profilepicture.xml
echo "	<packagename>profilepicture</packagename>" >> ${BASE}/pkg_profilepicture.xml
echo "	<version>${PLG_VERSION}</version>" >> ${BASE}/pkg_profilepicture.xml
echo "	<author>Mosets Consulting</author>" >> ${BASE}/pkg_profilepicture.xml
echo "	<creationDate>$(date +%B) $(date +'%Y')</creationDate>" >> ${BASE}/pkg_profilepicture.xml
echo "	<url>http://www.mosets.com</url>" >> ${BASE}/pkg_profilepicture.xml
echo "	<packager>Mosets Consulting</packager>" >> ${BASE}/pkg_profilepicture.xml
echo "	<packageurl>http://www.mosets.com</packageurl>" >> ${BASE}/pkg_profilepicture.xml
echo "	<description>Profile Picture Package</description>" >> ${BASE}/pkg_profilepicture.xml
echo "	<update>http://update.mosets.com/packages/profilepicture</update>" >> ${BASE}/pkg_profilepicture.xml
echo "	<files folder=\".\">" >> ${BASE}/pkg_profilepicture.xml
echo "		<file type=\"library\" id=\"lib_profilepicture\">lib_profilepicture-${LIB_VERSION}.zip</file>" >> ${BASE}/pkg_profilepicture.xml
echo "		<file type=\"plugin\" id=\"profilepicture\" group=\"user\">plg_user_profilepicture-${PLG_VERSION}.zip</file>" >> ${BASE}/pkg_profilepicture.xml
echo "	</files>" >> ${BASE}/pkg_profilepicture.xml
echo "</extension>" >> ${BASE}/pkg_profilepicture.xml

zip ../pkg_profilepicture-${PLG_VERSION}.zip plg_user_profilepicture-${PLG_VERSION}.zip lib_profilepicture-${LIB_VERSION}.zip pkg_profilepicture.xml
cd ..

printf \\n%s\\n\\n "Your package was successfuly packaged here: ${BASE}/pkg_profilepicture-${PLG_VERSION}.zip"
