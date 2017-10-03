This is super slim Framework. It adheres to PSR-3, PSR-4, and PSR-7 standards

TL;DR
===
In the index class put the route, and the name of the controller class
you want instantiated.

Go to the `Factories` directory, and add to the `Definitions.php` file the contoller, the service, and the model. The service should take in the model in its definition. The controller should at least take in a service in its definition

NOTES
===

This uses Pimple for depdency injection. I chose Pimple because the developer must explicitly define the depdencies which means loading is faster and no sort of caching is required.

The service class is where all your logic lives. 

Write all SQL queries in the model. The HTTP body should be parsed in the 
service class.

Routes
===
You MUST add your routes to the index file using regex

FLOW
====
The index is called first, which autoloads a Pimple container.

The anonymous function of the route uses the Pimple container to instantiate an appropriate controller (based on the route).

The `handleRequest()` method is called on the controller class. This method 
calls the proper method that corresponds to the HTTP verb (POST, PUT, etc)
the request came in on.

The Service Class
===
This is where **ALL** the logic lives. Models query, controllers route,
services tie everything together.

Logging
===
A global logging function exists which uses a stream object to write to a log file.

To log a variable, simply call the function like so

    logVar('user string: '.$variable);

    // or

    logVar($variabl);

You can pass in any variable to the `logVar()` function. It automatically converts objects, arrays, and booleans to strings for easy reading in a log file



