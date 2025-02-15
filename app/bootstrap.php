<?php

declare(strict_types=1);

/**
 * This file is part of nextphp.
 *
 * @link     https://github.com/next-laboratory
 * @license  https://github.com/next-laboratory/next/blob/master/LICENSE
 */

use App\Logger;
use Dotenv\Dotenv;
use Next\Config\Repository;
use Next\Di\Context;
use Next\Event\ListenerProvider;
use Next\Utils\Filesystem;

require_once BASE_PATH . '/vendor/autoload.php';

$container = Context::getContainer();

register_shutdown_function(function () use ($container) {
    if ($error = error_get_last()) {
        $container->make(Logger::class)->error($error['message'], [
            'type' => $error['type'],
            'file' => $error['file'],
            'line' => $error['line'],
        ]);
    }
});

// Initialize environment variables and configurations
if (file_exists($envFile = base_path('.env'))) {
    if (method_exists('Dotenv\Dotenv', 'createUnsafeImmutable')) {
        Dotenv::createUnsafeImmutable(base_path())->load();
    } else {
        Dotenv::createMutable(base_path())->load();
    }
}

//$repository = $container->make(Repository::class);
//$files      = (new Filesystem())->files(base_path('./config'), pattern: '*.php');
//foreach ($files as $file) {
//    $repository->set(pathinfo($file->getBasename(), PATHINFO_FILENAME), include $file);
//}

// Initialize bindings
//foreach ($repository->get('app.bindings', []) as $id => $value) {
//    $container->bind($id, $value);
//}

// Initialize event listeners
//$listenerProvider = $container->make(ListenerProvider::class);
//if (!empty($listeners = $repository->get('app.listeners', []))) {
//    foreach ($listeners as $listener) {
//        $listenerProvider->addListener($container->make($listener));
//    }
//}
