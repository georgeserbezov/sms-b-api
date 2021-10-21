<?php
    error_reporting(E_ALL);
    ini_set('display_errors',1);

    include_once '../object/user.php';
    include_once '../object/verification_code.php';

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

    $user = new User($db);
    $code = new VerificationCode($db);
    
    if(
        !empty($data->email) &&
        !empty($data->phone) &&
        !empty($data->password)
    ) {
        $user->setEmail($data->email);
        $user->setPhone($data->phone);
        $user->setPassword($data->password);

        $uid = $user->create();
        if($uid) {
            // Random number for code
            $msgCode = rand(1000000, 9999999);
            $phone = $data->phone;
            $msg = "Verification code: " . $msgCode;

            $message_body = prepare_message($username, $password, $msg, $phone);
            $result = send_message( $message_body, $url );

            if($result['success']) {
                $codeMessage = "Sent successfully!";

                // Save verification code in DB
                $code->setCode($msgCode);
                $code->setUserId($uid);
                $code->create();
            } else {
                $codeMessage = "Failed!";
            }

            http_response_code(201);
            echo json_encode(array("success" => true, "uid" => $uid));

        } else {
            http_response_code(503);
            echo json_encode(array("success" => false));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("success" => false));
    }
?>