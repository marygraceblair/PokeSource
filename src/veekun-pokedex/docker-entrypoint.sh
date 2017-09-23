#!/usr/bin/env bash
set -e

if [ -z "${SOURCES_PATH}" ]; then
    echo "SOURCES_PATH is not set. Aborting..."
    exit 1
fi

cd ${SOURCES_PATH}

if [ ! -f "${SOURCES_PATH}/bin/python" ] || [ ! -f "${SOURCES_PATH}/bin/veekun-pokedex" ] ; then
    echo "Building the bin/veekun-pokedex executable ..."
    virtualenv $SOURCES_PATH --python=python2
    bin/python setup.py develop
fi

case "$1" in
   "") bin/pokedex help
   ;;
   "exec") exec ${@:2}
   ;;
   *) bin/pokedex $@
   ;;
esac
