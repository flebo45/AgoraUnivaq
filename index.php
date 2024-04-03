<?php
require_once "autoload.php";
require_once "StartSmarty.php";
require_once "Installation.php";

Installation::install();
$fc = new CFrontController();
$fc->run($_SERVER['REQUEST_URI']);