<?php

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $senderName = ($_POST['name']) ? $_POST['name'] : '';
    $senderEmail = ($_POST['email']) ? $_POST['email'] : '';
    $emailContent = ($_POST['message']) ? $_POST['message'] : '';
    $errors = array();
    $response = '';
    $is_sent = false;

    if( empty($senderName) ) {
        $errors['name'] = 'Name is required.';
    }
    if( empty($senderEmail) ) {
        $errors['email'] = 'Email is required.';
    }

    $countErrors = ($errors) ? count($errors) : 0;

    if( $errors ) {
        $is_sent = false;
        if($countErrors>1) {
            $response = '<div class="errorMsg">Fill-in the required fields.</div>';
        } else {
            $response = '<div class="errorMsg">'.end($errors).'</div>';
        }
    } else {

        $datesent = date('m/d/Y h:i:s a');
        $subject = 'Web Contact Form ('.$senderEmail.')'; 
        $recipient = 'lisaqdebona@gmail.com';
        $a['sender_name'] = $senderName;
        $a['sender_email'] = $senderEmail;
        $a['subject'] = $subject;
        $a['message'] = $emailContent;
        $a['recipient'] = $recipient;
        $a['from_email'] = 'Web Form <webform@debonytes.com>';
        $a['sentvia'] = 'http://www.debonytes.com';
        $is_sent = send_email($a);
        if( $is_sent ) {
            $response = '<div class="successMg">Thanks for filling out the form! We will be in touch with you shortly.</div>';
        } else {
            $response = '<div class="errorMsg">Message failed to send.<br>Please try again.</div>';
        }
    }

    $out['sent'] = $is_sent;
    $out['message'] = $response;
    echo json_encode($out);
}
else {
    header("Location: ".$_SERVER["HTTP_REFERER"]);
}


function get_user_country_by_ip($ip) {
    if($ip) {
        $xml = simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=".$ip);
        return ($xml) ? $xml->geoplugin_countryName : "";
    }
}

function get_user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function send_email($a) {
    $userIP = get_user_ip();
    $userCountry = get_user_country_by_ip($userIP);
    $senderName = $a['sender_name'];
    $senderEmail = $a['sender_email'];
    $emailSubject = $a['subject'];
    $emailBody = $a['message'];
    $fromEmail = $a['from_email'];
    $recipient = $a['recipient'];
    $sentvia = $a['sentvia'];
    $isSent = false;
    if($recipient) {
        $to = $recipient;
        $subject = $emailSubject;
        $headers = 'From: ' . $fromEmail . "\r\n";
        $headers .= 'Reply-To: '.$senderEmail . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        $message = '<table style="border-collapse: collapse;border:1px solid #98d5e2;max-width:700px;width:100%;font-size:14px;line-height:1.4;"><tbody>';
        $message .= '<tr><td style="padding:10px;background:#f4fdff;"><table>';
        $message .= '<tr><td style="width:85px;vertical-align:top;">Sender Name</td><td style="width:10px;vertical-align:top;">:</td><td style="vertical-align:top;"><strong>'.$senderName.'</strong></td></tr>';
        $message .= '<tr><td style="width:85px;vertical-align:top;">Sender Email</td><td style="width:10px;vertical-align:top;">:</td><td style="vertical-align:top;"><strong>'.$senderEmail.'</strong></td></tr>';
        $message .= '<tr><td style="width:85px;vertical-align:top;">Subject</td><td style="width:10px;vertical-align:top;">:</td><td style="vertical-align:top;"><strong>'.$emailSubject.'</strong></td></tr>';
        $message .= '<tr><td colspan="3" style="padding:5px 0;"><p style="margin:10px 0px 5px 0px;"><strong>Message:</strong></p>'.$emailBody.'</td></tr>';
        $message .= '</table></td></tr>';
        $message .= '</tbody></table><br>';
        $message .= '<div>This email is sent via <em><a>'.$sentvia.'</a></em></div>';
        $message .= '<div>User IP: '.$userIP . '</div>';
        $message .= '<div>User Country: '.$userCountry . '</div>';

        if( mail($to, $subject, $message, $headers) ) {
            $isSent = true;
        } 
    }
    return $isSent;
}

?>