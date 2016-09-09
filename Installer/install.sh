#!/bin/bash

display_help() {
    echo 'Usage: sh <generator_project>/Installer/install.sh' \
        '[-h|--help]' \
        '-p|--project <project_path>' \
        '[-q|--quiet]' \
        '[-v|--verbose]' \
        '[-vv|--very-verbose]' \
        '[-vvv|--debug]' \
        '[-V|--version]'
    echo ''
    echo '    -h|--help:'
    echo '        Display this help screen.'
    echo ''
    echo '    -p|--project <project_path>:'
    echo '        Directory where the installer will create the project. This directory must not exists as it will be created.'
    echo ''
    echo '    -q|--quiet:'
    echo '        Do not display anything except errors.'
    echo ''
    echo '    -v|--verbose:'
    echo '        Activate the verbose mode.'
    echo ''
    echo '    -vv|--very-verbose:'
    echo '        Activate the very verbose mode, with more information than option -v.'
    echo ''
    echo '    -vvv|--debug:'
    echo '        Activate the ultra verbose mode (debug mode).'
    echo ''
    echo '    -V|--version:'
    echo '        Use this option to set the Symfony version of the created project. Default is 2.8.*.'
    echo ''
}

####
# 0. Parse all arguments
####
SCRIPT_NAME="$0"
PROJECT_NAME=''
SF_VERSION='2.8.*'
VERBOSE_MODE=''
QUIET=''

while [ $# -gt 0 ]; do
key="$1"

case ${key} in
    -h|--help)
    display_help
    exit 0;
    ;;
    -p|--project)
    PROJECT_NAME="$2"
    shift
    ;;
    -V|--version)
    SF_VERSION="$2"
    shift
    ;;
    -v|--verbose)
    VERBOSE_MODE='-v'
    ;;
    -vv|--very-verbose)
    VERBOSE_MODE='-vv'
    ;;
    -vvv|--debug)
    VERBOSE_MODE='-vvv'
    ;;
    -q|--quiet)
    VERBOSE_MODE=''
    QUIET='-q'
    ;;
    *)
    ;;
esac
shift
done

####
# 0 bis. Check mandatory options are here
####
#Validate the target project name
if [ '' = "$PROJECT_NAME" ]; then
    echo '# ERROR: you must set the project name where you want to create the new project.'
    display_help
    exit 10
fi

if [ -d "$PROJECT_NAME" ]; then
    echo '# ERROR: This project already exist.'
    display_help
    exit 11
fi

####
# 1. ACTION of creating the project.
####
if [ '' = "$QUIET" ]; then
    echo '####################################################'
    echo '### Installer: Create Symphony DDD ready project ###'
    echo '####################################################'
fi

# Move the composer.json if exists.
if [ -f ./composer.json ]; then
    mv ./composer.json ./composer.json.old
fi

if [ '' = "$QUIET" ]; then
    echo '# Creation of the Symfony based project.'
    if [ "-vv" = "$VERBOSE_MODE" ] || [ "-vvv" = "$VERBOSE_MODE" ]; then
        echo '# Project name defined:' "${PROJECT_NAME}"
        echo '# Version of Symfony:' "${SF_VERSION}"
    fi
fi
composerCreateNoInteraction='';
if [ '-q' = "$QUIET" ]; then
    composerCreateNoInteraction='--no-interaction --no-progress'
fi
composer create-project symfony/framework-standard-edition ${PROJECT_NAME} ${SF_VERSION} --no-install -n ${composerCreateNoInteraction} ${VERBOSE_MODE} ${QUIET}

if [ '' = "$QUIET" ]; then
    echo '# Replace the composer.json content.'
fi
cat ${0%/install.sh}/templates/composer.json.tpl > ${PROJECT_NAME}/composer.json

if [ '' = "$QUIET" ]; then
    echo '# Removing the composer.lock and re-install the new one.'
fi
rm ${PROJECT_NAME}/composer.lock
composer install -d ${PROJECT_NAME} ${composerCreateNoInteraction} ${VERBOSE_MODE} ${QUIET}
sleep 8

if [ '' = "$QUIET" ]; then
    echo '# Remove the useless AppBundle folder.'
fi
rm -rf ${PROJECT_NAME}/src/AppBundle

if [ '' = "$QUIET" ]; then
    echo '# Activate bundles into the Kernel.'
fi
cat ${0%/install.sh}/templates/appKernel.php.tpl > ${PROJECT_NAME}/app/AppKernel.php

if [ '' = "$QUIET" ]; then
    echo '# Remove useless generated files.'
fi
rm -rf ${PROJECT_NAME}/README.md
rm -rf ${PROJECT_NAME}/UPGRADE*.md

if [ '' = "$QUIET" ]; then
    echo '### SUCCESS ### Your project is generated.'
fi
exit 0
