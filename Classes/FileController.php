<?php

Class FileController
{
    private $connId;
    private $fType;
    private $targetParts;
    private $localFile;
    private $tmpPath;

    function __construct($connId)
    {
        $this->connId = $connId;
    }

    public function isImage($fType)
    {
        if ($fType == 'image') {
            return FTP_IMAGE;
        } else {
            return FTP_AUTOSEEK;
        }
    }

    private function checkFtpDir()
    {
        if (ftp_pwd($this->connId) == '/') {
            if (!ftp_chdir($this->connId, 'scr/')) {
                ftp_mkdir($this->connId, 'scr/' . date("Ym"));
            }
        }
    }

    public function upload($domain)
    {
        if ($this->tmpLocalSave()) {
            if (ftp_fput(
                $this->connId,
                date("Ym") . '/' . end($this->targetParts),
                $this->localFile,
                $this->isImage($this->fType)
            )) {
                fclose($this->localFile);
                unlink($this->tmpPath);
                print('http://' .
                    $domain . '/' .
                    date("Ym") . '/' .
                    end($this->targetParts));
            } else {
                print("Ошибка при загрузке файла на сервер");
            }
        }
    }

    private function tmpLocalSave()
    {
        $nameParts = explode(".", $_FILES["ShareX"]["name"]);
        $target = bin2hex(random_bytes(6)) . "." . end($nameParts);

        if (move_uploaded_file($_FILES['ShareX']['tmp_name'],$target)) {
            $this->targetParts = explode('/' .
                date("Ym"), $target);
            $this->tmpPath = dirname(__DIR__) .
                '/' . end($this->targetParts);
            $this->fType = $nameParts;
            $this->localFile = fopen($this->tmpPath, 'r');
            $this->fType = explode('/', $_FILES['ShareX']['type'])[0];
            $this->checkFtpDir();
            return true;
        } else {
            print 'Ошибка при сохранении на локальном сервере';
            return false;
        }
    }
}

