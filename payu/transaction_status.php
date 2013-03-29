<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/payu.php');

$payu = new payu();
$server = 'www.platnosci.pl';
$url = '/paygw/UTF/Payment/get'; // żądamy danych w formacie xml

// PayU raportuje zmianę statusu transakcji - sprawdzenie czy przesłano wymagane parametry
if (!isset($_POST['pos_id']) || !isset($_POST['session_id']) || !isset($_POST['ts']) || !isset($_POST['sig'])) {
    exit(); // brak niektórych parametrów
}

// sprawdzenie czy pos_id należy do naszego sklepu
if ($_POST['pos_id'] != Configuration::get('PAYU_POSID')) {
    exit(); // nie nasz pos_id
}

// sprawdzenie podpisu przesłanych parametrów
$sig = md5(
    $_POST['pos_id'] .
    $_POST['session_id'] .
    $_POST['ts'] .
    Configuration::get('PAYU_AUTHKEY2')
);
if ($_POST['sig'] != $sig) {
    exit(); // błędny podpis
}

// jak na razie wszystko OK - teraz odpytanie o status z naszej strony
$ts = time();
$sig = md5(
    Configuration::get('PAYU_POSID') .
    $_POST['session_id'] .
    $ts .
    Configuration::get('PAYU_AUTHKEY1')
);

$params = "pos_id=" . Configuration::get('PAYU_POSID') .
    "&session_id=" . $_POST['session_id'] .
    "&ts=" . $ts .
    "&sig=" . $sig;

$header = 'POST ' . $url . ' HTTP/1.0' . "\r\n" .
        'Host: ' . $server . "\r\n" .
        'Content-Type: application/x-www-form-urlencoded' . "\r\n".
        'Content-Length: ' . strlen($params) . "\r\n" .
        'Connection: close' . "\r\n\r\n";

if (!($fp = @fsockopen('ssl://' . $server, 443, $errno, $errstr, 30))) {
    exit(); // połączenie niemożliwe
}

// żądanie do PayU
@fputs($fp, $header . $params);

// odczyt z PayU
$response = '';
while (!@feof($fp)) {
    $res = @fgets($fp, 1024);
    $response .= $res;
}
// zamknięcie gniazda
@fclose($fp);

// jak na razie wszystko OK - dekodowanie odpowiedzi
$parts = array();
preg_match('#<trans>.*' .
    '<pos_id>([0-9]*)</pos_id>.*' .     /* 1 */
    '<session_id>(.*)</session_id>.*' . /* 2 */
    '<order_id>(.*)</order_id>.*' .     /* 3 */
    '<amount>([0-9]*)</amount>.*' .     /* 4 */
    '<status>([0-9]*)</status>.*' .     /* 5 */
    '<desc>(.*)</desc>.*' .             /* 6 */
    '<desc2>(.*)</desc2>.*' .           /* 7 */
    '<ts>([0-9]*)</ts>.*' .             /* 8 */
    '<sig>([a-z0-9]*)</sig>.*' .        /* 9 */
    '</trans>#is', $response, $parts
);

$status = -1; // zakładamy najgorsze, tj. nierozpoznany status

if (count($parts) >= 9) {
    if ($parts[1] != Configuration::get('PAYU_POSID')) {
        $status = -1; // błędny pos_id
    }
    $sig = md5(
        $parts[1] . $parts[2] . $parts[3] . $parts[5] . $parts[4] . $parts[6] . $parts[8] .
        Configuration::get('PAYU_AUTHKEY2')
    );
    if ($parts[9] != $sig) {
        $status = -1; // błędny podpis
    }
    // status transakcji
    switch ($parts[5]) {
        case 1: $status = Configuration::get('PAYU_STATUS_NEW'); break;
        case 2: $status = Configuration::get('PAYU_STATUS_CANCELED'); break;
        case 3: $status = Configuration::get('PAYU_STATUS_DECLINED'); break;
        case 4: $status = Configuration::get('PAYU_STATUS_STARTED'); break;
        case 5: $status = Configuration::get('PAYU_STATUS_PENDING'); break;
        case 7: $status = Configuration::get('PAYU_STATUS_DECLINED'); break;
        case 99: $status = Configuration::get('PAYU_STATUS_CLOSED'); break;
        case 888: $status = Configuration::get('PAYU_STATUS_ERROR'); break;
        default: $status = -1;
    }
}

if ($status == -1) {
    exit(); // nierozpoznany status
}

// dekodowanie session_id - zawiera nr koszyka i znacznik czasu
$cartId = intval(explode('_', $parts[2])[0]);
$orderId = Order::getOrderByCartId($cartId);

if (!$orderId) {
    exit('OK'); // nie zdołano utworzyć zamówienia, więc utnij dalszy dialog z PayU
}

$history = new OrderHistory();
$history->id_order = intval($orderId);

// na wypdek błędu, kiedy PayU wysyła ten sam status po kilkadziesiąt razy
if ($history->state_id != $status) {
    $history->changeIdOrderState($status, $orderId);
    $history->addWithemail(TRUE);
}

// zakończ obsługę
exit('OK');

