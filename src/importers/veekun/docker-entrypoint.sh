#!/usr/bin/env bash
set -e

cd /project
source ./src/env.sh

export REPO_PATH=${PROJECT_PATH}/vendor/veekun-pokedex

if [ ! -d "${REPO_PATH}/pokedex" ] ; then
    git_require veekun/pokedex veekun-pokedex master
    cd $REPO_PATH
    git reset --hard $COMMIT_REF
    cd $PROJECT_PATH
fi

cd ${REPO_PATH}

if [ ! -f "${REPO_PATH}/bin/python" ] || [ ! -f "${REPO_PATH}/bin/pokedex" ] ; then
    echo "Building the bin/pokedex executable ..."
    virtualenv $REPO_PATH --python=python2
    bin/python setup.py develop
fi

__fn_import() {
    rm -rf ${REPO_PATH}/pokedex/data/pokedex.sqlite ${BUILD_PATH}/*.sqlite
    bin/pokedex status
    bin/pokedex setup $@
    cp ${REPO_PATH}/pokedex/data/pokedex.sqlite ${BUILD_PATH}/pokedex.sqlite
    exit
}

case "$1" in
   "") bin/pokedex help
   ;;
   "import") __fn_import ${@:2}
   ;;
   "setup") bin/pokedex $@
   ;;
   "status") bin/pokedex $@
   ;;
   "load") bin/pokedex $@
   ;;
   "dump") bin/pokedex $@
   ;;
   "reindex") bin/pokedex $@
   ;;
   "help") bin/pokedex $@
   ;;
   "lookup") bin/pokedex $@
   ;;
   *) exec ${@}
   ;;
esac
