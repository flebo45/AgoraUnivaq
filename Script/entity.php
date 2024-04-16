<?php

require_once "autoload.php";

$pm = FPersistentManagerSQL::getInstance();

$user = $pm->retriveObj("EUser", 1);

echo($user->getId());



