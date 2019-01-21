<?php

class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            $file = "Classes/" . str_replace('\\',
                    DIRECTORY_SEPARATOR,
                    $class) . '.php';
            if (file_exists($file)) {
                require $file;
                return true;
            }
            return false;
        });
    }
}

Autoloader::register();

$settings = include "config.php";

$uploadController = new UploadController($settings);
$uploadController->upload($_FILES['ShareX'], $_POST);

