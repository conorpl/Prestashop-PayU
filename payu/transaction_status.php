<?php

// DEBUG

$get = var_export( $_GET, true );
$post = var_export( $_POST, true );
$file = dirname(__FILE__) . '/log.txt';


// file_put_contents($file, '======================= ' . date('c') . " =======================\n\n", FILE_APPEND);
// file_put_contents($file, 'IP: ' . $_SERVER['REMOTE_ADDR'] . ' Host: ' . $_SERVER['REMOTE_HOST'] . "\n\n", FILE_APPEND);
// file_put_contents($file, "GET\n", FILE_APPEND);
// file_put_contents($file, $get, FILE_APPEND);
// file_put_contents($file, "\n\nPOST\n", FILE_APPEND);
// file_put_contents($file, $post, FILE_APPEND);

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/payu.php');

$payu      = new payu();
$server    = 'www.platnosci.pl';
$url       = '/paygw/UTF/Payment/get/txt';

//TODO: XML

$ts        = time();
$sig       = md5( Configuration::get('PAYU_POSID') . $_POST['session_id'] . $ts . Configuration::get('PAYU_AUTHKEY1'));
$params    = "pos_id=" . Configuration::get('PAYU_POSID') . "&session_id=" . $_POST['session_id'] . "&ts=" . $ts . "&sig=" . $sig;

$header = 'POST '.$url.' HTTP/1.0'."\r\n" .
        'Host: '.$server."\r\n".
        'Content-Type: application/x-www-form-urlencoded'."\r\n".
        'Content-Length: '.strlen($params)."\r\n".
        'Connection: close'."\r\n\r\n";

$fp = @fsockopen('ssl://' . $server, 443, $errno, $errstr, 30);

if ( !$fp )
{
	// file_put_contents($file, 'Nie można połączyć się z ' . $server . ' ' . $url , FILE_APPEND);
    exit('Nie można połączyć z ' . $server . ' metoda ' . $url);
}

fputs($fp, $header.$params);
$verified = false;
$read = '';

while (!feof($fp))
{
    $line = fgets($fp, 1024);
	
	// file_put_contents($file, $line , FILE_APPEND);
    
    if (substr($line,0,6)=="trans_")
    {
        $v = explode(":",$line);
        $res[$v[0]] = trim($v[1]);
    }
    $read .= $line;
}

fclose ($fp);

$status = -1;

switch($res['trans_status'])
{
    case 1: 
        $status = Configuration::get('PAYU_STATUS_NEW');
        break;
    
    case 2:
        $status = Configuration::get('PAYU_STATUS_CANCELED');
        break;
        
    case 3:
        $status = Configuration::get('PAYU_STATUS_DECLINED');
        break;
        
    case 4:
        $status = Configuration::get('PAYU_STATUS_STARTED');
        break;
        
    case 5:
        $status = Configuration::get('PAYU_STATUS_PENDING');
        break;
        
    case 7:
        $status = Configuration::get('PAYU_STATUS_DECLINED');
        break;

    case 99:
        $status = Configuration::get('PAYU_STATUS_CLOSED');
        break;
       
    case 888:
        $status = Configuration::get('PAYU_STATUS_ERROR');
        break;
        
    default:
        $status = -1;
        break;
}

if ( $status == -1 )
{
	// file_put_contents($file, 'Nieznany status' , FILE_APPEND);
    exit('Nieznany status.' . htmlentities($res['trans_status'], ENT_COMPAT, 'UTF-8'));
}

$cartId    = intval($res['trans_session_id']);
$orderId   = Order::getOrderByCartId($cartId);

$history = new OrderHistory();
$history->id_order = intval($orderId);

// na wypdek błędu, kiedy platnosci.pl wysylaja ten sam status po klikadzisiat razy
if ( $history->state_id != $status )
{
    $history->changeIdOrderState($status, $orderId);
    $history->addWithemail(true);
}

// file_put_contents($file, 'OK' , FILE_APPEND);
// file_put_contents($file, "\n\n======================= END =======================\n\n", FILE_APPEND);

exit('OK');
