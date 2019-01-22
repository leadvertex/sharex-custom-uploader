<?php

error_reporting(0);

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

$settings = require_once "config.php";

if(!empty($_POST)) {
    try {
        $uploadController = new UploadController($settings);
        print($uploadController->upload($_FILES['ShareX'], $_POST));
    } catch (Exception $e) {
        print($e);
    }
} else {
    echo "POST пуст!";
}
