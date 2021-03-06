#!/usr/bin/env bash
set -e

cd /project
source ./src/env.sh

export REPO_PATH=${PROJECT_PATH}/vendor/pokemon-showdown

if [ ! -d "${REPO_PATH}/data" ] ; then
    git_require Zarel/Pokemon-Showdown pokemon-showdown master
    cd $REPO_PATH
    git reset --hard $COMMIT_REF
    cd $PROJECT_PATH
fi

__fn_help() {
    echo "--------------------"
    echo "List of commands:"
    echo "--------------------"
    echo "help (default)"
    echo "import [@]: Imports all needed content from the Zarel/Pokemon-Showdown project."
    echo "--------------------"
}

__fn_import() {
    rm -rf ${BUILD_PATH}/showdown
    ./src/importers/showdown/import.sh ${@:2}
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
