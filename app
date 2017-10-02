#!/usr/bin/env bash
set -e

export PROJECT_PATH=./
source ./src/env.sh

# Run setup if the .sqlite DB is not found
if [ "$1" == "setup" ] || [ ! -f "${DB_FILE}" ] ; then
    echo "Setting up the project importing and migrating the DB..."
    docker-compose run --rm importer_veekun import
    docker-compose run --rm importer_showdown import
    docker-compose run --rm tasks migrate
    docker-compose run --rm importer_pokesprite import
    exit
fi

# Clear nginx cache dir
if [ "$1" == "clear-cache" ] || [ "$1" == "clearcache" ] ; then
    rm -rf ${BUILD_PATH}/cache/nginx/*
    echo "API cache cleared."
    exit
fi

if [ "$1" == "wipe" ] ; then
    rm -rf ${BUILD_PATH}/* ${DIST_PATH}/*
    mkdir -p ${BUILD_PATH}/cache/nginx
    echo "/build and /dist paths cleared."
    exit
fi

# Starts the API server
if [ "$1" == "start" ] || [ "$1" == "serve" ] ; then
    echo "Starting API server..."
    docker-compose up web
    exit
fi

if [ "$1" == "ssh" ] ; then
    echo "Entering in a new instance of a 'tasks' container..."
    docker-compose run --rm tasks /bin/bash
else
    echo "Running command from 'tasks'..."
    docker-compose run --rm tasks ${@}
fi