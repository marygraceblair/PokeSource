#!/usr/bin/env bash

if [ "$1" == "setup" ] ; then
    rm -rf ./vendor/veekun-pokedex/pokedex/data/pokedex.sqlite ./build
    mkdir -p ./build
    docker-compose run --rm veekun_pokedex setup
    docker-compose run --rm pokemon_showdown export
    cp ./vendor/veekun-pokedex/pokedex/data/pokedex.sqlite ./build/pokedex.sqlite
    docker-compose run --rm data migrate
    exit
fi

if [ "$1" == "start" ] || [ "$1" == "serve" ] ; then
    docker-compose up data
    exit
fi

if [ "$1" == "ssh" ] ; then
    docker-compose run --rm data /bin/bash
else
    docker-compose run --rm data ${@}
fi