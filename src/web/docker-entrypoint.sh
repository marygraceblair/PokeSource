#!/usr/bin/env bash
set -e

cd /project
source ./src/env.sh

if [ ! -d "${VENDOR_PATH}/doctrine/dbal" ] ; then
    echo "Missing vendor packages. Please run composer install."
    exit 1;
fi

exec ${@}
