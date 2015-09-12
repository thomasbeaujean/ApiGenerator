

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
		    default: #The default behaviour for all entities
		        create: false #optionnal
		        update: false #optionnal
		        delete: false #optionnal
		        get_one: false      #optionnal #get one entity (only foreign keys are sent)
		        get_one_deep: false #optionnal #get one entity but the foreign entities are completed normalized too
		        get_all: false      #optionnal #get all entities
		        get_all_deep: false #optionnal #get all entities but the foreign entities are completed normalized too
		    entity:  #Specify the rights for specific entities
                        user: #the entity alias
                            class: "FrontBundle\\Entity\\SomeEntity" #mandatory
		            create: true  #optionnal
		            update: false #optionnal
		            delete: true  #optionnal
		            get_one: true #optionnal
		            get_one_deep: true #optionnal
		            get_all: true #optionnal
		            get_all_deep: true #optionnal

# Usage

Go to the url:

		htpp://your_app/_apigenerator-configuration

It displays the entities and the rights associated for the Api Generator


# TODO

Persist OneToMany collections

Remove entities

