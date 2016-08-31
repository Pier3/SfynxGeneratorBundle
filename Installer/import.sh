#!/bin/bash

echo '############################################'
echo '### Import Swagger file into new project ###'
echo '############################################'
echo ''

#
# 1. ACTION of copying the Swagger file into the asked project.
#

#Validate the SWAGGER_PATH (1st argument)
SWAGGER_PATH="$1"
if [ '' = "$SWAGGER_PATH" ]
then
    echo '# ERROR: you must set the Swagger file path.'
    echo "# Usage: $0 <SWAGGER_PATH> <PROJECT_NAME>"
    exit 10
fi
if [ ! -f "$SWAGGER_PATH" ]
then
    echo '# ERROR: the Swagger file does not exist. Please specify a valid Swagger file.'
    echo "# Usage: $0 <SWAGGER_PATH> <PROJECT_NAME>"
    exit 11
fi

#Validate the target project name
PROJECT_NAME="$2"
if [ '' = "$PROJECT_NAME" ]
then
    echo '# ERROR: you must set the project name where you want to import the Swagger file.'
    echo "# Usage: $0 <SWAGGER_PATH> <PROJECT_NAME>"
    exit 20
fi
expectedProjectPath="`pwd`/$PROJECT_NAME"
if [ ! -d "$expectedProjectPath" ]
then
    echo '# ERROR: the project path does not exists. Please set a valid project'
    echo '# path or check that you are running this tool from parent folder of '
    echo '# your project repository.'
    echo "# Usage: $0 <SWAGGER_PATH> <PROJECT_NAME>"
    exit 21
fi

#Finally copy the Swagger file
echo '# Copy the Swagger file into the new project.'
cp "$SWAGGER_PATH" "$PROJECT_NAME"/entities.yml

#
# 2. ACTION of defining the environment variables to bootstrap the generator
#
echo ''
echo '# Define the environment variables to bootstrap the generator.'

#Define the environment variable setting the Swagger file path
SYMFONY_SFYNX_PATH_TO_SWAGGER_FILE="$PROJECT_NAME"/entities.yml
export SYMFONY_SFYNX_PATH_TO_SWAGGER_FILE

#Define the environment variable setting the project name with Uppercase
SYMFONY_SFYNX_CONTEXT_NAME=`echo "$PROJECT_NAME" | sed "s/\(.\)/\U\1/"`
export SYMFONY_SFYNX_CONTEXT_NAME

#Define the environment variable to set the destination of generated files
SYMFONY_SFYNX_PATH_TO_DEST_FILES="$PROJECT_NAME"/src
export SYMFONY_SFYNX_PATH_TO_DEST_FILES


#
# 3. ACTION of running the generator to generate all entities from the Swagger file.
#
echo ''
echo '# Importing the entities from the Swagger file by creating all source code.'
#php "$PROJECT_NAME"/bin/generator sfynx:api --create-all
php ${0%/Installer/import.sh}/bin/generator sfynx:api --create-all

phpStatus="$?"
if [ 0 = "$phpStatus" ]
then
    echo "# SUCCESS. The import of all entities succeed. Your project $PROJECT_NAME is now available."
    exit 0
else
    echo "# FAILURE. The import of all entities failed."
    exit 255
fi
