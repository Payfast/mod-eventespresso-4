<?php
if (!defined('EVENT_ESPRESSO_VERSION'))
    exit('No direct script access allowed');

/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package            Event Espresso
 * @ author             Seth Shoultes
 * @ copyright      (c) 2008-2011 Event Espresso  All Rights Reserved.
 * @ license            http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link                   http://www.eventespresso.com
 * @ version            4.0
 *
 * ------------------------------------------------------------------------
 *
 * Payment Gateway - PayFast
 *
 * Copyright (c) 2008 PayFast (Pty) Ltd
 * You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 *
 * @package         Event Espresso
 * @subpackage      gateways/
 * @author              Ron Darby - PayFast
 *
 * ------------------------------------------------------------------------
 */
Class EE_PayFast extends EE_Offsite_Gateway {

    private static $_instance = NULL;
    
    const SANDBOX_MERCHANT_KEY = '1pelravrwmo8e';
    const SANDBOX_MERCHANT_ID = '10000861';

    public static function instance(EEM_Gateways &$model) {
        // check if class object is instantiated
        if (self::$_instance === NULL or !is_object(self::$_instance) or ! ( self::$_instance instanceof  EE_PayFast )) {
            self::$_instance = new self($model);
            //echo '<h3>'. __CLASS__ .'->'.__FUNCTION__.'  ( line no: ' . __LINE__ . ' )</h3>';
        }
        return self::$_instance;
    }

    protected function __construct(EEM_Gateways &$model) {
        $this->_gateway_name = 'PayFast';
        $this->_button_base = 'payfast-logo.png';
        $this->_path = str_replace('\\', '/', __FILE__);
        
        parent::__construct($model);
        if(!$this->_payment_settings['use_sandbox']){
            $this->_gatewayUrl = 'https://www.PayFast.co.za/eng/process';
        }else{
            $this->_gatewayUrl = 'https://sandbox.PayFast.co.za/eng/process';
        }
    }

    protected function _default_settings() {
        $this->_payment_settings['PayFast_merchant_id'] = '';
        $this->_payment_settings['PayFast_merchant_key'] = '';
        $this->_payment_settings['currency_format'] = 'ZAR';
        $this->_payment_settings['use_sandbox'] = true;        
        $this->_payment_settings['use_debugging'] = true;
        $this->_payment_settings['type'] = 'off-site';
        $this->_payment_settings['display_name'] = __('PayFast','event_espresso');
        $this->_payment_settings['current_path'] = '';
        $this->_payment_settings['button_url'] = $this->_btn_img;
    }

    protected function _update_settings() {
        $this->_payment_settings['PayFast_merchant_key'] = $_POST['PayFast_merchant_key'];      
        $this->_payment_settings['PayFast_merchant_id'] = $_POST['PayFast_merchant_id'];
        $this->_payment_settings['use_sandbox'] = $_POST['use_sandbox'];     
        $this->_payment_settings['use_debugging'] = $_POST['use_debugging'];
        $this->_payment_settings['button_url'] = isset( $_POST['button_url'] ) ? esc_url_raw( $_POST['button_url'] ) : '';  
    }


    protected function _help_content() {
        ob_start();
        ?>      
       <div id="payfast_sandbox_info">
            <h2>
                <?php _e('PayFast Sandbox', 'event_espresso'); ?>
            </h2>
            <p>
                <?php _e('The PayFast Sandbox is a testing environment that is a duplicate of the live PayFast site, except that no real money changes hands. The Sandbox allows you to test your entire integration before submitting transactions to the live PayFast environment. Create and manage test accounts, and view emails and API credentials for those test accounts.', 'event_espresso'); ?>
            </p>
            <hr />
            <p><strong><?php _e('Helpful Links', 'event_espresso'); ?></strong></p>
            <ul>
                <li><a href="https://sandbox.payfast.co.za/" target="_blank"><?php _e('PayFast Sandbox Login', 'event_espresso'); ?></a></li>
                <li><a href="https://www.payfast.co.za/developers/sandbox_credentials" target="_blank"><?php _e('Sandbox Credentials', 'event_espresso'); ?></a></li>
            </ul>
        </div>
        <div id="payfast_debugging_info">
            <h2>
                <?php _e('PayFast Debugging', 'event_espresso'); ?>
            </h2>
            <p>
                <?php _e('When transaction information gets sent via the ITN process from the PayFast Payment Engine, the debugging mode allows you to record all PayFast variables and steps in order to fault find if you are experiencing difficulties completing a transaction.', 'event_espresso'); ?>
            </p>        
            
        </div>
        <div id="payfast_button_image">
            <h2>
                <?php _e('Button Image URL', 'event_espresso'); ?>
            </h2>
            <p>
                <?php _e('A default payment button is provided. A custom payment button may be used, choose your image or upload a new one, and just copy the "file url" here (optional.)', 'event_espresso'); ?>
            </p>
            <p><?php _e('Current Button Image', 'event_espresso'); ?></p>
            <p><?php echo '<img src="' . $this->_payment_settings['button_url'] . '" />'; ?></p>
        </div>
       
        <?php
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    protected function _display_settings() {
        ?>
        <tr>
            <th>
                <label><strong style="color:#F00"><?php _e('Please Note', 'event_espresso'); ?></strong></label>
            </th>
            <td>                
                <?php _e('You will need a PayFast Individual or Business account to receive payments using PayFast.', 'event_espresso'); ?>
            </td>
        </tr>

        <tr>
            <th><label for="PayFast_merchant_id">
                    <?php _e('PayFast Merchant ID', 'event_espresso'); ?>
                </label></th>
            <td><input class="regular-text" type="text" name="PayFast_merchant_id" size="35" id="PayFast_merchant_id" value="<?php echo $this->_payment_settings['PayFast_merchant_id']; ?>">
                <br />
                <span class="description">
                    <?php _e('The merchant id available from the \'Settings\' page within the logged in dashboard on PayFast.co.za', 'event_espresso'); ?>
                </span></td>
        </tr>
        <tr>
            <th><label for="PayFast_merchant_key">
                    <?php _e('PayFast Merchant Key', 'event_espresso'); ?>
                </label></th>
            <td><input class="regular-text" type="text" name="PayFast_merchant_key" size="35" id="PayFast_merchant_key" value="<?php echo $this->_payment_settings['PayFast_merchant_key']; ?>">
                <br />
                <span class="description">
                    <?php _e('The merchant key available from the \'Settings\' page within the logged in dashboard on PayFast.co.za', 'event_espresso'); ?>
                </span></td>
        </tr>       

        
        <tr>
            <th><label for="use_sandbox">
                    <?php _e('Use the PayFast Sandbox', 'event_espresso'); ?>
                    </label></th>
            <td><?php echo EEH_Form_Fields::select_input('use_sandbox', $this->_yes_no_options, $this->_payment_settings['use_sandbox']); ?></td>
        </tr>
        <tr>
            <th><label for="use_debugging">
                    <?php _e('Use the Debugging Feature', 'event_espresso'); ?>
                    </label></th>
            <td><?php echo EEH_Form_Fields::select_input('use_debugging', $this->_yes_no_options, $this->_payment_settings['use_debugging']); ?></td>
        </tr>
        
        <?php
    }

    protected function _display_settings_help() {
        ?>      
        <div id="payfast_sandbox_info" style="display:none">
            <h2>
                <?php _e('PayFast Sandbox', 'event_espresso'); ?>
            </h2>
            <p>
                <?php _e('The PayFast Sandbox is a testing environment that is a duplicate of the live PayFast site, except that no real money changes hands. The Sandbox allows you to test your entire integration before submitting transactions to the live PayFast environment. Create and manage test accounts, and view emails and API credentials for those test accounts.', 'event_espresso'); ?>
            </p>
            <hr />
            <p><strong><?php _e('Helpful Links', 'event_espresso'); ?></strong></p>
            <ul>
                <li><a href="https://sandbox.payfast.co.za/" target="_blank"><?php _e('PayFast Sandbox Login', 'event_espresso'); ?></a></li>
                <li><a href="https://www.payfast.co.za/developers/sandbox_credentials" target="_blank"><?php _e('Sandbox Credentials', 'event_espresso'); ?></a></li>
            </ul>
        </div>
        <div id="payfast_debugging_info" style="display:none">
            <h2>
                <?php _e('PayFast Debugging', 'event_espresso'); ?>
            </h2>
            <p>
                <?php _e('When transaction information gets sent via the ITN process from the PayFast Payment Engine, the debugging mode allows you to record all PayFast variables and steps in order to fault find if you are experiencing difficulties completing a transaction.', 'event_espresso'); ?>
            </p>        
            
        </div>
        <div id="payfast_button_image" style="display:none">
            <h2>
                <?php _e('Button Image URL', 'event_espresso'); ?>
            </h2>
            <p>
                <?php _e('A default payment button is provided. A custom payment button may be used, choose your image or upload a new one, and just copy the "file url" here (optional.)', 'event_espresso'); ?>
            </p>
            <p><?php _e('Current Button Image', 'event_espresso'); ?></p>
            <p><?php echo '<img src="' . $this->_payment_settings['button_url'] . '" />'; ?></p>
        </div>
       
        <?php
    }

    public function process_payment_start(EE_Line_Item $total_line_item, $transaction = null) {

        $PayFast_settings = $this->_payment_settings;
        $PayFast_sandbox = $PayFast_settings['use_sandbox'];

        $item_num = 1;
        
        /* @var $transaction EE_Transaction */
        if( ! $transaction){
            $transaction = $total_line_item->transaction();
        }

        //get any of the current registrations, 
        $primary_registrant = $transaction->primary_registration();
        $items = $total_line_item->get_items();
        
        $item_name = '';
        foreach( $items as $k=>$v )
        {
            $item_name .= $v->name().', ';
        }
        $item_name = substr( $item_name, 0, -2 );
        if( strlen( $item_name ) > 100 )
        {
            $item_name = substr( $item_name, 0, 96).'...';
        }            
        
        $pfSecureString = '';
        $pfArray = array(
            'merchant_id' => ( $PayFast_sandbox ? self::SANDBOX_MERCHANT_ID : $PayFast_settings['PayFast_merchant_id'] ),
            'merchant_key' => ( $PayFast_sandbox ? self::SANDBOX_MERCHANT_KEY : $PayFast_settings['PayFast_merchant_key'] ),
            'return_url' => $this->_get_return_url( $primary_registrant ),
            'cancel_url' => $this->_get_cancel_url(),
            'notify_url' => $this->_get_notify_url( $primary_registrant ),
            //'name_first' => $primary_registrant->fname(), 
            //'name_last' => $primary_registrant->lname(),
            //'email_address' => $primary_registrant->email(),
            'amount' => $transaction->remaining(),
            'item_name' => $item_name
            );

        
        foreach( $pfArray as $k=>$v )
        {
            $pfSecureString .="$k=".urlencode( trim( $v ) )."&";
            $this->addField( $k, $v );
        }
        $signature = md5( substr( $pfSecureString, 0, -1 ) );
        $this->addField( 'signature', $signature );
       
        
        do_action( 'AHEE_log', __FILE__, __FUNCTION__, serialize( get_object_vars( $this ) ) );
        $this->_EEM_Gateways->set_off_site_form( $this->submitPayment() );
        
        $this->redirect_after_reg_step_3( $transaction, $PayFast_settings['use_sandbox'] );
    }




    /**
     * Handles a PayFast ITN, verifies we haven't already processed this ITN, creates a payment (regardless of success or not)
     * and updates the provided transaction, and saves to DB
     * @param EE_Transaction or ID $transaction
     * @return boolean
     */
    public function handle_ipn_for_transaction(EE_Transaction $transaction){
        $this->_debug_log("<hr><br>".get_class($this).":start handle_ipn_for_transaction on transaction:".($transaction instanceof EE_Transaction)?$transaction->ID():'unknown');
        
        //verify there's payment data that's been sent
        if(empty($_POST['payment_status']) || empty($_POST['pf_payment_id'])){
            return false;
        }
        $this->_debug_log( "<hr><br>".get_class($this).": payment_status and pf_payment_id sent properly. payment_status:".$_POST['payment_status'].", pf_payment_id:".$_POST['pf_payment_id']);
        
        $PayFast_settings = $this->_payment_settings;
        $PayFast_sandbox = $PayFast_settings['use_sandbox']; 
         
        define( 'PF_DEBUG', $PayFast_settings['use_debugging'] );

        include( 'lib/payfast_common.inc' );
        pflog( 'PayFast ITN call received' );

        $pfError = false;
        $pfErrMsg = '';
        $pfDone = false;
        $pfData = array();      
        $pfParamString = '';      

        //// Notify PayFast that information has been received
        if( !$pfError && !$pfDone )
        {
            header( 'HTTP/1.0 200 OK' );
            flush();
        }

        //if the transaction's just an ID, swap it for a real EE_Transaction
        $transaction = $this->_TXN->ensure_is_obj( $transaction );
        //verify the transaction exists
        if(empty($transaction)){
            return false;
        }

        if( !$pfError && !$pfDone )
        {
            pflog( 'Get posted data' );

            // Posted variables from ITN
            $pfData = pfGetData();

            pflog( 'PayFast Data: '. print_r( $pfData, true ) );

            if( $pfData === false )
            {
                $pfError = true;
                $pfErrMsg = PF_ERR_BAD_ACCESS;
            }
        }

        //// Verify security signature
        if( !$pfError && !$pfDone )
        {
            pflog( 'Verify security signature' );

            // If signature different, log for debugging
            if( !pfValidSignature( $pfData, $pfParamString ) )
            {
                $pfError = true;
                $pfErrMsg = PF_ERR_INVALID_SIGNATURE;
            }
        }

        //// Verify source IP (If not in debug mode)
        if( !$pfError && !$pfDone )
        {
            pflog( 'Verify source IP' );

            if( !pfValidIP( $_SERVER['REMOTE_ADDR'] ) )
            {
                $pfError = true;
                $pfErrMsg = PF_ERR_BAD_SOURCE_IP;
            }
        }

        $pfHost = $PayFast_sandbox ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';

        //// Verify data received
        if( !$pfError )
        {
            pflog( 'Verify data received' );

            $pfValid = pfValidData( $pfHost, $pfParamString );

            if( !$pfValid )
            {
                $pfError = true;
                $pfErrMsg = PF_ERR_BAD_ACCESS;
            }
        }

         //// Check data against internal order
        if( !$pfError && !$pfDone )
        {
           pflog( 'Check data against internal order' );            
            
            // Check order amount
            if( !pfAmountsEqual( $pfData['amount_gross'], $transaction->remaining() ) )
            {
                $pfError = true;
                $pfErrMsg = PF_ERR_AMOUNT_MISMATCH;
            }          
            
        }
        //// Check status and update order
        if( !$pfError && !$pfDone )
        {
            pflog( 'Check status and update order' );

            
            $transaction_id = $pfData['pf_payment_id'];

            switch( $pfData['payment_status'] )
            {
                case 'COMPLETE':
                    pflog( '- Complete' );
                    pflog( 'PayFast transaction id: '.$pfData['pf_payment_id'] );
                    $status = EEM_Payment::status_id_approved;//approved
                    $gateway_response = __('Your payment is approved.', 'event_espresso');                   
                    break;

                case 'FAILED':
                    pflog( '- Failed' );
                    $status = EEM_Payment::status_id_failed;//declined
                    $gateway_response = __('Your payment has failed.', 'event_espresso');                   
                    break;

                case 'PENDING':
                    pflog( '- Pending' );
                    $status = EEM_Payment::status_id_pending;//approved
                    $gateway_response = __('Your payment is in progress. Another message will be sent when payment is approved.', 'event_espresso');
                    break;

                default:
                    // If unknown status, do nothing (safest course of action)
                break;
            }
        }
        else
        {
            //huh, something's wack... the ITN didn't validate. We must have replied to teh ITN incorrectly,
            EE_Error::add_error(__("PayFast ITN Validation failed!", "event_espresso"));
            return false;
        }
               
        $this->_debug_log( "<hr>Payment is interpreted as $status, and the gateway's response set to '$gateway_response'");
        //check if we've already processed this payment
        
        $payment = $this->_PAY->get_payment_by_txn_id_chq_nmbr( $pfData['pf_payment_id'] );
        if(!empty( $payment )){
            //payment exists. if this has the exact same status and amount, don't bother updating. just return
            if( $payment->STS_ID() == $status && $payment->amount() == $pfData['amount_gross'] ){
                //echo "duplicated ipn! dont bother updating transaction foo!";
                $this->_debug_log( "<hr>Duplicated ITN! ignore it...");
                return false;
            }else{
                $this->_debug_log( "<hr>Existing ITN for this PayFast transaction, but it\'s got some new info. Old status:".$payment->STS_ID().", old amount:".$payment->amount());
                $payment->set_status( $status );
                $payment->set_amount( $pfData['amount_gross'] );
                $payment->set_gateway_response( $gateway_response );
                $payment->set_details( $pfData );
            }
        }else{
            $this->_debug_log( "<hr>No Previous ITN payment received. Create a new one");
            //no previous payment exists, create one
            $primary_registrant = $transaction->primary_registration();
            $primary_registration_code = !empty( $primary_registrant ) ? $primary_registrant->reg_code() : '';
            
            $payment = EE_Payment::new_instance(array(
                'TXN_ID' => $transaction->ID(), 
                'STS_ID' => $status, 
                'PAY_timestamp' => $transaction->datetime(), 
                'PAY_method' => sanitize_text_field( 'PayFast Payment' ), 
                'PAY_amount' => floatval( $pfData['amount_gross'] ), 
                'PAY_gateway' => $this->_gateway_name, 
                'PAY_gateway_response' => $gateway_response, 
                'PAY_txn_id_chq_nmbr' => $pfData['pf_payment_id'], 
                'PAY_po_number' => NULL, 
                'PAY_extra_accntng'=>$primary_registration_code,
                'PAY_via_admin' => false, 
                'PAY_details' => $pfData
            ));
        
        }
        $payment->save();
        return $this->update_transaction_with_payment( $transaction, $payment );   
    }
    
    
    

    public function espresso_display_payment_gateways( $selected_gateway = '' ) {
        $this->_css_class = $selected_gateway == $this->_gateway_name ? '' : ' hidden';
        echo $this->_generate_payment_gateway_selection_button();
        ?>
        <div id="reg-page-billing-info-<?php echo $this->_gateway_name; ?>-dv" class="reg-page-billing-info-dv <?php echo $this->_css_class; ?>">
            <h3><?php _e('You have selected "PayFast" as your method of payment', 'event_espresso'); ?></h3>
            <p><?php _e('After finalizing your registration, you will be transferred to the PayFast.co.za website where your payment will be securely processed.', 'event_espresso'); ?></p>
        </div>
        <?php
    }
}

//end class
