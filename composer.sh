#!/usr/bin/env bash

set -eu -o pipefail

cd "$(dirname "$0")";
WORKINGDIR=${PWD}

dockerCompose () {
    local OVERRIDE_YML
    OVERRIDE_YML=$([ -f "docker-compose.override.yml" ] && echo "docker-compose.override.yml" || echo "");
    COMPOSE_PROJECT_NAME="composer-script" docker-compose \
       -f docker-compose.yml \
       ${OVERRIDE_YML:+-f "${OVERRIDE_YML}"} \
       -f docker-compose.composer.yml \
       run --rm --no-deps \
           -u "${1}" \
           -w ${WORKINGDIR} \
           -e COMPOSER_HOME=${COMPOSER_HOME} \
           -e COMPOSER_MEMORY_LIMIT=2G \
           -v $HOME/.ssh/id_rsa:$HOME/id_rsa \
           -v ${WORKINGDIR}:${WORKINGDIR} \
           -v ${VOLUME}:${COMPOSER_HOME} \
           php bash -c "${@:2}"
}

run () {
   local VERSION=2.2.6
   local VOLUME_PREFIX="composer_version"
   local USER_ID="$(id -u)"
   local GROUP_ID="$(id -g)"
   local VOLUME="${VOLUME_PREFIX}_${VERSION}_${USER_ID}_${GROUP_ID}"
   local COMPOSER_HOME="/tmp/composer"
   local LOCATION="${COMPOSER_HOME}/composer"
   local FILENAME="$(basename "${LOCATION}")"
   local INSTALLER_URL="https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer"

   local INSTALL_COMMAND=""
   INSTALL_COMMAND="${INSTALL_COMMAND} mkdir -p ${COMPOSER_HOME}"
   INSTALL_COMMAND="${INSTALL_COMMAND} && curl -L ${INSTALLER_URL} | php -- --version=${VERSION} --filename=${FILENAME} --install-dir=${COMPOSER_HOME}"
   INSTALL_COMMAND="${INSTALL_COMMAND} && chown ${USER_ID}:${GROUP_ID} -R ${COMPOSER_HOME}"

   if [ -z "$(docker volume ls -q -f name=^${VOLUME}\$)" ]; then
        dockerCompose "0:0" "${INSTALL_COMMAND}" 1>/dev/null
   fi;

   dockerCompose "${USER_ID}:${GROUP_ID}" "${LOCATION} ${@}"
}

run "${@:-install}"
