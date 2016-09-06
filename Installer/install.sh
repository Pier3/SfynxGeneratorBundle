#!/bin/bash

echo '# Installer of the generator bundle.';
echo '# Creation of the Symfony based project.';

if [ '' = "$1" ]
then
    echo '### ERROR ### You must add the project name as 1st argument.'
    exit 1
fi

projectName=$1;

if [ '' = "$2" ]
then
    version="2.8.*"
else
    version=$2
fi

if [ -d "$projectName" ]
then
    echo '### ERROR ## This project already exist.'
    exit 2
fi

if [ -f ./composer.json ]; then
    mv ./composer.json ./composer.json.old
fi

composer create-project symfony/framework-standard-edition "$projectName" "$version" --no-install --no-progress -n

echo '# Replace the composer.json content.'
cat ${0%/install.sh}/templates/composer.json.tpl > ${projectName}/composer.json

echo '# Removing the composer.lock and re-install the new one.'
rm ${projectName}/composer.lock
composer install -d ${projectName}
sleep 8

echo '# Activate bundles into the Kernel.'
cat ${0%/install.sh}/templates/appKernel.php.tpl > ${projectName}/app/AppKernel.php

echo '### SUCCESS ### Your project is generated.'
exit 0
