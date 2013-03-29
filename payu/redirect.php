<?php

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../header.php');
include(dirname(__FILE__) . '/payu.php');

if (!$cookie->isLogged()) {
    Tools::redirect('authentication.php?back=order.php');
}

// przygotowanie nowej płatności

$payu = new payu();
$cart = new Cart(intval($cookie->id_cart));
$customer = new Customer(intval($cart->id_customer));
$address = new Address(intval($cart->id_address_invoice));

// odczyt konfiguracji PayU
$posId = Configuration::get('PAYU_POSID');
$posAuthKey = Configuration::get('PAYU_POSAUTHKEY');
$authKey1 = Configuration::get('PAYU_AUTHKEY1');

// dane adresowe klienta
$firstName = $address->firstname;
$lastName = $address->lastname;
$clientsEmail = $customer->email;

// znacznik czasu
$ts = time();

// dane zamówienia
$amount = $cart->getOrderTotal() * 100;
$sessionId = intval($cart->id) . '_' . $ts; // identyfikator sesji to nr koszyka i znacznik czasowy
$desc = 'Zamówienie ' . intval($cart->id) . ' w sklepie ' . Configuration::get('PS_SHOP_DOMAIN');

// podpis przesyłanych danych
$sig = md5(
    $posId .
    $sessionId .
    $posAuthKey .
    $amount .
    $desc .
    $firstName .
    $lastName .
    $clientsEmail .
    $_SERVER['REMOTE_ADDR'] .
    $ts .
    $authKey1
);

global $smarty;

// skompletowanie danych dla Payu
$smarty->assign(array(
    'firstName' => $firstName,
    'lastName' => $lastName,
    'clientsEmail' => $clientsEmail,
    'posId' => $posId,
    'posAuthKey' => $posAuthKey,
    'sessionId' => $sessionId,
    'amount' => $amount,
    'desc' => $desc,
    'clientsIp' => $_SERVER['REMOTE_ADDR'],
    'ts' => $ts,
    'sig' => $sig,
));

// utworzenie i wyświetlenie formularza przejścia do PayU
if (is_file(_PS_THEME_DIR_ . 'modules/payu/redirect.tpl')) {
    $smarty->display(_PS_THEME_DIR_ . 'modules/' . $payu->name . '/redirect.tpl');
} else {
    $smarty->display(_PS_MODULE_DIR_ . $payu->name . '/redirect.tpl');
}

include_once(dirname(__FILE__) . '/../../footer.php');

