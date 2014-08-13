#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
BASE=${DIR}/dist/build
PLG=plg_user_profilepicture
LIB=lib_mosets_profilepicture
if [ -d "dist" ]; then
	rm -rf dist
fi

mkdir -p ${BASE}/${PLG}-1.1.0
cp -r plugins/user/profilepicture/* ${BASE}/${PLG}-1.1.0
cp -r media ${BASE}/${PLG}-1.1.0/

mkdir -p ${BASE}/${LIB}-1.0.0/profilepicture
cp libraries/mosets/profilepicture/profilepicture.xml ${BASE}/${LIB}-1.0.0/
cp libraries/mosets/profilepicture/profilepicture.php ${BASE}/${LIB}-1.0.0/profilepicture/
cp -r language ${BASE}/${LIB}-1.0.0/

cd ${BASE}/${PLG}-1.1.0
zip -r ../${PLG}-1.1.0.zip *
cd ${BASE}/${LIB}-1.0.0
zip -r ../${LIB}-1.0.0.zip *
cd ${BASE}
cp ${DIR}/pack/pkg_profilepicture.xml ${BASE}
zip ../pkg_profilepicture-1.1.0.zip plg_user_profilepicture-1.1.0.zip lib_mosets_profilepicture-1.0.0.zip pkg_profilepicture.xml
cd ..

