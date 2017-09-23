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

__fn_show_help() {
    echo "--------------------"
    echo "## veekun-pokedex-data data-transformer ##"
    echo "--------------------"
    echo "List of commands:"
    echo "--------------------"
    echo "help (default)"
    echo "migrate: Runs all migration scripts."
    echo "dump: Exports the current state of the SQLite DB to CSV."
    echo "--------------------"
}

__fn_run_migrations(){
    php src/data-transformer/create-migrations-table.php
    php src/data-transformer/run-migrations.php
}

__fn_dump_db(){
    rm -rf ${DIST_PATH}/csv/*.csv
    php src/data-transformer/export-db-csv.php
    # Remove Conquest game data (is not main series)
    rm -rf ${DIST_PATH}/csv/conquest*.csv
    rm -rf ${DIST_PATH}/db/*
    php src/data-transformer/create-db-bundle.php
}

case "$1" in
   "") __fn_show_help
   ;;
   "help") __fn_show_help
   ;;
   "migrate") __fn_run_migrations
   ;;
   "dump") __fn_dump_db
   ;;
   *) exec ${@}
   ;;
esac
