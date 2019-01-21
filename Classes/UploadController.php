<?php

Class UploadController
{
    private $connId;
    private $fType;
    private $targetParts;
    private $localFile;
    private $tmpPath;
    private $domain;
    private $user;
    private $pass;
    private $server;

    function __construct( $settings)
    {
        $this->domain = $settings['ftpDomain'];
        $this->tokens = $settings['tokens'];
        $this->user = $settings['ftpUser'];
        $this->pass = $settings['ftpPass'];
        $this->server = $settings['ftpServer'];
    }

    private function isImage($fType)
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

    public function upload($files,$login)
    {
        $this->ftpConnect($login);
        if ($this->tmpLocalSave($files)) {
            if (ftp_fput(
                $this->connId,
                date("Ym") . '/' . end($this->targetParts),
                $this->localFile,
                $this->isImage($this->fType)
            )) {
                fclose($this->localFile);
                unlink($this->tmpPath);
                $this->ftpClose();
                print('http://' .
                    $this->domain . '/' .
                    date("Ym") . '/' .
                    end($this->targetParts));
            } else {
                print("Ошибка при загрузке файла на сервер");
            }
        }
    }

    private function tmpLocalSave($files)
    {
        $nameParts = explode(".", $files["name"]);
        $target = bin2hex(random_bytes(6)) . "." . end($nameParts);

        if (move_uploaded_file($files['tmp_name'],$target)) {
            $this->targetParts = explode('/' .
                date("Ym"), $target);
            $this->tmpPath = dirname(__DIR__) .
                '/' . end($this->targetParts);
            $this->fType = $nameParts;
            $this->localFile = fopen($this->tmpPath, 'r');
            $this->fType = explode('/', $files['type'])[0];
            $this->checkFtpDir();
            return true;
        } else {
            print 'Ошибка при сохранении на локальном сервере';
            return false;
        }
    }

    private function ftpConnect($login)
    {
        if ($this->tokens[$login['username']] != $login['token']) {
            die("Неверный токен или пользователь");
        }

        $connId = ftp_ssl_connect($this->server) or
        die('Не удалось установить соединение с ' . $this->server);

        if (!ftp_login($connId, $this->user, $this->pass)) {
            die('Не удалось установить соединение под логином ' . $this->user);
        } else {
            ftp_pasv ($connId,true);
        }
        $this->connId = $connId;
    }

    private function ftpClose()
    {
        ftp_close($this->connId);
    }
}

