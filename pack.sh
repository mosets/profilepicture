#!/bin/bash

MONTHS=(January February March April May June July August September October November December)
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
BASE=${DIR}/dist/build
PLG=plg_user_profilepicture
PLG_CFG=plugins/user/profilepicture/profilepicture.xml
PLG_VERSION=$(grep -oPm1 "(?<=<version>)[^<]+" ${PLG_CFG})
LIB=lib_mosets_profilepicture
LIB_CFG=libraries/mosets/profilepicture/profilepicture.xml
LIB_VERSION=$(grep -oPm1 "(?<=<version>)[^<]+" ${LIB_CFG})
if [ -d "dist" ]; then
	rm -rf dist
fi

mkdir -p ${BASE}/${PLG}-${PLG_VERSION}
cp -r plugins/user/profilepicture/* ${BASE}/${PLG}-${PLG_VERSION}
cp -r media ${BASE}/${PLG}-${PLG_VERSION}/

mkdir -p ${BASE}/${LIB}-${LIB_VERSION}/profilepicture
cp ${LIB_CFG} ${BASE}/${LIB}-${LIB_VERSION}/
cp libraries/mosets/profilepicture/profilepicture.php ${BASE}/${LIB}-${LIB_VERSION}/profilepicture/
cp -r language ${BASE}/${LIB}-${LIB_VERSION}/

cd ${BASE}/${PLG}-${PLG_VERSION}
zip -r ../${PLG}-${PLG_VERSION}.zip *
cd ${BASE}/${LIB}-${LIB_VERSION}
zip -r ../${LIB}-${LIB_VERSION}.zip *
cd ${BASE}
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" > ${BASE}/pkg_profilepicture.xml
echo "<extension type=\"package\" version=\"${PLG_VERSION}\" method=\"upgrade\">" >> ${BASE}/pkg_profilepicture.xml
echo "	<name>Profile Picture Package</name>" >> ${BASE}/pkg_profilepicture.xml
echo "	<packagename>profilepicture</packagename>" >> ${BASE}/pkg_profilepicture.xml
echo "	<version>${PLG_VERSION}</version>" >> ${BASE}/pkg_profilepicture.xml
echo "	<author>Mosets Consulting</author>" >> ${BASE}/pkg_profilepicture.xml
echo "	<creationDate>${MONTHS[$(date +'%m')]} $(date +'%Y')</creationDate>" >> ${BASE}/pkg_profilepicture.xml
echo "	<url>http://www.mosets.com</url>" >> ${BASE}/pkg_profilepicture.xml
echo "	<packager>CY Lee</packager>" >> ${BASE}/pkg_profilepicture.xml
echo "	<packageurl>http://www.mosets.com</packageurl>" >> ${BASE}/pkg_profilepicture.xml
echo "	<description>Profile Picture Package</description>" >> ${BASE}/pkg_profilepicture.xml
echo "	<update>http://update.mosets.com/packages/profilepicture</update>" >> ${BASE}/pkg_profilepicture.xml
echo "	<files folder=\".\">" >> ${BASE}/pkg_profilepicture.xml
echo "		<file type=\"library\" id=\"lib_profilepicture\">lib_mosets_profilepicture-${LIB_VERSION}.zip</file>" >> ${BASE}/pkg_profilepicture.xml
echo "		<file type=\"plugin\" id=\"profilepicture\" group=\"user\">plg_user_profilepicture-${PLG_VERSION}.zip</file>" >> ${BASE}/pkg_profilepicture.xml
echo "	</files>" >> ${BASE}/pkg_profilepicture.xml
echo "</extension>" >> ${BASE}/pkg_profilepicture.xml
zip ../pkg_profilepicture-${PLG_VERSION}.zip plg_user_profilepicture-${PLG_VERSION}.zip lib_mosets_profilepicture-${LIB_VERSION}.zip pkg_profilepicture.xml
cd ..

