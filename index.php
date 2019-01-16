<?php
include "Classes/ftpService.php";
include "Classes/fileController.php";


$settings = include "config.php";

$conn_id = ftpService::FTPConnect($settings);

$file = new fileController($conn_id);

$file->upload();

ftpService::FTPClose($conn_id);





