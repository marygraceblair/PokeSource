#!/usr/bin/env bash

# TODO: add help section here

# Create nginx cache dir, just in case
mkdir -p ./build/cache/nginx

# Run setup if the .sqlite DB is not found
if [ "$1" == "setup" ] || [ ! -f "./build/pokedex.sqlite" ] ; then
    echo "Setting up the project importing and migrating the DB..."
    docker-compose run --rm importer_showdown import
    docker-compose run --rm importer_veekun import
    docker-compose run --rm phpfpm migrate
    exit
fi

# Clear nginx cache dir
if [ "$1" == "clear-cache" ] || [ "$1" == "clearcache" ] ; then
    rm -rf ./build/cache/nginx
    # Create nginx cache dir
    mkdir -p ./build/cache/nginx
    echo "API cache cleared."
    exit
fi

# Starts the API server
if [ "$1" == "start" ] || [ "$1" == "serve" ] ; then
    echo "Starting API server..."
    docker-compose up nginx
    exit
fi

if [ "$1" == "ssh" ] ; then
    echo "Entering instance of phpfpm..."
    docker-compose run --rm phpfpm /bin/bash
else
    echo "Running command from phpfpm..."
    docker-compose run --rm phpfpm ${@}
fi