#### Laravel package for assessment

This package is intended to assess the skills to design, develop, and test a Laravel package using best practices.

####Requisitos

`PHP ^8.0`
`laravel ^12`

####Installation
You need to modify your `composer.json` file by adding the github repository and adding the package to your dependencies as follows

```json
"require-dev": {
        ...
        "rh/quotes": "@dev"
    },
"repositories":[
        {
            "type": "vcs",
            "url": "https://github.com/johanp345/rh-quotes.git"
        }
    ],
```

Once we have that we proceed to do the installation with
```bash
composer update
```

After finishing the installation and loading of the package's autoload, we must publish the configuration and assets of the package's frontend.

```bash
php artisan vendor:publish --tag=quotes-ui-assets --force
```

```bash
 php artisan vendor:publish --tag=config --force
```

Once this operation is done we can access the route that the package automatically creates.

>###### ruta-de-mi-proyecto.com/quotes-ui
There we can interact with the package's appointment loading options.

#####Configuration file

Request rate limiting and API URL `.env`
 
```bach
QUOTES_API_BASE_URL=https://dummyjson.com
QUOTES_RATE_LIMIT=10
QUOTES_RATE_WINDOW=60
```

#### Test Cases
To run the test cases we must follow the following steps:

####From the package route

##### All tests
`./vendor/bin/phpunit`

##### Only Feature tests
`./vendor/bin/phpunit --testsuite "Package Feature Tests"`

##### Only Unit tests
`./vendor/bin/phpunit --testsuite "Package Unit Tests"`

####From the main app

in your main app's `Composer.json` insert (this is for dev only)
```
{
    "autoload-dev": {
        "psr-4": {
            "RH\\Quotes\\Tests\\": "vendor/rh/quotes/tests/"
        }
    }
}
```
then execute `composer dump-autoload`

Add the package tests directory to the main application's phpunit.xml file:

```
<phpunit>
    <testsuites>
        <testsuite name="Application Tests">
            <!-- Tus tests existentes -->
        </testsuite>
        <testsuite name="Package Tests">
            <directory suffix="Test.php">vendor/rh/quotes/tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

In the main application, install the dependencies needed for package testing:
`composer require --dev orchestra/testbench phpunit/phpunit`

##### Configure environment variables
Add these variables to the `.env.testing` of the main application:

####Execute

`php artisan test --testsuite="Package Tests"`