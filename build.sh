#!/bin/bash
ROOT_PATH=`pwd`

TAG=$([[ -d .git && -n $(git tag --points-at HEAD) ]] && echo $(git tag --points-at HEAD) || echo $(git rev-parse --short HEAD ))
if [[ -n "$1" ]]; then
  TAG=$1
fi

SRC_PATH=${ROOT_PATH}
BUILD_PATH="${ROOT_PATH}/build"
DST_PATH="${BUILD_PATH}/apirone"
ARC_PATH=${ROOT_PATH}/apirone.${TAG}.zip

rm -rf ${BUILD_PATH}

paths=( $(grep -v '#' ${SRC_PATH}/build.list) )

for val in ${paths[@]}
do
    src=${SRC_PATH}/${val}
    dst=${DST_PATH}/${val}
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
        dst=${DST_PATH}/${val}
        mkdir -p `echo ${dst} | sed s/\\\/[^\\\/]*$//`
        cp -R ${src} ${dst}

        src=""
    fi
done

# Run php-cs-fixer
mkdir -p tmp
composer require -q -d tmp friendsofphp/php-cs-fixer
./tmp/vendor/bin/php-cs-fixer fix ${DST_PATH}
rm -rf ./tmp

rm -f "${ARC_PATH}"
cd "${BUILD_PATH}"
zip -qr "${ARC_PATH}" ./apirone
