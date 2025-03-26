#### Laravel package for assessment

Este paquete tiene como finalidad evaluar las habilidades para diseñar, desarrollar, probar un paquete de laravel con las mejores practicas.

####Requisitos

`PHP ^8.0`
`laravel ^12`

####Installation
Debes modificar tu archivo `composer.json` agregando el repositorio github y agregando el paquete a tus dependencias de la siguiente manera

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

Una vez tenemos eso procedemos a hacer la instalación con 
```bash
composer update
```

Al terminar la inatalacion y carga de los autoload del paquete debemos realizar las publicaciones de configuración yh assets del frontend del paquete

```bash
php artisan vendor:publish --tag=quotes-ui-assets --force
```

```bash
 php artisan vendor:publish --tag=config --force
```

Realizado esta operación podremos acceder a la ruta que crea automaticamente el paquete
>###### ruta-de-mi-proyecto.com/quotes-ui
Alli podremos interactuar con las opciones de carga de citas del paquete

#####Archivo de configuación 

 limitación de tasa de solicitudes y url del api `.env`
 
```bach
QUOTES_API_BASE_URL=https://dummyjson.com
QUOTES_RATE_LIMIT=10
QUOTES_RATE_WINDOW=60
```

#### Test Cases
Para ejecutar los test case debemos cumplir con los siguientes pasos

####Desde la ruta del paquete 

##### Todos los tests
`./vendor/bin/phpunit`

##### Solo Feature tests
`./vendor/bin/phpunit --testsuite "Package Feature Tests"`

##### Solo Unit tests
`./vendor/bin/phpunit --testsuite "Package Unit Tests"`

####Desde la app principal

en el `Composer.json` de tu aplicación principal inserta  (esto es solo para dev)
```
{
    "autoload-dev": {
        "psr-4": {
            "RH\\Quotes\\Tests\\": "vendor/rh/quotes/tests/"
        }
    }
}
```
luego ejecuta `composer dump-autoload`

Agrega el directorio de pruebas del paquete al archivo phpunit.xml de la aplicación principal:

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

En la aplicación principal, instala las dependencias necesarias para las pruebas del paquete:
`composer require --dev orchestra/testbench phpunit/phpunit`

##### Configurar variables de entorno
Añade estas variables al `.env.testing` de la aplicación principal:

####Ejecutar

`php artisan test --testsuite="Package Tests"`