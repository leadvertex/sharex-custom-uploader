<?php

error_reporting(0);
//error_reporting(E_ALL);

class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            $file = "Classes" . DIRECTORY_SEPARATOR . $class . '.php';
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
            return false;
        });
    }
}

Autoloader::register();

$settings = require_once __DIR__ . DIRECTORY_SEPARATOR . "config.php";

try {
    if(empty($_POST)) {
        throw new Exception('POST пуст!');
    }
    try {
        $uploadController = new UploadController(
            $settings['ftpDomain'], $settings['tokens'], $settings['ftpUser'],
            $settings['ftpPass'], $settings['ftpServer'],
            $settings['ftpTimeout'], $settings['ftpPort'],
            $settings['ftpUseSsl'], $settings['ftpBaseDir']
        );
        print_r($uploadController->upload($_FILES['ShareX'], $_POST));
    }catch (Exception $e) {
        throw $e;
    }
} catch (Exception $e) {
    print($e->getMessage());
}