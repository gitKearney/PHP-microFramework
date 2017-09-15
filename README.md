This is super slim Framework. It adheres to PSR-3, PSR-4, and PSR-7 standards

TL;DR
===
In the index class put the route, and the name of the controller class
you want instantiated.

Go to the `Factory` directory, create a factory, which should create a model
and a service class. The service class then takes the model as a parameter.

The service class is where all your logic lives. 

Write all SQL queries in the model. The HTTP body should be parsed in the 
service class.


Routes
===
You MUST add your routes to the index file using regex

Controllers
===
The `construct()` method of the controller calls a factory that creates the service
class, and its dependencies

Take for example the `UserController::__construct()` method

    public function __construct()
    {
        # create a factory to create a service and a model
        $this->userFactory = new UserFactory;
    }
    
This creates a factory class, which creates a `User` model class, 
a `UUID` service class, and the `UserService` class which requires the
above 2 to be created.

This is superior to direct injection in that the dependencies to create the
`UserService` class are defined in the factory.

FLOW
====
The index is called first, which autoloads the controller based on the route.

The constructor to the controller then creates a factory which creates 
the UserService class and its dependencies.

The `handleRequest()` method is called on the controller class. This method 
calls the proper method that corresponds to the HTTP verb (POST, PUT, etc)
the request came in on.

The method that handles the verb, calls the `create()` on the factory which 
returns the service class. 

The Service Class
===
This is where **ALL** the logic lives. Models query, controllers route,
services tie everything together.



