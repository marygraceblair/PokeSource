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

mkdir -p ${DIST_PATH}/csv ${DIST_PATH}/db ${DIST_PATH}/proto ${PROJECT_PATH}/build

if [ ! -d "${PROJECT_PATH}/vendor/doctrine/dbal" ] ; then
    echo "Installing missing vendor packages..."
    composer install
fi

__fn_show_help() {
    echo "--------------------"
    echo "## data ##"
    echo "--------------------"
    echo "List of commands:"
    echo "--------------------"
    echo "help (default)"
    echo "start: Starts serving the API server. You can use also 'serve'."
    echo "migrate: Runs all migration scripts."
    echo "dump: Exports the current state of the SQLite DB to the different supported formats like CSV."
    echo "--------------------"
}

__fn_run_migrations(){
    php src/data/create-migrations-table.php
    php src/data/run-migrations.php
}

__fn_dump_db(){
    rm -rf ${DIST_PATH}/csv/* ${DIST_PATH}/db/* ${DIST_PATH}/proto/*
    php src/data/export-db.php
    php src/data/create-db-bundle.php
}

__fn_start_api(){
    APP_ENV=${APP_ENV:-"development"}
    APP_HOST=${APP_HOST:-"0.0.0.0"}
    APP_PORT=${APP_PORT:-$(( ((RANDOM<<15)|RANDOM) % 919 + 8081 ))} # Default: random port will be from 8081 to 8999
    APP_PUBLIC_PATH=${APP_PUBLIC_PATH:-"./src/data/api/"}

    echo "Starting API from '${APP_PUBLIC_PATH}' as http://${APP_HOST}:${APP_PORT} ..."

    php -S ${APP_HOST}:${APP_PORT} -t ${APP_PUBLIC_PATH}
}

case "$1" in
   "") __fn_show_help
   ;;
   "help") __fn_show_help
   ;;
   "serve") __fn_start_api
   ;;
   "start") __fn_start_api
   ;;
   "migrate") __fn_run_migrations
   ;;
   "dump") __fn_dump_db
   ;;
   *) exec ${@}
   ;;
esac
