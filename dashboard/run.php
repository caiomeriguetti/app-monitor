<?php

include 'config.php';

$name=$_GET["task"];

if (empty($name)) {
    throw new Exception("task cannot be empty");
}

$taskFile = $CONFIG["tasks-dir"] . $name . '.sh';

if (!file_exists($taskFile)) {
    throw new Exception("Task ".$name.' doesnt exists');
}


shell_exec('/bin/bash '.$taskFile.' > /tmp/task-'.$name);

