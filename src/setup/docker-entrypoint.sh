#!/usr/bin/env bash
set -e

if [ -z "${DIST_PATH}" ]; then
    echo "DIST_PATH is not set. Aborting..."
    exit 1
fi

if [ -z "${PROJECT_PATH}" ]; then
    echo "PROJECT_PATH is not set. Aborting..."
    exit 1
fi

cd ${PROJECT_PATH}

__fn_setup() {
    mkdir -p ${DIST_PATH}/csv ${DIST_PATH}/db ${DIST_PATH}/proto ${PROJECT_PATH}/build

    if [ ! -d "${PROJECT_PATH}/vendor/doctrine" ] ; then
        composer install
    fi
    if [ ! -d "${PROJECT_PATH}/vendor/veekun-pokedex/pokedex" ] ; then
        ./src/setup/git-require.sh veekun/pokedex veekun-pokedex master
    fi
    if [ ! -d "${PROJECT_PATH}/vendor/pokemon-showdown/data" ] ; then
        ./src/setup/git-require.sh Zarel/Pokemon-Showdown pokemon-showdown master
    fi
}

case "$1" in
   "") __fn_setup
   ;;
   "setup") __fn_setup
   ;;
   *) exec ${@}
   ;;
esac
