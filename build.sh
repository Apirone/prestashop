#!/bin/bash
ROOT_PATH=$1
if [ -z "$ROOT_PATH" -o "$ROOT_PATH" = "." -o "$ROOT_PATH" = "./" -o ! -d "$ROOT_PATH" ]; then
    ROOT_PATH=`pwd`
fi

TAG=$2
if [ -z "$TAG" ]; then
    TAG="no.tag"
fi

SRC_PATH=${ROOT_PATH}
BUILD_PATH="${ROOT_PATH}/build"
ARC_PATH=${ROOT_PATH}/apirone.${TAG}.zip

rm -rf ${BUILD_PATH}

paths=( $(grep -v '#' ${SRC_PATH}/build.list) )

for val in ${paths[@]}
do
    src=${SRC_PATH}/${val}
    dst=${BUILD_PATH}/apirone/${val}
    mkdir -p `echo ${dst} | sed s/\\\/[^\\\/]*$//`
    cp -R ${src} ${dst}
done

paths=( $(grep -v '#' ${SRC_PATH}/build.map) )

src=""
for val in ${paths[@]}
do
    if [[ $src == "" ]]; then
        src=${SRC_PATH}/${val}
    else
        dst=${BUILD_PATH}/apirone/${val}
        mkdir -p `echo ${dst} | sed s/\\\/[^\\\/]*$//`
        cp -R ${src} ${dst}

        src=""
    fi
done

cd "${BUILD_PATH}"
rm -f "${ARC_PATH}"
zip -qr "${ARC_PATH}" ./apirone
