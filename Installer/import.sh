#!/bin/bash

display_help() {
    echo 'Usage: sh <generator_project>/Installer/import.sh' \
        '[-h|--help]' \
        '[-i|--input-user]' \
        '-p|--project <project_path>' \
        '[-q|--quiet]' \
        '-s|--swagger-file <swagger_path>' \
        '[-v|--verbose]' \
        '[-vv|--very-verbose]' \
        '[-vvv|--debug]'
    echo ''
    echo '    -h|--help:'
    echo '        Display this help screen.'
    echo ''
    echo '    -i|--input-user:'
    echo '        Ask the user which entities must be created.'
    echo ''
    echo '    -p|--project <project_path>:'
    echo '        Directory which is the project where you want to import.'
    echo ''
    echo '    -q|--quiet:'
    echo '        Do not display anything except errors.'
    echo ''
    echo '    -s|--swagger-file <swagger_path>:'
    echo '        Specify the swagger file used to import the project.'
    echo ''
    echo '    -v|--verbose:'
    echo '        Activate the verbose mode for the import PHP script.'
    echo ''
    echo '    -vv|--very-verbose:'
    echo '        Activate the very verbose mode for the import PHP script, with more information than option -v.'
    echo ''
    echo '    -vvv|--debug:'
    echo '        Activate the ultra verbose mode (debug mode) for the import PHP script.'
    echo ''
}

####
# 0. Parse all arguments
####
SCRIPT_NAME="$0"
PROJECT_NAME=''
SWAGGER_PATH=''
VERBOSE_MODE=''
QUIET="false"
CREATE_ALL='--create-all'

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
    -s|--swagger-file)
    SWAGGER_PATH="$2"
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
    -i|--input-user)
    CREATE_ALL=''
    ;;
    -q|--quiet)
    VERBOSE_MODE=''
    QUIET="true"
    ;;
    *)
    ;;
esac
shift
done

####
# 0 bis. Check mandatory options are here
####
#Validate the SWAGGER_PATH
if [ '' = "$SWAGGER_PATH" ]; then
    echo '# ERROR: you must set the Swagger file path.'
    display_help
    exit 10
fi
if [ ! -f "$SWAGGER_PATH" ]; then
    echo '# ERROR: the Swagger file does not exist. Please specify a valid Swagger file.'
    display_help
    exit 11
fi

#Validate the target project name
if [ '' = "$PROJECT_NAME" ]; then
    echo '# ERROR: you must set the project name where you want to import the Swagger file.'
    display_help
    exit 20
fi

expectedProjectPath="`pwd`/$PROJECT_NAME"
if [ ! -d "$expectedProjectPath" ]; then
    echo '# ERROR: the project path does not exists. Please set a valid project'
    echo '# path or check that you are running this tool from parent folder of '
    echo '# your project repository.'
    display_help
    exit 21
fi

####
# 1. ACTION of copying the Swagger file into the asked project.
####
if [ "false" = "$QUIET" ]; then
    echo '############################################'
    echo '### Import Swagger file into new project ###'
    echo '############################################'
fi

#Copy the Swagger file
if [ "false" = "$QUIET" ]; then
    echo ''
    echo '# Copy the Swagger file into the new project.'
fi
SWAGGER_COPY="${PROJECT_NAME}/entities.${SWAGGER_PATH##*.}"
cp "$SWAGGER_PATH" "${SWAGGER_COPY}"

####
# 2. ACTION of defining the environment variables to bootstrap the generator
####
if [ "false" = "$QUIET" ]; then
    echo ''
    echo '# Define the environment variables to bootstrap the generator.'
fi

#Define the environment variable setting the Swagger file path
SYMFONY_SFYNX_PATH_TO_SWAGGER_FILE="${SWAGGER_COPY}"
export SYMFONY_SFYNX_PATH_TO_SWAGGER_FILE

#Define the environment variable setting the project name with Uppercase
SYMFONY_SFYNX_CONTEXT_NAME=`echo "$PROJECT_NAME" | sed "s/\(.\)/\U\1/"`
export SYMFONY_SFYNX_CONTEXT_NAME

#Define the environment variable to set the destination of generated files
SYMFONY_SFYNX_PATH_TO_DEST_FILES="$PROJECT_NAME"/src
export SYMFONY_SFYNX_PATH_TO_DEST_FILES

####
# 3. ACTION of running the generator to generate all entities from the Swagger file.
####
if [ "false" = "$QUIET" ]; then
    echo ''
    echo '# Importing the entities from the Swagger file by creating all source code.'
fi
php ${SCRIPT_NAME%/Installer/import.sh}/bin/generator sfynx:api ${CREATE_ALL} ${VERBOSE_MODE}
phpStatus="$?"

####
# 4. ACTION of cleaning useless files or residual elements
####
# Remove the entities.yml created for the import
rm -f ${SYMFONY_SFYNX_PATH_TO_SWAGGER_FILE}

####
# 5. ACTION of checking the result.
####
if [ 0 = "$phpStatus" ]
then
    echo "# SUCCESS. The import of all entities succeed. Your project $PROJECT_NAME is now available."
    exit 0
else
    echo "# FAILURE. The import of all entities failed."
    exit 255
fi
