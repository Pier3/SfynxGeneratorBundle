#!/bin/bash

echo 'Installer of the generator bundle.';
echo 'Creation of the Symfony based project';

if [ '' = "$1" ]
then
	echo 'ERROR: you must add the project name as 1st argument'
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
	echo 'ERROR: this project already exist.'
	exit 2
fi

composer create-project symfony/framework-standard-edition "$projectName" "$version" --no-install
cd "$projectName"

echo 'Replace the composer.json content'
cat ../generator/Installer/templates/composer.json.tpl > composer.json

echo 'Removing the composer.lock and re-install the new one'

rm composer.lock
composer install --ignore-platform-reqs

echo 'Activate bundles into the Kernel'
cat ../generator/Installer/templates/appKernel.php.tpl > app/AppKernel.php

echo 'SUCCESS. Your project is generated.'
exit 0
