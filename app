#!/usr/bin/env bash

# Run setup if the .sqlite DB is not found
if [ "$1" == "setup" ] || [ ! -f "./build/pokedex.sqlite" ] ; then
    docker-compose run --rm importer_showdown import
    docker-compose run --rm importer_veekun import
    docker-compose run --rm phpfpm migrate
    exit
fi

# Starts the API server
if [ "$1" == "start" ] || [ "$1" == "serve" ] ; then
    docker-compose up nginx
    exit
fi

if [ "$1" == "ssh" ] ; then
    docker-compose run --rm phpfpm /bin/bash
else
    docker-compose run --rm phpfpm ${@}
fi