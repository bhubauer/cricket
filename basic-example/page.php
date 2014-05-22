<?php


require_once("entry.php");

use cricket\core\Dispatcher;

cricket\utils\Utils::error_print_r($_SERVER);

$dispatcher = new Dispatcher(null, null, null, array(
    '../lib/cricket' => '../lib/cricket',
));
$dispatcher->dispatchRequest();

