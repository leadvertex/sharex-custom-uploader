<?php
class UploadController
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
    private $port;
    private $timeout;

    public function __construct($settings)
    {
        $this->domain = $settings['ftpDomain'];
        $this->tokens = $settings['tokens'];
        $this->user = $settings['ftpUser'];
        $this->pass = $settings['ftpPass'];
        $this->server = $settings['ftpServer'];
        $this->timeout = $settings['ftpTimeout'];
        $this->port = $this->checkPort($settings['ftpPort']);
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
            } else if(!ftp_chdir($this->connId, date("Ym") . '/')) {
                ftp_mkdir($this->connId, date("Ym") . '/');
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
                return('http://' .
                    $this->domain . '/scr/' .
                    date("Ym") . '/' .
                    end($this->targetParts));
            } else {
                unlink($this->tmpPath);
                return("Ошибка при загрузке файла на сервер");
            }
        } else {
            return 'Ошибка при сохранении на локальном сервере';
        }
    }

    private function tmpLocalSave($files)
    {
        $nameParts = explode(".", $files["name"]);
        $target = substr(hash("ripemd160",$files["name"]),0,10) . "." . end($nameParts);

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
            return false;
        }
    }

    private function ftpConnect($login)
    {
        if ($this->tokens[$login['username']] != $login['token']) {
            throw new Exception(
                "Неверный токен или пользователь",
                0
            );
        }

        $connId = ftp_ssl_connect($this->server,$this->port,$this->timeout);
        if ($connId == false) {
            throw new Exception(
                'Не удалось установить соединение с ' .
                $this->server,
                1
            );
        }

        if (!ftp_login($connId, $this->user, $this->pass)) {
            throw new Exception(
                'Неверное имя пользователя ftp: ' .
                $this->user,
                2
            );
        } else {
            ftp_pasv($connId,true);
        }
        $this->connId = $connId;
    }

    private function ftpClose()
    {
        ftp_close($this->connId);
    }

    private function checkPort($port)
    {
        if(!empty($port)){
            return $port;
        } else {
            return 21;
        }
    }
}

