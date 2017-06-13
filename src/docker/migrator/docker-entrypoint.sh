#!/usr/bin/env bash
set -e

if [ -z "${PROJECT_PATH}" ]; then
    echo "PROJECT_PATH is not set. Aborting..."
    exit 1
fi
cd ${PROJECT_PATH}

__fn_show_help() {
    echo "--------------------"
    echo "## veekun-pokedex-migrations ##"
    echo "--------------------"
    echo "List of commands:"
    echo "--------------------"
    echo "help (default)"
    echo "migrate: Runs all migration scripts."
    echo "dump: Exports the current state of the SQLite DB to CSV."
    echo "--------------------"
}

case "$1" in
   "") __fn_show_help
   ;;
   "help") __fn_show_help
   ;;
   "migrate") php src/scripts/migrate.php
   ;;
   "dump")  php src/scripts/db2csv.php
   ;;
   *) exec ${@}
   ;;
esac
