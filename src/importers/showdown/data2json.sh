#!/usr/bin/env bash

if [ -z "${PROJECT_PATH}" ]; then
    echo "PROJECT_PATH is not set. Aborting..."
    exit 1
fi
if [ -z "${SOURCES_PATH}" ]; then
    echo "SOURCES_PATH is not set. Aborting..."
    exit 1
fi
if [ -z "${BUILD_PATH}" ]; then
    echo "BUILD_PATH is not set. Aborting..."
    exit 1
fi
cd ${PROJECT_PATH}

OUTPUT_PATH="${BUILD_PATH}/pokemon-showdown"

rm -rf $OUTPUT_PATH
mkdir -p $OUTPUT_PATH

__convert_json(){
    f=$1

    JSON_FILE=${f/$SOURCES_PATH/$OUTPUT_PATH}

    mkdir -p $(dirname $JSON_FILE)

    echo -n "Generating ${JSON_FILE}... "
    cat $f | jq ${@:2} . > ${JSON_FILE}
    echo "DONE."
}

__convert_js_object(){
    f=$1

    JSON_FILE=${f/$SOURCES_PATH/$OUTPUT_PATH}
    JSON_FILE=${JSON_FILE/.js/.json}

    mkdir -p $(dirname $JSON_FILE)

    echo -n "Generating ${JSON_FILE}... "
    node ./src/pokemon-showdown/module2json.js $f | jq ${@:2} . > ${JSON_FILE}
    echo "DONE."
}

for f in ${SOURCES_PATH}/data/*.json; do
    if [ ! -f "$f" ] ; then
        break
    fi
    __convert_json $f ${@}
done

for f in ${SOURCES_PATH}/data/*.js; do
    if [ ! -f "$f" ] ; then
        break
    fi
    if [[ $f == *.json ]] ; then
        continue
    fi
    __convert_js_object $f ${@}
done

for f in ${SOURCES_PATH}/mods/**/*.json; do
    if [ ! -f "$f" ] ; then
        break
    fi
    __convert_json $f ${@}
done

for f in ${SOURCES_PATH}/mods/**/*.js; do
    if [ ! -f "$f" ] ; then
        break
    fi
    if [[ $f == *.json ]] ; then
        continue
    fi
    __convert_js_object $f ${@}
done