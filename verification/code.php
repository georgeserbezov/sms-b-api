<?php
    error_reporting(E_ALL);
    ini_set('display_errors',1);

    include_once '../object/user.php';
    include_once '../object/verification_code.php';
    include_once '../object/attempts.php';

    include_once '../config/database.php';
    include_once '../config/int-send_sms.php';

    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    
    
    $database = new Database();
    $db = $database->getConnection();
    $data = json_decode(file_get_contents("php://input"));

    //BulkSMS Auth - DO NOT CHANGE!
    $username = 'gserbezov';
    $password = 'Thisismysmsbumptest1';
    $url = 'https://api-legacy2.bulksms.com/eapi/submission/send_sms/2/2.0';

    $uid = $data->uid;

    $code = new VerificationCode($db);
    $attempt = new Attempts($db);
    $verifyCode = $code->getCode($data->code);

    if ($verifyCode->rowCount() === 1) {
        $row = $verifyCode->fetch();
        $user = new User($db);
        $phone = $user->getPhone($row['user_id'])['phone'];
        $msg = "Welcome to SMS Bump";

        $message_body = prepare_message($username, $password, $msg, $phone);
        send_message( $message_body, $url );

        $user->setVerified($row['user_id']);
        
        echo json_encode(["success" => true]);
        return;
    }

    $countAttempts = $attempt->check($uid)->rowCount();
    if ($countAttempts === 0 || $countAttempts%3 > 0 ) {
        $attempt->setCode($data->code);
        $attempt->setUserId($uid);
        $attempt->create();
    } else {
        echo json_encode(["freeze" => true]);
        return;
    }

    
    // TODO: Save code to Attempts
    echo json_encode(["success" => false]);
    return;
    
?>