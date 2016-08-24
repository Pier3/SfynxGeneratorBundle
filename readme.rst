#####################
SphynxGeneratorBundle
#####################

This bundle permits to generate a DDD code.



How install generator
=======================


Create a symfony project with composer
--------------------------------------------
.. code-block:: bash

    composer create-project symfony/framework-standard-edition Demo "2.8.*"
    cd Demo



Replacer your composer.json
---------------------------

.. code-block:: json

    {
        "name": "symfony/framework-standard-edition",
        "license": "MIT",
        "type": "project",
        "description": "The \"Symfony Standard Edition\" distribution",
        "autoload": {
            "psr-4": { "": "src/" },
            "classmap": [ "app/AppKernel.php", "app/AppCache.php" ]
        },
        "require": {
            "php": ">=5.3.9",
            "symfony/symfony": "2.8.*",
            "doctrine/orm":"dev-master",
            "doctrine/doctrine-bundle": "~1.4",
            "symfony/swiftmailer-bundle": "~2.3",
            "symfony/monolog-bundle": "~2.4",
            "sensio/distribution-bundle": "~5.0",
            "sensio/framework-extra-bundle": "^3.0.2",
            "incenteev/composer-parameter-handler": "~2.0",
            "sfynx-project/tool-ddd-bundle": "dev-2.8-dev",
            "doctrine/mongodb-odm-bundle":"@dev",
            "doctrine/couchdb": "@dev",
            "doctrine/couchdb-odm": "@dev",
            "stof/doctrine-extensions-bundle":"@dev",
            "sfynx-project/tool-generator-bundle": "dev2.8-dev",
            "stof/doctrine-extensions-bundle":"@dev"
        },
        "require-dev": {
            "sensio/generator-bundle": "~3.0",
            "symfony/phpunit-bridge": "~2.7"
        },
        "scripts": {
            "post-install-cmd": [
                "Incenteev\\ParameterHandler\\ScriptHandler::buildParameters",
                "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::buildBootstrap",
                    "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::clearCache",
                "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installAssets",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installRequirementsFile",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::prepareDeploymentTarget"
        ],
        "post-update-cmd": [
            "Incenteev\\ParameterHandler\\ScriptHandler::buildParameters",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::buildBootstrap",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::clearCache",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installAssets",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installRequirementsFile",
                "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::prepareDeploymentTarget"
            ]
        },
        "config": {
            "bin-dir": "bin",
            "platform": {
                "php": "5.3.9"
            }
        },
        "extra": {
            "symfony-app-dir": "app",
            "symfony-web-dir": "web",
            "symfony-assets-install": "relative",
            "incenteev-parameters": {
                "file": "app/config/parameters.yml"
            },
            "branch-alias": {
                "dev-master": "2.8-dev"
            }
        }
    }

Install vendors
---------------------------

.. code-block:: bash

    rm composer.lock
    composer install --ignore-platform-reqs




Add the generator in your AppKernel.php
-----------------------------------------
.. code-block:: php

    new Sfynx\DddGeneratorBundle\SfynxDddGeneratorBundle(),
    new \Sfynx\DddBundle\SfynxDddBundle(),
    new \JMS\SerializerBundle\JMSSerializerBundle(),
    new \Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),

Create a swagger file (or use a swagger file in generator folder for test)
---------------------------------------------------------------------------

Somes swagger files are present in the root of generator bundle


read write
----------------------
If you have a chmod, chown or write error retry under root user


Generation step
----------------------

Generate the country API
~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

    SYMFONY_SFYNX_PATH_TO_SWAGGER_FILE=vendor/sfynx-project/tool-generator-    bundle/swagger_country.yml
    export SYMFONY_SFYNX_PATH_TO_SWAGGER_FILE
    SYMFONY_SFYNX_CONTEXT_NAME=DemoCountry
    export SYMFONY_SFYNX_CONTEXT_NAME
    php app/console sfynx:generate:ddd:api --create-all

Generate the actor API with values objects
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
.. code-block:: bash

    SYMFONY_SFYNX_PATH_TO_SWAGGER_FILE=vendor/sfynx-project/tool-generator-    bundle/swagger_actor.yml
    export SYMFONY_SFYNX_PATH_TO_SWAGGER_FILE
    SYMFONY_SFYNX_CONTEXT_NAME=DemoActor
    export SYMFONY_SFYNX_CONTEXT_NAME
    php app/console sfynx:generate:ddd:api --create-all


