WHY
---
This is how **I** feel a good PHP web environment should be setup. It comes 
from various tutorials I've read, as well as just working with PHP for a few
years.

Each container is divided into a _service_:i.e. it does one thing.

### COMPOSER
composer is installed via a composer container. So, all composer commands are 
run via the command `docker compose run php_composer composer require {package}`

_E.G._ `docker compose run php_composer composer require INSERT_PACKAGE_HERE`

### AUTO LOADING
You'll also need to run `docker compose run php_composer composer dump-autoload`


### DOCKER EXEC
Connect to the PHP-FPM container

    docker exec -it utmsf-php-fpm-1 bash

### DOCKER RESTART
Need to restart lighttpd?

    docker restart lighttpd_dev


