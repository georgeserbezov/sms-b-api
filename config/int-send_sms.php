<?php

// Code from BulkSMS Developers documentation
  function send_message ( $post_body, $url ) {
    $ch = curl_init( );
    curl_setopt ( $ch, CURLOPT_URL, $url );
    curl_setopt ( $ch, CURLOPT_POST, 1 );
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_body );
    curl_setopt ( $ch, CURLOPT_TIMEOUT, 20 );
    curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 20 );

    $response_string = curl_exec( $ch );
    $curl_info = curl_getinfo( $ch );

    $sms_result = array();
    $sms_result['success'] = 0;
    $sms_result['details'] = '';
    $sms_result['transient_error'] = 0;
    $sms_result['http_status_code'] = $curl_info['http_code'];
    $sms_result['api_status_code'] = '';
    $sms_result['api_message'] = '';
    $sms_result['api_batch_id'] = '';

    if ( $response_string == FALSE ) {
      $sms_result['details'] .= "cURL error: " . curl_error( $ch ) . "\n";
    } elseif ( $curl_info[ 'http_code' ] != 200 ) {
      $sms_result['transient_error'] = 1;
      $sms_result['details'] .= "Error: non-200 HTTP status code: " . $curl_info[ 'http_code' ] . "\n";
    }
    else {
      $sms_result['details'] .= "Response from server: $response_string\n";
      $api_result = explode( '|', $response_string );
      $status_code = $api_result[0];
      $sms_result['api_status_code'] = $status_code;
      $sms_result['api_message'] = $api_result[1];
      if ( count( $api_result ) != 3 ) {
        $sms_result['details'] .= "Error: could not parse valid return data from server.\n" . count( $api_result );
      } else {
        if ($status_code == '0') {
          $sms_result['success'] = 1;
          $sms_result['api_batch_id'] = $api_result[2];
          $sms_result['details'] .= "Message sent - batch ID $api_result[2]\n";
        }
        else if ($status_code == '1') {
          $sms_result['success'] = 1;
          $sms_result['api_batch_id'] = $api_result[2];
        }
        else {
          $sms_result['details'] .= "Error sending: status code [$api_result[0]] description [$api_result[1]]\n";
        }

      }
    }
    curl_close( $ch );

    return $sms_result;
  }

  function prepare_message ( $username, $password, $message, $phone ) {
    $post_fields = array (
    'username' => $username,
    'password' => $password,
    'message'  => character_resolve( $message ),
    'msisdn'   => $phone,
    'allow_concat_text_sms' => 0,
    'concat_text_sms_max_parts' => 2
    );

    return make_post_body($post_fields);
  }

  function make_post_body($post_fields) {
    $stop_dup_id = make_stop_dup_id();
    if ($stop_dup_id > 0) {
      $post_fields['stop_dup_id'] = make_stop_dup_id();
    }
    $post_body = '';
    foreach( $post_fields as $key => $value ) {
      $post_body .= urlencode( $key ).'='.urlencode( $value ).'&';
    }
    $post_body = rtrim( $post_body,'&' );

    return $post_body;
  }

  function character_resolve($body) {
    $ret_msg = '';
    if( mb_detect_encoding($body, 'UTF-8') != 'UTF-8' ) {
      $body = utf8_encode($body);
    }
    for ( $i = 0; $i < mb_strlen( $body, 'UTF-8' ); $i++ ) {
      $c = mb_substr( $body, $i, 1, 'UTF-8' );
      $ret_msg .= $c;
    }
    return $ret_msg;
  }

  function make_stop_dup_id() {
    return 0;
  }
?>