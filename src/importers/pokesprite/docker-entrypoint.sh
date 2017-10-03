#!/usr/bin/env bash
set -e

cd /project
source ./src/env.sh

export REPO_PATH=${VENDOR_PATH}/msikma-pokesprite/

if [ ! -d "${REPO_PATH}/icons/pokemon" ] ; then
    git_require msikma/pokesprite msikma-pokesprite master
    cd $REPO_PATH
    git reset --hard $COMMIT_REF
    cd $PROJECT_PATH
fi

__fn_help() {
    echo "--------------------"
    echo "List of commands:"
    echo "--------------------"
    echo "help (default)"
    echo "import [@]: Imports all needed content from the msikma/pokesprite project."
    echo "--------------------"
    exit
}

__fn_import() {
    php ./src/importers/pokesprite/import.php
    exit
}

case "$1" in
   "") __fn_help
   ;;
   "help") __fn_help
   ;;
   "import") __fn_import
   ;;
   *) exec ${@}
   ;;
esac
