<?php

class payu extends PaymentModule {

    private $_html = '';
    
    public function __construct() {

        $this->name = 'payu';
        $this->tab = 'payments_gateways';
        $this->version = '1.0';

        $this->currencies = FALSE;
		$this->currencies_mode = 'radio';

        parent::__construct();

        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('PayU');
        $this->description = $this->l('Płatności PayU');

    }
    
    public function install() {
        if (
            !parent::install() OR
            !Configuration::updateValue('PAYU_POSID', '') OR
            !Configuration::updateValue('PAYU_POSAUTHKEY', '') OR
            !Configuration::updateValue('PAYU_AUTHKEY1', '') OR
            !Configuration::updateValue('PAYU_AUTHKEY2', '') OR
            !$this->registerHook('payment') OR
            !$this->registerHook('paymentReturn')
        ) {
            return FALSE;
        }
        
        /* Create payment status ( 1 - new payment ) */
        
        if (!Configuration::get('PAYU_STATUS_NEW')) {
            $orderState = new OrderState();
            $orderState->name[Language::getIdByIso("pl")] = "Nowa płatność";
            $orderState->name[Language::getIdByIso("en")] = "New payment";
            $orderState->send_email = FALSE;
            $orderState->invoice = FALSE;
            $orderState->unremovable = FALSE;

            if (!$orderState->add()) {
                return FALSE;
            }
            if (!Configuration::updateValue('PAYU_STATUS_NEW', $orderState->id)) {
                return FALSE;
            }
        }

        /* Create payment status ( 2 - cancelled ) */

        if (!Configuration::get('PAYU_STATUS_CANCELED')) {
            $orderState = new OrderState();
            $orderState->name[Language::getIdByIso("pl")] = "Płatność anulowana";
            $orderState->name[Language::getIdByIso("en")] = "Payment canceled";
            $orderState->send_email = FALSE;
            $orderState->invoice = FALSE;
            $orderState->unremovable = FALSE;

            if (!$orderState->add()) {
                return FALSE;
            }
            if (!Configuration::updateValue('PAYU_STATUS_CANCELED', $orderState->id)) {
                return FALSE;
            }
        }

        /* Create payment status ( 3,7 - declined ) */

        if (!Configuration::get('PAYU_STATUS_DECLINED')) {
            $orderState = new OrderState();
            $orderState->name[Language::getIdByIso("pl")] = "Płatność odrzucona";
            $orderState->name[Language::getIdByIso("en")] = "Payment declined";
            $orderState->send_email = FALSE;
            $orderState->invoice = FALSE;
            $orderState->unremovable = FALSE;

            if (!$orderState->add()) {
                return FALSE;
            }
            if (!Configuration::updateValue('PAYU_STATUS_DECLINED', $orderState->id)) {
                return FALSE;
            }
        }

        /* Create payment status ( 4 - started ) */

        if (!Configuration::get('PAYU_STATUS_STARTED')) {
            $orderState = new OrderState();
            $orderState->name[Language::getIdByIso("pl")] = "Płatność rozpoczęta";
            $orderState->name[Language::getIdByIso("en")] = "Payment started";
            $orderState->send_email = FALSE;
            $orderState->invoice = FALSE;
            $orderState->unremovable = FALSE;

            if (!$orderState->add()) {
                return FALSE;
            }
            if (!Configuration::updateValue('PAYU_STATUS_STARTED', $orderState->id)) {
                return FALSE;
            }
        }

        /* Create payment status ( 5 - pending ) */

        if (!Configuration::get('PAYU_STATUS_PENDING')) {
            $orderState = new OrderState();
            $orderState->name[Language::getIdByIso("pl")] = "Płatność oczekuje na odbiór";
            $orderState->name[Language::getIdByIso("en")] = "Payment pending";
            $orderState->send_email = FALSE;
            $orderState->invoice = FALSE;
            $orderState->unremovable = FALSE;

            if (!$orderState->add())
                return FALSE;
            if(!Configuration::updateValue('PAYU_STATUS_PENDING', $orderState->id))
                return FALSE;
        } 

        /* Create payment status ( 99 - closed ) */

        if (!Configuration::get('PAYU_STATUS_CLOSED')) {
            $orderState = new OrderState();
            $orderState->name[Language::getIdByIso("pl")] = "Płatność odebrana - zakończona";
            $orderState->name[Language::getIdByIso("en")] = "Payment successed - closed";
            $orderState->send_email = FALSE;
            $orderState->invoice = FALSE;
            $orderState->unremovable = FALSE;

            if (!$orderState->add()) {
                return FALSE;
            }
            if(!Configuration::updateValue('PAYU_STATUS_CLOSED', $orderState->id)) {
                return FALSE;
            }
        } 

        /* Create payment status ( 888 - error ) */

        if (!Configuration::get('PAYU_STATUS_ERROR')) {

            $orderState = new OrderState();
            $orderState->name[Language::getIdByIso("pl")] = "Błąd płatności";
            $orderState->name[Language::getIdByIso("en")] = "Payment error";
            $orderState->send_email = FALSE;
            $orderState->invoice = FALSE;
            $orderState->unremovable = FALSE;

            if (!$orderState->add()) {
                return FALSE;
            }
            if (!Configuration::updateValue('PAYU_STATUS_ERROR', $orderState->id)) {
                return FALSE;
            }
        }
       

        return TRUE;
    }
    
