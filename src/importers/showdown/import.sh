#!/usr/bin/env bash

if [ -z "${PROJECT_PATH}" ]; then
    echo "PROJECT_PATH is not set. Aborting..."
    exit 1
fi
if [ -z "${REPO_PATH}" ]; then
    echo "REPO_PATH is not set. Aborting..."
    exit 1
fi
if [ -z "${BUILD_PATH}" ]; then
    echo "BUILD_PATH is not set. Aborting..."
    exit 1
fi
cd ${PROJECT_PATH}

OUTPUT_PATH="${BUILD_PATH}/showdown"

rm -rf $OUTPUT_PATH
mkdir -p $OUTPUT_PATH

__convert_json(){
    f=$1

    JSON_FILE=${f/$REPO_PATH/$OUTPUT_PATH}

    mkdir -p $(dirname $JSON_FILE)

    echo -n "Generating ${JSON_FILE}... "
    cat $f | jq ${@:2} . > ${JSON_FILE}
    echo "DONE."
}

__convert_js_object(){
    f=$1

    JSON_FILE=${f/$REPO_PATH/$OUTPUT_PATH}
    JSON_FILE=${JSON_FILE/.js/.json}

    mkdir -p $(dirname $JSON_FILE)

    echo -n "Generating ${JSON_FILE}... "
    node ${SRC_PATH}/importers/showdown/convert.js $f | jq ${@:2} . > ${JSON_FILE}
    echo "DONE."
}

for f in ${REPO_PATH}/data/*.json; do
    if [ ! -f "$f" ] ; then
        break
    fi
    __convert_json $f ${@}
done

for f in ${REPO_PATH}/data/*.js; do
    if [ ! -f "$f" ] ; then
        break
    fi
    if [[ $f == *.json ]] ; then
        continue
    fi
    __convert_js_object $f ${@}
done

for f in ${REPO_PATH}/mods/**/*.json; do
    if [ ! -f "$f" ] ; then
        break
    fi
    __convert_json $f ${@}
done

for f in ${REPO_PATH}/mods/**/*.js; do
    if [ ! -f "$f" ] ; then
        break
    fi
    if [[ $f == *.json ]] ; then
        continue
    fi
    __convert_js_object $f ${@}
done