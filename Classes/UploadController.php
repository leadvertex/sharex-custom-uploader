<?php
class UploadController
{
    private $connId;
    private $newNameParts;
    private $localFile;
    private $tmpPath;
    private $domain;
    private $user;
    private $pass;
    private $server;
    private $port;
    private $timeout;
    private $tokens;
    private $ftpUseSsl;
    private $ftpBaseDir;

    public function __construct(
        $ftpDomain, $tokens, $ftpUser,
        $ftpPass, $ftpServer, $ftpTimeout = 90, $ftpPort = 21,
        $ftpUseSsl, $ftpBaseDir
    )
    {
        $this->domain = $ftpDomain;
        $this->tokens = $tokens;
        $this->user = $ftpUser;
        $this->pass = $ftpPass;
        $this->server = $ftpServer;
        $this->timeout = $ftpTimeout;
        $this->port = $ftpPort;
        $this->ftpUseSsl = $ftpUseSsl;
        $this->ftpBaseDir = $ftpBaseDir;
    }

    private function checkFtpDir()
    {
        if (ftp_pwd($this->connId) == DIRECTORY_SEPARATOR) {
            if (!ftp_chdir($this->connId, $this->ftpBaseDir)) {
                ftp_mkdir($this->connId, $this->ftpBaseDir .
                    date("Ym")
                );
            } else {
                ftp_mkdir(
                    $this->connId, date("Ym") .
                    DIRECTORY_SEPARATOR
                );
            }
        }
    }

    public function upload($files,$login)
    {
        $this->ftpConnect($login);
        $this->tmpLocalSave($files);
        $this->checkFtpDir();
        if (ftp_fput(
            $this->connId,
            date("Ym") . DIRECTORY_SEPARATOR .
            end($this->newNameParts), $this->localFile, FTP_BINARY
        )) {
            fclose($this->localFile);
            unlink($this->tmpPath);
            return($this->domain . DIRECTORY_SEPARATOR. $this->ftpBaseDir .
                date("Ym") . DIRECTORY_SEPARATOR .
                end($this->newNameParts));
        } else {
            unlink($this->tmpPath);
            throw new Exception("Ошибка при загрузке файла на сервер");
        }
    }

    private function tmpLocalSave($files)
    {
        $fileNameParts = explode(".", $files["name"]);
        $fileNewName = substr(hash("ripemd160", $files["name"]),
                0, 10) . "." . end($fileNameParts);

        if (move_uploaded_file($files['tmp_name'],$fileNewName)) {
            $this->newNameParts = explode(DIRECTORY_SEPARATOR .
                date("Ym"), $fileNewName);
            $this->tmpPath = dirname(__DIR__) .
                DIRECTORY_SEPARATOR . $fileNewName;
            $this->localFile = fopen($this->tmpPath, 'r');
        } else {
            throw new Exception('Ошибка при сохранении на локальном сервере');
        }
    }

    private function ftpConnect($login)
    {
        if ($this->tokens[$login['username']] != $login['token']) {
            throw new Exception(
                "Неверный токен или пользователь"
            );
        }

        if ($this->ftpUseSsl == true) {
            $connId = ftp_ssl_connect($this->server, $this->port,
                $this->timeout);
        } else {
            $connId = ftp_connect($this->server, $this->port,
                $this->timeout);
        }

        if ($connId == false) {
            throw new Exception(
                'Не удалось установить соединение с ' .
                $this->server
            );
        }

        if (!ftp_login($connId, $this->user, $this->pass)) {
            throw new Exception(
                'Неверное имя пользователя ftp'
            );
        } else {
            ftp_pasv($connId,true);
        }
        $this->connId = $connId;
    }

    public function __destruct()
    {
        if (isset($this->connId)) {
            ftp_close($this->connId);
        };
    }

}
