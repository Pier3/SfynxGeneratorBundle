**************************
  Sfynx GeneratorBundle
**************************

This bundle permits to generate a DDD code.

Installation 
************

You need to install it via composer.
Clone the GeneratorBundle into a directory on your local filesystem.
git clone http://git.it.aareonit.fr/dil/generator.git

Then update the composer.json of the application on which you work to indicate a new composer repository :

.. code-block:: json

    "repositories": [
        {
            "type": "vcs",
            "url": "/home/dev/generator"
        }
    ]
    
in your project directory type then

.. code-block:: bash

    root@dev:/home/project$ composer require sfynx/generatorBundle dev-develop    
    
Add the Bundle in your appKernel.php :

.. code-block:: php

            new \Sfynx\DddGeneratorBundle\SfynxDddGeneratorBundle();    
            



Bundle generator
****************

Bundle generator permits to create DDD context with a route to a controller.


List of generated files
***********************

.. code-block:: php

    src/DemoContext/
    ├── Application
    │   └── DemoContext
    │       ├── Command
    │       └── Query
    ├── Config
    ├── Domain
    │   ├── Entity
    │   ├── Repository
    │   ├── Service
    │   │   └── DemoContext
    │   │       ├── Factory
    │   │       │   ├── CouchDB
    │   │       │   ├── Odm
    │   │       │   └── Orm
    │   │       ├── Manager
    │   │       └── Processor
    │   ├── Specification
    │   │   └── Infrastructure
    │   │       └── DemoContext
    │   └── Workflow
    │       └── DemoContext
    │           ├── Handler
    │           └── Listener
    ├── Infrastructure
    │   └── Persistence
    │       └── Repository
    │           └── DemoContext
    ├── InfrastructureBundle
    │   ├── DependencyInjection
    │   │   ├── Configuration.php
    │   │   └── InfrastructureBundleExtension.php
    │   ├── InfrastructureBundle.php
    │   └── Resources
    ├── Presentation
    │   ├── Adapter
    │   │   └── DemoContext
    │   ├── Coordination
    │   │   └── DemoContetxtxt
    │   │       └── Query
    │   │           └── Controller.php
    │   └── Request
    │       └── DemoContext
    └── PresentationBundle
        ├── Config
        │   └── routing.yml
        ├── DependencyInjection
        │   ├── Compiler
        │   │   └── ResettingListenersPass.php
        │   ├── Configuration.php
        │   └── PresentationBundleExtension.php
        ├── PresentationBundle.php
        └── Resources
            ├── config
            │   ├── application
            │   ├── controllers.yml
            │   ├── public
            │   ├── route
            │   └── translations
            └── Services.yml
    

Usage:
^^^^^^

.. code-block:: bash
    
    root@dev:/home/project$ php app/console sfynx:generate:ddd:bundle
    
Enter the Context Name when prompt ask you for one.    
    
Configuration:
^^^^^^^^^^^^^^

You need to create a reference to Context route file in app/config/routing.yml:


.. code-block:: php

    project_demoContext_routing:
        resource: "@PresentationBundle/config/routing.yml"
        prefix: /

    


Api generator
*************

The api generator permits to create a DDD api from a swagger file.

Usage:
^^^^^^

.. code-block:: bash

    root@dev:/home/project$ SYMFONY__PROJECT__DIR__SRC__ENV=src
    root@dev:/home/project$ export SYMFONY__PROJECT__DIR__SRC__ENV
    root@dev:/home/project$ php app/console sfynx:generate:ddd:api
    
Answer each questions when prompt ask you to know you if you want to create ValueObject or Actions.


List of generated files
***********************


.. code-block:: php
    
    src/
    ├── Application
    │   └── Country
    │       ├── Command
    │       │   ├── Handler
    │       │   │   ├── DeleteCommandHandler.php
    │       │   │   ├── NewCommandHandler.php
    │       │   │   └── UpdateCommandHandler.php
    │       │   ├── NewCommand.php
    │       │   ├── UpdateCommand.php
    │       │   └── Validation
    │       │       ├── SpecHandler
    │       │       │   ├── NewCommandSpecHandler.php
    │       │       │   └── UpdateSpecHandler.php
    │       │       └── ValidationHandler
    │       │           ├── NewCommandValidationHandler.php
    │       │           └── UpdateCommandValidationHandler.php
    │       └── Query
    │           ├── FindbynameQuery.php
    │           ├── GetAllQuery.php
    │           ├── GetQuery.php
    │           └── SearchByQuery.php
    ├── Domain
    │   ├── Entity
    │   │   └── Country.php
    │   ├── Repository
    │   │   └── CountryRepositoryInterface.php
    │   ├── ValueObject
    │   │   └── IdVO.php
    │   └── WorkFlow
    │       └── Country
    │           ├── Handler
    │           │   ├── NewWFHandler.php
    │           │   └── UpdateWFHandler.php
    │           └── Observer
    │               ├── WFGenerateVOObserver.php
    │               └── WFSaveEntityObserver.php
    ├── Infrastructure
    │   └── Persistence
    │       └── Repository
    │           └── Country
    │               ├── Odm
    │               │   ├── DeleteRepository.php
    │               │   ├── FindbynameRepository.php
    │               │   ├── GetAllRepository.php
    │               │   ├── GetRepository.php
    │               │   └── SearchByRepository.php
    │               └── Orm
    │                   ├── DeleteRepository.php
    │                   ├── FindbynameRepository.php
    │                   ├── GetAllRepository.php
    │                   ├── GetRepository.php
    │                   └── SearchByRepository.php
    └── Presentation
        ├── Adapter
        │   └── Country
        │       ├── Command
        │       │   ├── DeleteCommandAdapter.php
        │       │   ├── NewCommandAdapter.php
        │       │   └── UpdateCommandAdapter.php
        │       ├── query
        │       │   └── FindbynameQueryAdapter.php
        │       └── Query
        │           ├── GetAllQueryAdapter.php
        │           ├── GetQueryAdapter.php
        │           └── SearchByQueryAdapter.php
        ├── Coordination
        │   └── Country
        │       ├── Command
        │       │   └── CountryController.php
        │       └── Query
        │           └── CountryController.php
        └── Request
            └── Country
                ├── DeleteRequest.php
                ├── FindbynameRequest.php
                ├── GetAllRequest.php
                ├── GetRequest.php
                ├── NewRequest.php
                ├── SearchByRequest.php
                └── UpdateRequest.php




    