Generate the movie API
~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: bash

    SYMFONY_SFYNX_PATH_TO_SWAGGER_FILE=vendor/sfynx-project/tool-generator-    bundle/swagger_movie.yml
    export SYMFONY_SFYNX_PATH_TO_SWAGGER_FILE
    SYMFONY_SFYNX_CONTEXT_NAME=DemoMovie
    export SYMFONY_SFYNX_CONTEXT_NAME
    php app/console sfynx:generate:ddd:api --create-all

Generate your own API
~~~~~~~~~~~~~~~~~~~~~~~~~~~


.. code-block:: bash

    SYMFONY_SFYNX_PATH_TO_SWAGGER_FILE=<PATH_TO_YOUR_SWAGGER_FILE>
    export SYMFONY_SFYNX_PATH_TO_SWAGGER_FILE
    SYMFONY_SFYNX_CONTEXT_NAME=<CONTEXT_NALE>
    export SYMFONY_SFYNX_CONTEXT_NAME
    php app/console sfynx:generate:ddd:api --create-all

Configfuration of Symfony after generation
-------------------------------------------

Add the new generated bundle in AppKernel.php
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    new \<CONTEXTNAME>\InfrastructureBundle\<CONTEXTNAME>InfrastructureBundle(),
    new \<CONTEXTNAME>\PresentationBundle\<CONTEXTNAME>PresentationBundle(),


*important:* replace <CONTEXTNAME> by the context name you enter in the generation step

Add a link to routes of your generated context
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
.. code-block:: php

    project_<CONTEXTNAME>_<ENTITY>_routing:
        resource: "@<CONTEXTNAME>PresentationBundle/Resources/config/routes/routing_<ENTITYNAME>.yml"
     prefix: /api

You need to add this section for each entities present in your x-entities of your swagger file.

*important:* replace <CONTEXTNAME> by the context name you enter in the generation step




Indicate the database.driver variable in app/config/parameters.yml
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
.. code-block:: php

    database.driver: orm

Configure Symfony config.yml (app/config/config.yml)
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
Add this statement under the doctrine section.
Don't forget to replace <CONTEXTNAME> by the context you specified in the generation step.


.. code-block:: php


    orm:
        auto_generate_proxy_classes: %kernel.debug%
        auto_mapping: true
        mappings:
            StofDoctrineExtensionsBundle: ~
            <CONTEXTNAME>:
                type: annotation
                alias: <CONTEXTNAME>
                prefix: <CONTEXTNAME>\Domain\Entity
                dir: "%kernel.root_dir%/../src/<CONTEXTNAME>/Domain/Entity"
            <CONTEXTNAME>VO:
                type: annotation
                alias: <CONTEXTNAME>VO
                prefix: <CONTEXTNAME>\Domain\ValueObject
                dir: "%kernel.root_dir%/../src/<CONTEXTNAME>/Domain/ValueObject"
            SfynxDddBundle:
                type: annotation
                alias: VO
                prefix: Sfynx\DddBundle\Layer\Domain\ValueObject
                dir: "%kernel.root_dir%/../vendor/sfynx-project/tool-ddd-bundle/Sfynx/DddBundle/Layer/Domain/ValueObject"
            translatable:
                type: annotation
                alias: Gedmo
                prefix: Gedmo\Translatable\Entity
                dir: "%kernel.root_dir%/../vendor/gedmo/doctrine-extensions/lib/Gedmo/Translatable/Entity"
            loggable:
                type: annotation
                alias: Gedmo
                prefix: Gedmo\Loggable\Entity
                dir: "%kernel.root_dir%/../vendor/gedmo/doctrine-extensions/lib/Gedmo/Loggable/Entity"
            tree:
                type: annotation
                alias: Gedmo
                prefix: Gedmo\Tree\Entity
                dir: "%kernel.root_dir%/../vendor/gedmo/doctrine-extensions/lib/Gedmo/Tree/Entity"
        metadata_cache_driver: array # array|apc|memcache#ETC
        query_cache_driver: array # array|apc|memcache#ETC
        result_cache_driver: array # array|apc|memcache#ETC

Add this section in the root on config file (no under a section).
~

*important:* replace <CONTEXTNAME> by the context name you enter in the generation step


.. code-block:: php

    DemoCountry_infrastructure:
        database_type: orm

