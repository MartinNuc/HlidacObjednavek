<?php

use Nette\Diagnostics\Debugger, Nette\Application\Routers\Route;
use Nette\Forms\Container;

// Load Nette Framework
$params['libsDir'] = __DIR__ . '/../libs';
require $params['libsDir'] . '/Nette/loader.php';

// Enable Nette Debugger for error visualisation & logging
Debugger::$logDirectory = __DIR__ . '/../log';
Debugger::$strictMode = TRUE;
Debugger::enable();

//require $params['libsDir'] . '/dibi/dibi.php';
// Load configuration from config.neon file
/*$configurator = new Nette\Configurator;
$configurator->container->params += $params;
$configurator->container->params['tempDir'] = __DIR__ . '/../temp';
$container = $configurator->loadConfig(__DIR__ . '/config.neon');*/

$configurator = new Nette\Config\Configurator;
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->addDirectory($params['libsDir'])
    ->register();
$configurator->addParameters($params);
$configurator->addConfig(__DIR__ . '/config.neon');
$container = $configurator->createContainer();

dibi::connect( $container->params['database'] );

$configurator->onCompile[] = function ($configurator, $compiler) {
    $compiler->addExtension('dibi', new DibiNetteExtension);
};

// Setup router
$router = $container->router;
$router[] = new Route('index.php', 'Sign:in', Route::ONE_WAY);
$router[] = new Route('', 'Sign:in');
$router[] = new Route('<presenter>/<action>[/<id>]', 'Sign:');

Container::extensionMethod('addDatePicker', function (Container $container, $name, $label = NULL) {
    return $container[$name] = new JanTvrdik\Components\DatePicker($label);
});

\Nette\Diagnostics\Debugger::$bar->addPanel(new SessionPanel($container->session));

// Configure and run the application!
$application = $container->application;
//$application->catchExceptions = TRUE;
$application->errorPresenter = 'Error';
$application->run();
