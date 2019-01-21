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
                require_once $file;
                return true;
            }
            return false;
        });
    }
}

Autoloader::register();

$settings = include "config.example.php";

try {
    error_reporting(E_ALL ^ E_WARNING);
    $uploadController = new UploadController($settings);
    $uploadController->upload($_FILES['ShareX'], $_POST);
} catch (Exception $e){
    print($e);
}
