#!/usr/bin/env bash
set -e

cd /project
source ./src/env.sh

if [ ! -d "${PROJECT_PATH}/vendor/doctrine/dbal" ] ; then
    echo "Installing missing vendor packages..."
    composer install
fi

__fn_help() {
    echo "--------------------"
    echo "List of commands:"
    echo "--------------------"
    echo "help (default)"
    echo "migrate: Runs all migration scripts."
    echo "export: Runs all export scripts and builds the dist (distributable) files."
    echo "sprites: Generates a CSS sprite sheet out of the icon images."
    echo "--------------------"
}

case "$1" in
   "") __fn_help
   ;;
   "help") __fn_help
   ;;
   "migrate") php src/tasks/migrate.php
   ;;
   "export") php src/tasks/export.php
   ;;
   "sprites") php src/tasks/sprites.php
   ;;
   *) exec ${@}
   ;;
esac