Syntax of swagger file :
=========================

Define route, controller and action
------------------------------------


Entities and values objects
-----------------------------
To edit swagger file you can use Swagger editor.
You can download a Docker image here : https://hub.docker.com/r/mydock/swagger-editor/

You need to declare requests and responses with original swagger syntax and extends swagger file with this data :

.. code-block:: php

    x-valueObjects:
      IdVO:                                                 <--- Name of value object
        name: id                                            <--- Name of field
        type: Sfynx\DddBundle\Layer\Domain\ValueObject\IdVO <--- Namespace of value object ( <CONTEXTNAME>\Domain\ValueObject\<VONAME> )
        x-fields:                                           <--- Declaration of fields of value object
          id:                                               <--- Name of field
            name: id                                        <--- Name of field
            type: IdVO                                      <--- type of field (string,number,valueObject name ...)
      ProfileVO:
        name: ProfileVO
        type: \DemoActor\Domain\ValueObject\ProfileVO
        x-fields:
          lastname:
            name: lastname
            type: string
          firstname:
            name: firstname
            type: string
      SituationVO:
        name: SituationVO
        type: \DemoActor\Domain\ValueObject\SituationVO
        x-fields:
          sexVO:
            name: SexVO
            type: SexVO
          birthday:
            name: birthday
            type: string
      ContactVO:
        name: ContactVO
        type: \DemoActor\Domain\ValueObject\ContactVO
        x-fields:
          phoneNumber1:
            name: phoneNumber1
            type: string
          phoneNumber2:
            name: phoneNumber2
            type: string
          email:
            name: email
            type: string
      SalaryVO:
        name: SalaryVO
        type: \DemoActor\Domain\ValueObject\SalaryVO
        x-fields:
          value:
            name: value
            type: integer
          currency:
            name: currency
            type: string
      SexVO:
        name: SexVO
        type: \DemoActor\Domain\ValueObject\SexVO
        x-fields:
          gender:
            name: gender
            type: string

    x-entities:                                                <--- Permits to declare entities
      Actor:                                                   <--- Entity Name
        name: Actor                                            <--- Entity Name
        type: entity                                           <--- Entity Type
        x-fields:                                              <--- Entity fields
          entityId:                                            <--- Field name
            name: entityId                                     <--- Field name
            type: id                                           <--- Field type
            voName: IdVO                                       <--- Field VO name (if field is value object)
          simplefield:
             name: simplefield
             type: string
          valueObjectField:
            name: valueObjectField
            type: valueObject
            voName: valueObjectFieldVO


*important:* replace <CONTEXTNAME> by the context name you enter in the generation step



Do a search on table
======================

To effectuate a search you can use the searchBy query.
For that create a route in your swagger file

.. code-block:: php

    /v{_version}/country/searchBy:
          post:
            operationId: searchBy
            x-controller: Country
            x-entity: Country
            description: |
              Get `country` object with id *countryId*
            parameters:
              - name: criteria
                in: body
                required: true
                description: criteria object
                schema:
                  title: country
                  type: object
                  properties:
                    id:
                      type: integer
                    id_parent:
                       type: integer
                    code:
                      type: string
                    type:
                      type: string
                    libelle:
                      type: string
                    reference:
                      type: string
            responses:
              # 200 Response code
              200:
                description: Successful response
                # A schema describing your response object.
                # Use JSON Schema format
                schema:
                  title: Entity
                  type: object
                  properties:
                    id:
                      type: integer
                    id_parent:
                       type: integer
                    code:
                      type: string
                    type:
                      type: string
                    libelle:
                      type: string
                    reference:
                      type: string

              # 403 Response code
              403  :
                description: Access forbidden
              500:
                description: An error occurs


To effectuate a searchrequest create a POSTrequest on /v1/country/searchBy with this data in body :

Simple :
---------
.. code-block:: javascript


    {
	"criterias":
		{
			"field": "a.indicatif",
			"operator": "=",
			"value": "1150"
		}
    }


Complex :
----------

.. code-block:: javascript

    {
	"criterias": {
		"and": [{
			"or": [{
				"field": "a.indicatif",
				"operator": "=",
				"value": "'1150'"
			}, {
				"field": "a.indicatif",
				"operator": "=",
				"value": "'2000'"
			}]
		}, {
			"field": "a.iso",
			"operator": "=",
			"value": "'fr'"
		}]
	}
    }

