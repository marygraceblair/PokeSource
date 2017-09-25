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

# Include other projects, that only need cloning and no container:
if [ ! -d "${PROJECT_PATH}/vendor/pokesprite" ] ; then
    ./src/git-require.sh msikma/pokesprite pokesprite master
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
    echo "export: Exports the current state of the SQLite DB to the different supported formats like CSV."
    echo "--------------------"
}

__fn_run_migrations(){
    php src/setup/create-migrations-table.php
    php src/setup/run-migrations.php
}

__fn_export_all(){
    rm -rf ${DIST_PATH}/*
    php src/exporters/export-db-as-csv.php
    php src/exporters/export-db-as-protobuf.php
    php src/exporters/create-db-bundle.php
}

case "$1" in
   "") __fn_show_help
   ;;
   "help") __fn_show_help
   ;;
   "migrate") __fn_run_migrations
   ;;
   "export") __fn_export_all
   ;;
   *) exec ${@}
   ;;
esac
