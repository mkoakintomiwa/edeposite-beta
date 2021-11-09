<?php
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
$loader = new FilesystemLoader(__DIR__);
$twig = new Environment($loader);

?>