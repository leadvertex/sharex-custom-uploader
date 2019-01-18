<?php
class ftpService
{
    static function ftpConnect($settings)
    {
        $tokenArray = $settings['tokens'];
        if (in_array([$_POST['username'] => $_POST['token']],$tokenArray)) {
            die("Верный токен");
        } else {
            die("Неверный токен");
        }

        $user = $settings['ftp_user'];
        $pass = $settings['ftp_pass'];
        $server = $settings['ftp_server'];
        $conn_id = ftp_ssl_connect($server) or die('Не удалось установить соединение с ' . $server);

        if (!ftp_login($conn_id, $user, $pass)) {
            die('Не удалось установить соединение под логином '.$user);
        } else {
            ftp_pasv ($conn_id,true);
        }
        return $conn_id;
    }

    static function ftpClose($conn_id)
    {
        ftp_close($conn_id);
    }
}

