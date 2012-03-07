<?php 

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
include(dirname(__FILE__).'/payu.php');



if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=order.php');


	
$payu      = new payu();
$cart       = new Cart(intval($cookie->id_cart));
$customer   = new Customer(intval($cart->id_customer));

$id_cart    = (int)$cart->id;
$order_state= Configuration::get('PAYU_STATUS_NEW');
$total      = floatval(Tools::ps_round(floatval($cart->getOrderTotal(true, 3)), 2));

$payu->validateOrder($id_cart, $order_state, $total, $payu->displayName, '');

$address = 'order-confirmation.php?key=' . $customer->secure_key . '&'.
               'id_cart=' . intval($cart->id) . '&' .
               'id_module=' . intval($payu->id) . '&slowvalidation';


			   
echo( $address );
		

		
Tools::redirect( $address );