    public function uninstall() {
        if (
            !Configuration::deleteByName('PAYU_POSID') OR
            !Configuration::deleteByName('PAYU_POSAUTHKEY') OR
            !Configuration::deleteByName('PAYU_AUTHKEY1', '') OR
            !Configuration::deleteByName('PAYU_AUTHKEY2', '') OR
            !parent::uninstall()
        ) {
            return FALSE;
        }

        return TRUE;
    }
    
    public function displayConfirmation() {
        $this->_html .= '
        <div class="conf confirm">
                <img src="../img/admin/ok.gif" alt="' . $this->l('Potwierdzenie') . '" />
                ' . $this->l('Ustawienia zapisane') . '
        </div>';
    }
    
    public function getContent() {

        $this->_html = '<h2>PayU</h2>';

        if ( array_key_exists('sumbitPayUConf', $_POST) ) {
            if ( array_key_exists('PAYU_POSID', $_POST) ) {
                $posId = pSQL( $_POST['PAYU_POSID'] );
                Configuration::updateValue('PAYU_POSID', $posId);
            }

            if ( array_key_exists('PAYU_POSAUTHKEY', $_POST) ) {
                $pos = pSQL( $_POST['PAYU_POSAUTHKEY'] );
                Configuration::updateValue('PAYU_POSAUTHKEY', $pos);
            }
            
            if ( array_key_exists('PAYU_AUTHKEY1', $_POST) ) {
                $auth1 = pSQL( $_POST['PAYU_AUTHKEY1'] );
                Configuration::updateValue('PAYU_AUTHKEY1', $auth1);
            }
            
            if ( array_key_exists('PAYU_AUTHKEY2', $_POST) ) {
                $auth2 = pSQL( $_POST['PAYU_AUTHKEY2'] );
                Configuration::updateValue('PAYU_AUTHKEY2', $auth2);
            }

            $this->displayConfirmation();
        }

        // get all options

        $conf = Configuration::getMultiple(array(
            'PAYU_POSID', 'PAYU_POSAUTHKEY', 'PAYU_AUTHKEY1', 'PAYU_AUTHKEY2'
        ));

        $posId      = array_key_exists('PAYU_POSID', $conf) ? $conf['PAYU_POSID'] : '';
        $posAuthKey = array_key_exists('PAYU_POSAUTHKEY', $conf) ? $conf['PAYU_POSAUTHKEY'] : '';
        $authKey1   = array_key_exists('PAYU_AUTHKEY1', $conf) ? $conf['PAYU_AUTHKEY1'] : '';
        $authKey2   = array_key_exists('PAYU_AUTHKEY2', $conf) ? $conf['PAYU_AUTHKEY2'] : '';

        $this->_html .= '<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" style="clear: both">
       	    <p>
        	    <fieldset>
        	        <legend> <img src="' . _MODULE_DIR_ . $this->name . '/logo.gif" />' . $this->l('Settings') . '</legend>
        	        <label> ' . $this->l('Id punktu płatności (pos_id)') . '</label>
        	        <div class="margin-form">
        	            <input type="text" size="33" name="PAYU_POSID" value="' . htmlentities($posId, ENT_COMPAT, 'UTF-8') . '" />
        	        </div>
        	        <label> ' . $this->l('Klucz autoryzacji płatności (pos_auth_key)') . '</label>
        	        <div class="margin-form">
        	            <input type="text" size="33" name="PAYU_POSAUTHKEY" value="' . htmlentities($posAuthKey, ENT_COMPAT, 'UTF-8') . '" />
        	        </div>
        	        <label> ' . $this->l('Klucz (MD5)') . '</label>
        	        <div class="margin-form">
        	            <input type="text" size="33" name="PAYU_AUTHKEY1" value="' . htmlentities($authKey1, ENT_COMPAT, 'UTF-8') . '" />
        	        </div>
        	        <label> ' . $this->l('Drugi klucz (MD5)') . '</label>
        	        <div class="margin-form">
        	            <input type="text" size="33" name="PAYU_AUTHKEY2" value="' . htmlentities($authKey2, ENT_COMPAT, 'UTF-8') . '" />
        	        </div>
        	    </fieldset>
        	    <center><input type="submit" name="sumbitPayUConf" value="' . $this->l('Update settings') . '" class="button" /></center>
       	    </p>';

        return $this->_html;
    }

    function validateOrder($id_cart, $id_order_state, $amountPaid, $paymentMethod = 'Unknown', $message = NULL, $extraVars = array(), $currency_special = NULL, $dont_touch_amount = FALSE) {
    	if (!$this->active) {
    		return;
    	}
    	parent::validateOrder($id_cart, $id_order_state, $amountPaid, $paymentMethod, $message, $extraVars, $currency_special, $dont_touch_amount);
    }

    public function hookPayment($params) {
    	if (!$this->active) {
    		return;
    	}
        // wyświetlenie opcji płatności PayU
    	return $this->display(__FILE__, 'payu.tpl');
    }

    public function hookPaymentReturn($params) {
    	if (!$this->active) {
    		return;
    	}
        // wyświetlenie komunikatu o zakończeniu płatności
    	return $this->display(__FILE__, 'confirmation.tpl');
    }

}

