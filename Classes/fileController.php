<?php

Class fileController{

    private $conn_id;
    private $fType;
    private $target_parts;
    private $localFile;
    private $tmpPath;

    function __construct($conn_id)
    {
        $this->conn_id = $conn_id;
    }

    public function isImage($fType){
        if ($fType=='image'){return FTP_IMAGE;}else{return FTP_AUTOSEEK;}
    }

    private function checkFtpDir(){
        if (ftp_pwd($this->conn_id)=='/') {
            if (!ftp_chdir($this->conn_id, 'scr/')) {
                ftp_mkdir($this->conn_id, 'scr/' . date("Ym"));
            }
        }

    }
    public function upload()
    {
        if ($arr = $this->TMPLocalSave()){

            if(ftp_fput ($this->conn_id, date("Ym").'/'.end($this->target_parts),$this->localFile,$this->isImage($this->fType))){
                fclose($this->localFile);
                unlink($this->tmpPath);
                echo 'http://scr.lvrtx.com/'.date("Ym").'/'. end($this->target_parts);
            }else {
                print("Ошибка при загрузке файла на сервер");

            }

        }

    }


    private function TMPLocalSave(){
        $nameParts = explode(".", $_FILES["ShareX"]["name"]);
        $target = bin2hex(random_bytes(6)) . "." . end($nameParts);

        if(move_uploaded_file($_FILES['ShareX']['tmp_name'],$target)){
            $this->target_parts = explode('/'.date("Ym"), $target);
            $this->tmpPath = dirname(__DIR__).'/'. end($this->target_parts);
            $this->fType = $nameParts;
            $this->localFile = fopen($this->tmpPath,'r');

            $this->fType = explode('/',$_FILES['ShareX']['type'])[0];
            $this->checkFtpDir();

            return true;

        }else {
            print 'Ошибка при сохранении на локальном сервере';
            return false;
        }
    }

}