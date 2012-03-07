<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/payu.php');

if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=order.php');

$payu           = new payu();
$cart           = new Cart(intval($cookie->id_cart));
$customer       = new Customer(intval($cart->id_customer));
$address        = new Address(intval($cart->id_address_invoice));

// konfiguracja

$posId          = Configuration::get('PAYU_POSID');
$posAuthKey     = Configuration::get('PAYU_POSAUTHKEY');
//$authKey1       = Configuration::get('PAYU_AUTHKEY1');


// dane adresowe

$firstName      = $address->firstname;
$lastName       = $address->lastname;
$clientsEmail   = $customer->email;


// dane zamowienia

$amount         = $cart->getOrderTotal() * 100;
$sessionId      = intval($cart->id);
$desc           = 'Zamówienie ' . intval($cart->id) . ' w sklepie ' . Configuration::get('PS_SHOP_DOMAIN');


global $smarty;

$smarty->assign(array(
    'firstName'     => $firstName,
    'lastName'      => $lastName,
    'clientsEmail'  => $clientsEmail,
    'posId'         => $posId,
    'posAuthKey'    => $posAuthKey,
    'sessionId'     => $sessionId,
    'amount'        => $amount,
    'desc'          => $desc,
    'clientsIp'     => $_SERVER['REMOTE_ADDR']
));

if (is_file(_PS_THEME_DIR_.'modules/payu/redirect.tpl'))
    $smarty->display(_PS_THEME_DIR_.'modules/'.$payu->name.'/redirect.tpl');
else
    $smarty->display(_PS_MODULE_DIR_.$payu->name.'/redirect.tpl');




include_once(dirname(__FILE__).'/../../footer.php');
