<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();


/*try {
    (new Dotenv\Dotenv(__DIR__.'/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}*/

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

class_alias(Barryvdh\DomPDF\Facade::class, 'PDF');

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();

$app->withEloquent();

$app->configure('mail');



/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
    // App\Http\Middleware\ExampleMiddleware::class
    App\Http\Middleware\CorsMiddleware::class
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'super_admin.auth' => App\Http\Middleware\JwtMiddleware::class,
    'admin.auth' => App\Http\Middleware\AdminMiddleware::class,
    'operator.auth' => App\Http\Middleware\OperatorMiddleware::class,
]);


/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

$app->register(\Barryvdh\DomPDF\ServiceProvider::class);

$app->configure('dompdf');

// register swagger
$app->register(\SwaggerLume\ServiceProvider::class);

// Swagger for api documentation
$app->configure('swagger-lume');

// opendox
$app->configure('opendox');

// opendox
$app->register(Noitran\Opendox\ServiceProvider::class);

// Larasupport for php artisan
$app->register(Irazasyed\Larasupport\Providers\ArtisanServiceProvider::class);

// laravel mail
$app->register(Illuminate\Mail\MailServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

// $app->alias('PDF', Barryvdh\DomPDF\Facade::class);

$app->configure('mail');
$app->alias('mailer', Illuminate\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\MailQueue::class);

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
