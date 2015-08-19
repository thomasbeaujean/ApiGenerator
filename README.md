

# Installation

		composer require "tbn/apigenerator-bundle"

## Enable the bundle in the AppKernel for the dev environment
		
		...
		new tbn\ApiGeneratorBundle\ApiGeneratorBundle();
       ...

## Add routing     

		tbn_api_generator:
		    resource: "@ApiGeneratorBundle/Resources/config/routing.yml"        

## Add routing for development environment

		tbn_api_generator_dev:
		    resource: "@ApiGeneratorBundle/Resources/config/routing_dev.yml"    

# Configuration
		
		api_generator:
		    all: #The default behaviour for all entities
		        create: false
		        update: false
		        delete: false
		        get_one: true      #get one entity (only foreign keys are sent)
		        get_one_deep: true #get one entity but the foreign entities are completed normalized too
		        get_all: true      #get all entities
		        get_all_deep: true #get all entities but the foreign entities are completed normalized too
		    entity:  #Specify the rights for specific entities
		        "FrontBundle\\Entity\\SomeEntity":
		            create: true
		            update: false
		            delete: true
		            get_one: true
		            get_one_deep: true
		            get_all: true
		            get_all_deep: true
		            

# Usage

Go to the url:

		htpp://your_app/apigenerator-configuration
	
It displays the entities and the rights associated for the Api Generator


# TODO

Persist OneToMany collections

Remove entity

Remove entities

