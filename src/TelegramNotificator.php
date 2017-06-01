<?php

namespace wh1te_w0lf\webhook_deploy;

use wh1te_w0lf\webhook_deploy\base\Notificator;

class TelegramNotificator extends Notificator {

    protected $_botKey = '';
    protected $_chatId = '';

    public function notificate($message) {
        $ch = curl_init();
        $url = "https://api.telegram.org/bot{$this->_botKey}/sendMessage";

        curl_setopt_array($ch, [
            CURLOPT_TIMEOUT => 30,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_NONE,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => ["Content-type: application/json"],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                "chat_id" => $this->_chatId,
                "text" => $message,
                "parse_mode" => "HTML"
            ])
        ]);

        $response = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);
        $json = json_decode($response, true);
        if (is_array($json) && !empty($json['ok'])) {
            return true;
        }
        return false;
    }

}