<?php
/**
iOS Send PushNotification
 */
class PushNotification {

    /* if set false then use APNS Production Server*/
    private $isSandbox = TRUE;

    private $Server = array('sandbox' => 'ssl://gateway.sandbox.push.apple.com:2195', 'production' => 'ssl://gateway.push.apple.com:2195');

    /* Make sure this is set to the password that you set for your private key when you exported it to the .pem file using openssl on your OS X */
    private $privateKeyPassword = 'nezz';

    /* Replace this with the name of the file that you placed by your PHP script file, containing your private key and certificate that you generated earlier. you may need full path */
    private $pushCertAndKeyPemFile = 'push_notification/PushCertificateAndKey.pem';

    public function Notification($collection = array()) {
        $this->SendNow($collection['deviceToken'],$collection['message']);
    }

    private function SendNow($deviceToken,$message) {
        $stream = stream_context_create();
        stream_context_set_option($stream,'ssl','passphrase',$this->privateKeyPassword);
        stream_context_set_option($stream,'ssl','local_cert',$this->pushCertAndKeyPemFile);
        $connectionTimeout = 30;
        $connectionType = STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT;
        $connection = NULL;
        if ($this->isSandbox) {
            $connection = stream_socket_client($this->Server['sandbox'], $errorNumber, $errorString, $connectionTimeout,$connectionType,$stream);
        } else {
            $connection = stream_socket_client($this->Server['production'], $errorNumber, $errorString, $connectionTimeout,$connectionType,$stream);
        }
        if (!$connection){
            echo "Failed to connect to the APNS server. Error = $errorString <br/>";
            return;
        } else {
            echo "Successfully connected to the APNS. Processing...<br/>";
        }
        $messageBody['aps'] = array('alert' => $message, 'sound' => 'default','badge' => 1);
        $payload = json_encode($messageBody);
        foreach ($deviceToken as $value) {
            $notification = chr(0) . pack('n', 32) . pack('H*', $value) . pack('n', strlen($payload)) . $payload;
            $wroteSuccessfully = fwrite($connection, $notification, strlen($notification));
            if (!$wroteSuccessfully){
                echo "Could not send the message<br/>";
            } else {
                echo "Successfully sent the message<br/>";
            }
        }
    }
}

