#!/usr/bin/env bash
set -e

export PROJECT_PATH=${PROJECT_PATH:-"/project"}

if [ ! -d "${PROJECT_PATH}" ]; then
    echo "PROJECT_PATH directory '${PROJECT_PATH}' does not exist. Aborting..."
    exit 1
fi

export BUILD_PATH=${PROJECT_PATH}/build
export DIST_PATH=${PROJECT_PATH}/dist
export SRC_PATH=${PROJECT_PATH}/src
export VENDOR_PATH=${PROJECT_PATH}/vendor

export DB_FILE=${BUILD_PATH}/pokedex.sqlite

export API_HOST="localhost:8151"
export INTERNAL_API_HOST="web:80"

if [ ! -d "${BUILD_PATH}" ] ; then
    mkdir -p ${BUILD_PATH}
fi

if [ ! -d "${BUILD_PATH}/cache/nginx" ] ; then
    mkdir -p ${BUILD_PATH}/cache/nginx
fi

if [ ! -d "${DIST_PATH}" ] ; then
    mkdir -p ${DIST_PATH}
fi

cd ${PROJECT_PATH}

git_require() {
    DEPENDENCIES_PATH=${VENDOR_PATH:-"${PROJECT_PATH}/vendor"}
    CLONE_BASE_URL=${CLONE_BASE_URL:-"https://github.com"}
    REPO_NAME=$1
    DIR_NAME=${2:-"${REPO_NAME}"}
    BRANCH_NAME=${3:-"master"}

    # Recreate build path if does not exist
    mkdir -p ${DEPENDENCIES_PATH}
    cd ${DEPENDENCIES_PATH}

    # Pull or clone original data source from the remote
    if [ -d "${DEPENDENCIES_PATH}/${DIR_NAME}/.git" ]; then
        cd ${DIR_NAME}
        git fetch -a
        git checkout ${BRANCH_NAME}
        git pull
    else
        rm -rf ${DIR_NAME} # in case not found or not a git repo
        git clone ${CLONE_BASE_URL}/${REPO_NAME}.git ${DIR_NAME} --branch ${BRANCH_NAME}
    fi
}