<?php

class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            $file = "Classes/" . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
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
$conn_id = FtpService::ftpConnect($settings);
$file = new FileController($conn_id);
$file->upload($settings["ftp_domain"]);
FtpService::ftpClose($conn_id);