*Important* : you need to prefix your field name by *a.*


Generated code structure
--------------------------

.. code-block: text

    src/DemoCountry/
    ├── Application
    │   └── Country
    │       ├── Command
    │       │   ├── DeleteCommand.php
    │       │   ├── Handler
    │       │   │   ├── Decorator
    │       │   │   │   ├── NewCommandHandlerDecorator.php
    │       │   │   │   ├── PatchCommandHandlerDecorator.php
    │       │   │   │   └── UpdateCommandHandlerDecorator.php
    │       │   │   ├── DeleteCommandHandler.php
    │       │   │   ├── DeleteManyCommandHandler.php
    │       │   │   ├── NewCommandHandler.php
    │       │   │   ├── PatchCommandHandler.php
    │       │   │   └── UpdateCommandHandler.php
    │       │   ├── NewCommand.php
    │       │   ├── PatchCommand.php
    │       │   ├── UpdateCommand.php
    │       │   └── Validation
    │       │       ├── SpecHandler
    │       │       │   ├── NewCommandSpecHandler.php
    │       │       │   ├── PatchCommandSpecHandler.php
    │       │       │   └── UpdateCommandSpecHandler.php
    │       │       └── ValidationHandler
    │       │           ├── NewCommandValidationHandler.php
    │       │           ├── PatchCommandValidationHandler.php
    │       │           └── UpdateCommandValidationHandler.php
    │       └── Query
    │           ├── GetAllQuery.php
    │           ├── GetByIdsQuery.php
    │           ├── GetQuery.php
    │           ├── Handler
    │           │   ├── GetAllQueryHandler.php
    │           │   ├── GetByIdsQueryHandler.php
    │           │   ├── GetQueryHandler.php
    │           │   └── SearchByQueryHandler.php
    │           └── SearchByQuery.php
    ├── Domain
    │   ├── Entity
    │   │   └── Country.php
    │   ├── Repository
    │   │   └── CountryRepositoryInterface.php
    │       ├── Service
    │   │   └── Country
    │   │       ├── Factory
    │   │       │   ├── CouchDB
    │   │       │   │   └── RepositoryFactory.php
    │   │       │   ├── Odm
    │   │       │   │   └── RepositoryFactory.php
    │   │       │   └── Orm
    │   │       │       └── RepositoryFactory.php
    │   │       ├── Manager
    │   │       │   └── CountryManager.php
    │   │       └── Processor
    │   │           ├── PostPersistProcess.php
    │   │           └── PrePersistProcess.php
    │   ├── Specification
    │   │   └── Infrastructure
    │   │       └── User
    │   │           ├── SpecIsRoleAdmin.php
    │   │           ├── SpecIsRoleAnonymous.php
    │   │           └── SpecIsRoleUser.php
    │   ├── ValueObject
    │   │   └── IdVO.php
    │   └── Workflow
    │       └── Country
    │           ├── Handler
    │           │   ├── NewWFHandler.php
    │           │   ├── PatchWFHandler.php
    │           │   └── UpdateWFHandler.php
    │           └── Listener
    │               ├── WFGenerateVOListener.php
    │               ├── WFGetCurrency.php
    │               ├── WFPublishEvent.php
    │               ├── WFRetrieveEntity.php
    │               └── WFSaveEntity.php
    ├── Infrastructure
    │   ├── EntityType
    │   │   ├── CouchDB
    │   │   │   └── IdVOType.php
    │   │   ├── Odm
    │   │   │   └── IdVOType.php
    │   │   └── Orm
    │   │       └── IdVOType.php
    │   └── Persistence
    │       └── Repository
    │           └── Country
    │               ├── Odm
    │               │   ├── DeleteManyRepository.php
    │               │   ├── DeleteRepository.php
    │               │   ├── GetAllRepository.php
    │               │   └── GetRepository.php
    │               ├── Orm
    │               │   ├── DeleteManyRepository.php
    │               │   ├── DeleteRepository.php
    │               │   ├── GetAllRepository.php
    │               │   ├── GetByIdsRepository.php
    │               │   ├── GetRepository.php
    │               │   └── SearchByRepository.php
    │               └── TraitEntityName.php
    ├── InfrastructureBundle
    │   ├── DemoCountryInfrastructureBundle.php
    │   └── DependencyInjection
    │       ├── Compiler
    │       │   └── CreateRepositoryFactoryPass.php
    │       ├── Configuration.php
    │       └── DemoCountryInfrastructureBundleExtension.php
    ├── Presentation
    │   ├── Adapter
    │   │   └── Country
    │   │       ├── Command
    │   │       │   ├── DeleteCommandAdapter.php
    │   │       │   ├── DeleteManyCommandAdapter.php
    │   │       │   ├── NewCommandAdapter.php
    │   │       │   ├── PatchCommandAdapter.php
    │   │       │   └── UpdateCommandAdapter.php
    │   │       └── Query
    │   │           ├── GetAllQueryAdapter.php
    │   │           ├── GetByIdsQueryAdapter.php
    │   │           ├── GetQueryAdapter.php
    │   │           └── SearchByQueryAdapter.php
    │   ├── Coordination
    │   │   └── Country
    │   │       ├── Command
    │   │       │   └── Controller.php
    │   │       └── Query
    │   │           └── Controller.php
    │   └── Request
    │       └── Country
    │           ├── Command
    │           │   ├── DeleteManyRequest.php
    │           │   ├── DeleteRequest.php
    │           │   ├── NewRequest.php
    │           │   ├── PatchRequest.php
    │           │   └── UpdateRequest.php
    │           └── Query
    │               ├── GetAllRequest.php
    │               ├── GetByIdsRequest.php
    │               ├── GetRequest.php
    │               └── SearchByRequest.php
    ├── PresentationBundle
    │   ├── DemoCountryPresentationBundle.php
    │   ├── DependencyInjection
    │   │   ├── Compiler
    │   │   │   └── ResettingListenersPass.php
    │   │   ├── Configuration.php
    │   │   └── DemoCountryPresentationBundleExtension.php
    │   └── Resources
    │       └── config
    │           ├── application
    │           │   └── country.yml
    │           ├── controllers.yml
    │           └── routes
    │               └── routing_country.yml
    └── Tests
        ├── Application
        │   └── Entity
        │       └── Command
        │           ├── DeleteCommandTest.php
        │           ├── Handler
        │           │   ├── Decorator
        │           │   │   ├── NewCommandHandlerDecoratorTest.php
        │           │   │   ├── PatchCommandHandlerDecoratorTest.php
        │           │   │   └── UpdateCommandHandlerDecoratorTest.php
        │           │   ├── DeleteCommandHandlerTest.php
        │           │   ├── DeleteManyCommandHandlerTest.php
        │           │   ├── NewCommandHandler.php
        │           │   ├── PatchCommandHandlerTest.php
        │           │   └── UpdateCommandHandlerTest.php
        │           ├── NewCommandTest.php
        │           └── UpdateCommandTest.php
        ├── Domain
        │   └── Service
        │       └── Country
        │           ├── Factory
        │           │   └── Orm
        │           │       └── RepositoryFactoryTest.php
        │           └── Manager
        │               └── CountryManagerTest.php
        ├── Presentation
        │   ├── Adapter
        │   │   └── Country
        │   │       └── Command
        │   │           ├── DeleteCommandAdapterTest.php
        │   │           ├── NewCommandAdapterTest.php
        │   │           ├── PatchCommandAdapterTest.php
        │   │           └── UpdateCommandAdapterTest.php
        │   ├── Coordination
        │   │   └── Country
        │   │       ├── Command
        │   │       │   └── ControllerTest.php
        │   │       └── Query
        │   │           └── ControllerTest.php
        │   └── Request
        │       └── Country
        │           ├── Command
        │           │   ├── DeleteRequestTest.php
        │           │   ├── NewRequestTest.php
        │           │   ├── PatchRequestTest.php
        │           │   └── UpdateRequestTest.php
        │           └── Query
        │               ├── GetRequestTest.php
        │               └── SearchByRequestTest.php
        └── TraitVerifyResolver.php



Unit tests
------------

It exists two kind of unit tests with the generator.

Generation tests :
~~~~~~~~~~~~~~~~~~~~

To run generation tests edit vendor/sfynx-project/tool-generator-bundle/phpunit.xml and change contextName and swaggerFile variable then run phpunit -c phpunit.xml in the vendor/sfynx-project/tool-generator-bundle/ dir.
This set of tests will check all files has been generated and has the good classes and namespace.


Functional tests
~~~~~~~~~~~~~~~~~~

This tests has not been complemently implemented at this time.




