#!/usr/bin/env bash

if [ -z "${PROJECT_PATH}" ]; then
    echo "PROJECT_PATH is not set. Aborting..."
    exit 1
fi
cd ${PROJECT_PATH}

DEPENDENCIES_PATH=${DEPENDENCIES_PATH:-"${PROJECT_PATH}/git_dependencies"}
CLONE_BASE_URL=${CLONE_BASE_URL:-"https://github.com"}
REPO_NAME=$1
DIR_NAME=${2:-"${REPO_NAME}"}
BRANCH_NAME=${3:-"master"}

# Recreate build path if does not exist
mkdir -p ${DEPENDENCIES_PATH}
cd ${DEPENDENCIES_PATH}

# Clone original data source from the remote
if [ -d "${DEPENDENCIES_PATH}/${DIR_NAME}/.git" ]; then
    cd ${DIR_NAME}
    git fetch -a
    git checkout ${BRANCH_NAME}
    git pull
else
    rm -rf ${DIR_NAME} # in case not found or not a git repo
    git clone ${CLONE_BASE_URL}/${REPO_NAME}.git ${DIR_NAME} --branch ${BRANCH_NAME}
fi