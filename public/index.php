<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use DI\Container;
use Infrastructure\Database\DatabaseConnectionInterface;
use Infrastructure\Database\Migrations\CreateTables;

require dirname(__DIR__) . '/vendor/autoload.php';

// Create a new container instance using PHP-DI
$container = new Container();

// Load settings
$settings = require __DIR__ . '/../app/settings.php';
$container->set('settings', $settings);

// Set the container to the Slim app
AppFactory::setContainer($container);

// Path to the SQLite database file
$dbFile = dirname(__DIR__) . '/database/chatapp.db';

// Include repositories
$repositories = require __DIR__ . '/../app/repositories.php';
$repositories($container);

// Include the dependencies
$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($container);

// Include middleware
$middleware = require __DIR__ . '/../app/middleware.php';
$middleware($container);

// Create the Slim App with the container
$app = AppFactory::create();

// Include routes
$routes = require __DIR__ . '/../app/routes.php';
$routes($app);

// Run migration to create tables
CreateTables::run($container->get(DatabaseConnectionInterface::class)->getConnection());

// Run the app
$app->run();