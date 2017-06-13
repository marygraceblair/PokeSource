#!/usr/bin/env bash

if [ "$1" == "init" ] ; then
    rm -f ./git_dependencies/veekun-pokedex/pokedex/data/pokedex.sqlite
    docker-compose run --rm pokedex setup
    docker-compose run --rm showdown export
else
    docker-compose run --rm migrator ${@}
fi