<?php 

    //echo $pass= $generalobj->decrypt("XcIZDZwoXA==");exit;
    $deviceToken = $_REQUEST['Token'];
    //5240381e085cf439d5bda4f322440fc0b9cd750315b91c725cfdc12996545eb1

    // Put your private key's passphrase here:
    $passphrase = '123456';

    // Put your alert message here:
    $message['key'] = 'push notification!';

    $message_json = json_encode($message);
    ////////////////////////////////////////////////////////////////////////////////

    $ctx = stream_context_create();
    //        stream_context_set_option($ctx, 'ssl', 'local_cert', 'apn-dev-uberapp.pem');'driver_apns_dev.pem'
    stream_context_set_option($ctx, 'ssl', 'local_cert', $_REQUEST['pemName']);
    stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

    // Open a connection to the APNS server
    $fp = stream_socket_client(
        'ssl://gateway.push.apple.com:2195', $err,
        $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
    echo "<BR/>fp:" . $fp . "<BR/>";

    if (!$fp) {
        exit("Failed to connect: $err $errstr" . PHP_EOL);
    }

    echo 'Connected to APNS' . PHP_EOL;
    // $msg = "{\"iDriverId\":\"20\"}";
    // Create the payload body
    $body['aps'] = array(
        'alert' => $_REQUEST['message'],
        'content-available' => 1,
        'body' => $_REQUEST['message'],
        'sound' => 'default',

    );

    // Encode the payload as JSON
    $payload = json_encode($body);

    // Build the binary notification
    $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

    // Send it to the server
    $result = fwrite($fp, $msg, strlen($msg));

    if (!$result) {
        echo 'Message not delivered' . PHP_EOL;
    } else {
        echo 'Message successfully delivered' . PHP_EOL;
    }

    // Close the connection to the server
    fclose($fp);

?>