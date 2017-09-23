#!/usr/bin/env bash
set -e

if [ -z "${PROJECT_PATH}" ]; then
    echo "PROJECT_PATH is not set. Aborting..."
    exit 1
fi

cd ${PROJECT_PATH}

__fn_setup() {
    if [ ! -d "${PROJECT_PATH}/vendor/veekun-pokedex/pokedex" ] || [ ! -d "${PROJECT_PATH}/vendor" ] ; then
        ./src/setup/git-require.sh veekun/pokedex veekun-pokedex master
        ./src/setup/git-require.sh Zarel/Pokemon-Showdown pokemon-showdown master
        composer install
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
