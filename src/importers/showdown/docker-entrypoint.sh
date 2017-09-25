#!/usr/bin/env bash
set -e

if [ -z "${PROJECT_PATH}" ]; then
    echo "PROJECT_PATH is not set. Aborting..."
    exit 1
fi
cd ${PROJECT_PATH}

if [ ! -d "${PROJECT_PATH}/vendor/pokemon-showdown/data" ] ; then
    ./src/git-require.sh Zarel/Pokemon-Showdown pokemon-showdown master
fi

__fn_show_help() {
    echo "--------------------"
    echo "## pokemon-showdown ##"
    echo "--------------------"
    echo "List of commands:"
    echo "--------------------"
    echo "help (default)"
    echo "export [@]: Converts and exports the Showdown data to JSON. Valid arguments: any jq options."
    echo "--------------------"
}

__fn_import() {
    rm -rf ${PROJECT_PATH}/build/pokemon-showdown
    ./src/pokemon-showdown/data2json.sh ${@:2}
    exit
}

case "$1" in
   "") __fn_show_help
   ;;
   "help") __fn_show_help
   ;;
   "import") __fn_import
   ;;
   *) exec ${@}
   ;;
esac
