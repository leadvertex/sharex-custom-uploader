<?php
class FtpService
{
    public static function ftpConnect($settings)
    {
        $tokenArray = $settings['tokens'];
        if ($tokenArray[$_POST['username']] != $_POST['token']) {
            die("Неверный токен или пользователь");
        }

        $user = $settings['ftpUser'];
        $pass = $settings['ftpPass'];
        $server = $settings['ftpServer'];
        $connId = ftp_ssl_connect($server) or
        die('Не удалось установить соединение с ' . $server);

        if (!ftp_login($connId, $user, $pass)) {
            die('Не удалось установить соединение под логином ' . $user);
        } else {
            ftp_pasv ($connId,true);
        }
        return $connId;
    }

    public static function ftpClose($connId)
    {
        ftp_close($connId);
    }
}

