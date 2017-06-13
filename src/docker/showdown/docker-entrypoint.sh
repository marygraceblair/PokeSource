#!/usr/bin/env bash
set -e

if [ -z "${PROJECT_PATH}" ]; then
    echo "PROJECT_PATH is not set. Aborting..."
    exit 1
fi
cd ${PROJECT_PATH}

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

case "$1" in
   "") __fn_show_help
   ;;
   "help") __fn_show_help
   ;;
   "export") ./src/scripts/showdown2json.sh ${@:2}
   ;;
   *) exec ${@}
   ;;
esac
