<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$app = new App\Core\Application();
$app->run();
