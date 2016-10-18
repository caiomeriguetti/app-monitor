<?php

$pass = $_GET["pass"];

if (empty($pass)) {
    throw new Exception("Unauthorized");
}
