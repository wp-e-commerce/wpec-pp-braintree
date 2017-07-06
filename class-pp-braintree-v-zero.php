<?php
/**
 * Custom Exception for when an issue occurs with a Braintree transaction
 */
class BraintreeTransactionException extends Exception {
	// No need to implement anything here
}

class wpsc_merchant_braintree_v_zero extends wpsc_merchant {

	public function __construct( $purchase_id = null, $is_receiving = false ) {
		parent::__construct( $purchase_id, $is_receiving );
	}

	/**
	 * Gets the Braintree Auth disconnect URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function wpec_bt_auth_get_disconnect_url() {

		$url = add_query_arg( 'disconnect_paypal_braintree', 1, admin_url( esc_url_raw( 'options-general.php?page=wpsc-settings&tab=gateway' ) ) );

		return wp_nonce_url( $url, 'disconnect_paypal_braintree', 'wpec_paypal_braintree_admin_nonce' );
	}

	/**
	 * Gets the Braintree Auth connect URL.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function wpec_bt_auth_get_connect_url() {

		$connect_url = 'https://wpecommerce.org/wp-json/wpec/v1/braintree';

		$redirect_url = wp_nonce_url( admin_url( esc_url_raw( 'options-general.php?page=wpsc-settings&tab=gateway' ) ), 'connect_paypal_braintree', 'wpec_paypal_braintree_admin_nonce' );

		$current_user = wp_get_current_user();

		$environment = get_option( 'braintree_sandbox_mode' );
		$environment = $environment == 'on' ? 'sandbox' : 'production' ;

		// Note:  We doubly urlencode the redirect url to avoid Braintree's server
		// decoding it which would cause loss of query params on the final redirect
		$query_args = array(
			'Auth'              => 'WPeCBraintree',
			'user_email'        => $current_user->user_email,
			'business_currency' => wpsc_get_currency_code(),
			'business_website'  => get_bloginfo( 'url' ),
			'redirect'          => base64_encode( $redirect_url ),
		);

		if ( ! empty( $current_user->user_firstname ) ) {
			$query_args[ 'user_firstName' ] = $current_user->user_firstname;
		}

		if ( ! empty( $current_user->user_lastname ) ) {
			$query_args[ 'user_lastName' ] = $current_user->user_lastname;
		}

		// Let's go ahead and assume the user and business are in the same region and country,
		// because they probably are.  If not, they can edit these anyways
		$base_country = new WPSC_Country( wpsc_get_base_country() );
		$region = new WPSC_Region( get_option( 'base_country' ), get_option( 'base_region' ) );

		$location = in_array( $base_country->get_isocode(), array( 'US', 'UK', 'FR' ) ) ? $base_country->get_isocode() : 'US';

		if ( ! empty( wpsc_get_base_country() ) ) {
			$query_args['business_country'] = $query_args['user_country'] = wpsc_get_base_country();
		}

		if ( ! empty( $region->get_name() ) ) {
			$query_args['business_region'] = $query_args['user_region'] = $region->get_code();
		}

		if ( $site_name = get_bloginfo( 'name' ) ) {
			$query_args[ 'business_name' ] = $site_name;
		}

		if ( $site_description = get_bloginfo( 'description' ) ) {
			$query_args[ 'business_description' ] = $site_description;
		}

		return add_query_arg( $query_args, $connect_url );
	}

	public static function bt_auth_can_connect() {
		$base_country = new WPSC_Country( wpsc_get_base_country() );

		return in_array( $base_country->get_isocode(), array( 'US', 'UK', 'FR' ) );
	}

	public static function bt_auth_is_connected() {
		$token = get_option( 'wpec_braintree_auth_access_token' );

		return ! empty( $token );
	}

	/**
	 * Returns a list of merchant currencies
	 */
	public static function getMerchantCurrencies() {

		$merchant_currencies = array();

		// These are all the currencies supported by Braintree. Some have been commented out as trying to
		// load them all really slows down the display of the admin section for Braintree payments

		/*
		$merchant_currencies[] = array('currency'=>'AFN','currency_label'=>'Afghan Afghani');
		$merchant_currencies[] = array('currency'=>'ALL','currency_label'=>'Albanian Lek');
		$merchant_currencies[] = array('currency'=>'AMD','currency_label'=>'Armenian Dram');
		$merchant_currencies[] = array('currency'=>'ANG','currency_label'=>'Netherlands Antillean Gulden');
		$merchant_currencies[] = array('currency'=>'AOA','currency_label'=>'Angolan Kwanza');
		$merchant_currencies[] = array('currency'=>'ARS','currency_label'=>'Argentine Peso');
		*/
		$merchant_currencies[] = array('currency'=>'AUD','currency_label'=>'Australian Dollar');
		/*
		$merchant_currencies[] = array('currency'=>'AWG','currency_label'=>'Aruban Florin');
		$merchant_currencies[] = array('currency'=>'AZN','currency_label'=>'Azerbaijani Manat');
		$merchant_currencies[] = array('currency'=>'BAM','currency_label'=>'Bosnia and Herzegovina Convertible Mark');
		$merchant_currencies[] = array('currency'=>'BBD','currency_label'=>'Barbadian Dollar');
		$merchant_currencies[] = array('currency'=>'BDT','currency_label'=>'Bangladeshi Taka');
		$merchant_currencies[] = array('currency'=>'BGN','currency_label'=>'Bulgarian Lev');
		$merchant_currencies[] = array('currency'=>'BHD','currency_label'=>'Bahraini Dinar');
		$merchant_currencies[] = array('currency'=>'BIF','currency_label'=>'Burundian Franc');
		$merchant_currencies[] = array('currency'=>'BMD','currency_label'=>'Bermudian Dollar');
		$merchant_currencies[] = array('currency'=>'BND','currency_label'=>'Brunei Dollar');
		$merchant_currencies[] = array('currency'=>'BOB','currency_label'=>'Bolivian Boliviano');
		$merchant_currencies[] = array('currency'=>'BRL','currency_label'=>'Brazilian Real');
		$merchant_currencies[] = array('currency'=>'BSD','currency_label'=>'Bahamian Dollar');
		$merchant_currencies[] = array('currency'=>'BTN','currency_label'=>'Bhutanese Ngultrum');
		$merchant_currencies[] = array('currency'=>'BWP','currency_label'=>'Botswana Pula');
		$merchant_currencies[] = array('currency'=>'BYR','currency_label'=>'Belarusian Ruble');
		$merchant_currencies[] = array('currency'=>'BZD','currency_label'=>'Belize Dollar');
		*/
		$merchant_currencies[] = array('currency'=>'CAD','currency_label'=>'Canadian Dollar');
		//$merchant_currencies[] = array('currency'=>'CDF','currency_label'=>'Congolese Franc');
		$merchant_currencies[] = array('currency'=>'CHF','currency_label'=>'Swiss Franc');
		//$merchant_currencies[] = array('currency'=>'CLP','currency_label'=>'Chilean Peso');
		$merchant_currencies[] = array('currency'=>'CNY','currency_label'=>'Chinese Renminbi Yuan');
		/*
		$merchant_currencies[] = array('currency'=>'COP','currency_label'=>'Colombian Peso');
		$merchant_currencies[] = array('currency'=>'CRC','currency_label'=>'Costa Rican Col�n');
		$merchant_currencies[] = array('currency'=>'CUC','currency_label'=>'Cuban Convertible Peso');
		$merchant_currencies[] = array('currency'=>'CUP','currency_label'=>'Cuban Peso');
		$merchant_currencies[] = array('currency'=>'CVE','currency_label'=>'Cape Verdean Escudo');
		$merchant_currencies[] = array('currency'=>'CZK','currency_label'=>'Czech Koruna');
		$merchant_currencies[] = array('currency'=>'DJF','currency_label'=>'Djiboutian Franc');
		$merchant_currencies[] = array('currency'=>'DKK','currency_label'=>'Danish Krone');
		$merchant_currencies[] = array('currency'=>'DOP','currency_label'=>'Dominican Peso');
		$merchant_currencies[] = array('currency'=>'DZD','currency_label'=>'Algerian Dinar');
		$merchant_currencies[] = array('currency'=>'EEK','currency_label'=>'Estonian Kroon');
		$merchant_currencies[] = array('currency'=>'EGP','currency_label'=>'Egyptian Pound');
		$merchant_currencies[] = array('currency'=>'ERN','currency_label'=>'Eritrean Nakfa');
		$merchant_currencies[] = array('currency'=>'ETB','currency_label'=>'Ethiopian Birr');
		*/
		$merchant_currencies[] = array('currency'=>'EUR','currency_label'=>'Euro');
		//$merchant_currencies[] = array('currency'=>'FJD','currency_label'=>'Fijian Dollar');
		//$merchant_currencies[] = array('currency'=>'FKP','currency_label'=>'Falkland Pound');
		$merchant_currencies[] = array('currency'=>'GBP','currency_label'=>'British Pound');
		/*
		$merchant_currencies[] = array('currency'=>'GEL','currency_label'=>'Georgian Lari');
		$merchant_currencies[] = array('currency'=>'GHS','currency_label'=>'Ghanaian Cedi');
		$merchant_currencies[] = array('currency'=>'GIP','currency_label'=>'Gibraltar Pound');
		$merchant_currencies[] = array('currency'=>'GMD','currency_label'=>'Gambian Dalasi');
		$merchant_currencies[] = array('currency'=>'GNF','currency_label'=>'Guinean Franc');
		$merchant_currencies[] = array('currency'=>'GTQ','currency_label'=>'Guatemalan Quetzal');
		$merchant_currencies[] = array('currency'=>'GYD','currency_label'=>'Guyanese Dollar');
		*/
		$merchant_currencies[] = array('currency'=>'HKD','currency_label'=>'Hong Kong Dollar');
		/*
		$merchant_currencies[] = array('currency'=>'HNL','currency_label'=>'Honduran Lempira');
		$merchant_currencies[] = array('currency'=>'HRK','currency_label'=>'Croatian Kuna');
		$merchant_currencies[] = array('currency'=>'HTG','currency_label'=>'Haitian Gourde');
		$merchant_currencies[] = array('currency'=>'HUF','currency_label'=>'Hungarian Forint');
		$merchant_currencies[] = array('currency'=>'IDR','currency_label'=>'Indonesian Rupiah');
		$merchant_currencies[] = array('currency'=>'ILS','currency_label'=>'Israeli New Sheqel');
		$merchant_currencies[] = array('currency'=>'INR','currency_label'=>'Indian Rupee');
		$merchant_currencies[] = array('currency'=>'IQD','currency_label'=>'Iraqi Dinar');
		$merchant_currencies[] = array('currency'=>'IRR','currency_label'=>'Iranian Rial');
		$merchant_currencies[] = array('currency'=>'ISK','currency_label'=>'Icelandic Kr�na');
		$merchant_currencies[] = array('currency'=>'JMD','currency_label'=>'Jamaican Dollar');
		$merchant_currencies[] = array('currency'=>'JOD','currency_label'=>'Jordanian Dinar');
		*/
		$merchant_currencies[] = array('currency'=>'JPY','currency_label'=>'Japanese Yen');
		/*
		$merchant_currencies[] = array('currency'=>'KES','currency_label'=>'Kenyan Shilling');
		$merchant_currencies[] = array('currency'=>'KGS','currency_label'=>'Kyrgyzstani Som');
		$merchant_currencies[] = array('currency'=>'KHR','currency_label'=>'Cambodian Riel');
		$merchant_currencies[] = array('currency'=>'KMF','currency_label'=>'Comorian Franc');
		$merchant_currencies[] = array('currency'=>'KPW','currency_label'=>'North Korean Won');
		$merchant_currencies[] = array('currency'=>'KRW','currency_label'=>'South Korean Won');
		$merchant_currencies[] = array('currency'=>'KWD','currency_label'=>'Kuwaiti Dinar');
		$merchant_currencies[] = array('currency'=>'KYD','currency_label'=>'Cayman Islands Dollar');
		$merchant_currencies[] = array('currency'=>'KZT','currency_label'=>'Kazakhstani Tenge');
		$merchant_currencies[] = array('currency'=>'LAK','currency_label'=>'Lao Kip');
		$merchant_currencies[] = array('currency'=>'LBP','currency_label'=>'Lebanese Lira');
		$merchant_currencies[] = array('currency'=>'LKR','currency_label'=>'Sri Lankan Rupee');
		$merchant_currencies[] = array('currency'=>'LRD','currency_label'=>'Liberian Dollar');
		$merchant_currencies[] = array('currency'=>'LSL','currency_label'=>'Lesotho Loti');
		$merchant_currencies[] = array('currency'=>'LTL','currency_label'=>'Lithuanian Litas');
		$merchant_currencies[] = array('currency'=>'LVL','currency_label'=>'Latvian Lats');
		$merchant_currencies[] = array('currency'=>'LYD','currency_label'=>'Libyan Dinar');
		$merchant_currencies[] = array('currency'=>'MAD','currency_label'=>'Moroccan Dirham');
		$merchant_currencies[] = array('currency'=>'MDL','currency_label'=>'Moldovan Leu');
		$merchant_currencies[] = array('currency'=>'MGA','currency_label'=>'Malagasy Ariary');
		$merchant_currencies[] = array('currency'=>'MKD','currency_label'=>'Macedonian Denar');
		$merchant_currencies[] = array('currency'=>'MMK','currency_label'=>'Myanmar Kyat');
		$merchant_currencies[] = array('currency'=>'MNT','currency_label'=>'Mongolian T�gr�g');
		$merchant_currencies[] = array('currency'=>'MOP','currency_label'=>'Macanese Pataca');
		$merchant_currencies[] = array('currency'=>'MRO','currency_label'=>'Mauritanian Ouguiya');
		$merchant_currencies[] = array('currency'=>'MUR','currency_label'=>'Mauritian Rupee');
		$merchant_currencies[] = array('currency'=>'MVR','currency_label'=>'Maldivian Rufiyaa');
		$merchant_currencies[] = array('currency'=>'MWK','currency_label'=>'Malawian Kwacha');
		$merchant_currencies[] = array('currency'=>'MXN','currency_label'=>'Mexican Peso');
		$merchant_currencies[] = array('currency'=>'MYR','currency_label'=>'Malaysian Ringgit');
		$merchant_currencies[] = array('currency'=>'MZN','currency_label'=>'Mozambican Metical');
		$merchant_currencies[] = array('currency'=>'NAD','currency_label'=>'Namibian Dollar');
		$merchant_currencies[] = array('currency'=>'NGN','currency_label'=>'Nigerian Naira');
		$merchant_currencies[] = array('currency'=>'NIO','currency_label'=>'Nicaraguan C�rdoba');
		$merchant_currencies[] = array('currency'=>'NOK','currency_label'=>'Norwegian Krone');
		$merchant_currencies[] = array('currency'=>'NPR','currency_label'=>'Nepalese Rupee');
		*/
		$merchant_currencies[] = array('currency'=>'NZD','currency_label'=>'New Zealand Dollar');
		/*
		$merchant_currencies[] = array('currency'=>'OMR','currency_label'=>'Omani Rial');
		$merchant_currencies[] = array('currency'=>'PAB','currency_label'=>'Panamanian Balboa');
		$merchant_currencies[] = array('currency'=>'PEN','currency_label'=>'Peruvian Nuevo Sol');
		$merchant_currencies[] = array('currency'=>'PGK','currency_label'=>'Papua New Guinean Kina');
		$merchant_currencies[] = array('currency'=>'PHP','currency_label'=>'Philippine Peso');
		$merchant_currencies[] = array('currency'=>'PKR','currency_label'=>'Pakistani Rupee');
		$merchant_currencies[] = array('currency'=>'PLN','currency_label'=>'Polish Zloty');
		$merchant_currencies[] = array('currency'=>'PYG','currency_label'=>'Paraguayan Guaran�');
		$merchant_currencies[] = array('currency'=>'QAR','currency_label'=>'Qatari Riyal');
		$merchant_currencies[] = array('currency'=>'RON','currency_label'=>'Romanian Leu');
		$merchant_currencies[] = array('currency'=>'RSD','currency_label'=>'Serbian Dinar');
		$merchant_currencies[] = array('currency'=>'RUB','currency_label'=>'Russian Ruble');
		$merchant_currencies[] = array('currency'=>'RWF','currency_label'=>'Rwandan Franc');
		$merchant_currencies[] = array('currency'=>'SAR','currency_label'=>'Saudi Riyal');
		$merchant_currencies[] = array('currency'=>'SBD','currency_label'=>'Solomon Islands Dollar');
		$merchant_currencies[] = array('currency'=>'SCR','currency_label'=>'Seychellois Rupee');
		$merchant_currencies[] = array('currency'=>'SDG','currency_label'=>'Sudanese Pound');
		$merchant_currencies[] = array('currency'=>'SEK','currency_label'=>'Swedish Krona');
		$merchant_currencies[] = array('currency'=>'SGD','currency_label'=>'Singapore Dollar');
		$merchant_currencies[] = array('currency'=>'SHP','currency_label'=>'Saint Helenian Pound');
		$merchant_currencies[] = array('currency'=>'SKK','currency_label'=>'Slovak Koruna');
		$merchant_currencies[] = array('currency'=>'SLL','currency_label'=>'Sierra Leonean Leone');
		$merchant_currencies[] = array('currency'=>'SOS','currency_label'=>'Somali Shilling');
		$merchant_currencies[] = array('currency'=>'SRD','currency_label'=>'Surinamese Dollar');
		$merchant_currencies[] = array('currency'=>'STD','currency_label'=>'S�o Tom� and Pr�ncipe Dobra');
		$merchant_currencies[] = array('currency'=>'SVC','currency_label'=>'Salvadoran Col�n');
		$merchant_currencies[] = array('currency'=>'SYP','currency_label'=>'Syrian Pound');
		$merchant_currencies[] = array('currency'=>'SZL','currency_label'=>'Swazi Lilangeni');
		$merchant_currencies[] = array('currency'=>'THB','currency_label'=>'Thai Baht');
		$merchant_currencies[] = array('currency'=>'TJS','currency_label'=>'Tajikistani Somoni');
		$merchant_currencies[] = array('currency'=>'TMM','currency_label'=>'Turkmenistani Manat');
		$merchant_currencies[] = array('currency'=>'TMT','currency_label'=>'Turkmenistani Manat');
		$merchant_currencies[] = array('currency'=>'TND','currency_label'=>'Tunisian Dinar');
		$merchant_currencies[] = array('currency'=>'TOP','currency_label'=>'Tongan Pa?anga');
		$merchant_currencies[] = array('currency'=>'TRY','currency_label'=>'Turkish New Lira');
		$merchant_currencies[] = array('currency'=>'TTD','currency_label'=>'Trinidad and Tobago Dollar');
		$merchant_currencies[] = array('currency'=>'TWD','currency_label'=>'New Taiwan Dollar');
		$merchant_currencies[] = array('currency'=>'TZS','currency_label'=>'Tanzanian Shilling');
		$merchant_currencies[] = array('currency'=>'UAH','currency_label'=>'Ukrainian Hryvnia');
		$merchant_currencies[] = array('currency'=>'UGX','currency_label'=>'Ugandan Shilling');
		*/
		$merchant_currencies[] = array('currency'=>'USD','currency_label'=>'United States Dollar');
		/*
		$merchant_currencies[] = array('currency'=>'UYU','currency_label'=>'Uruguayan Peso');
		$merchant_currencies[] = array('currency'=>'UZS','currency_label'=>'Uzbekistani Som');
		$merchant_currencies[] = array('currency'=>'VEF','currency_label'=>'Venezuelan Bol�var');
		$merchant_currencies[] = array('currency'=>'VND','currency_label'=>'Vietnamese �?ng');
		$merchant_currencies[] = array('currency'=>'VUV','currency_label'=>'Vanuatu Vatu');
		$merchant_currencies[] = array('currency'=>'WST','currency_label'=>'Samoan Tala');
		$merchant_currencies[] = array('currency'=>'XAF','currency_label'=>'Central African Cfa Franc');
		$merchant_currencies[] = array('currency'=>'XCD','currency_label'=>'East Caribbean Dollar');
		$merchant_currencies[] = array('currency'=>'XOF','currency_label'=>'West African Cfa Franc');
		$merchant_currencies[] = array('currency'=>'XPF','currency_label'=>'Cfp Franc');
		$merchant_currencies[] = array('currency'=>'YER','currency_label'=>'Yemeni Rial');
		$merchant_currencies[] = array('currency'=>'ZAR','currency_label'=>'South African Rand');
		$merchant_currencies[] = array('currency'=>'ZMK','currency_label'=>'Zambian Kwacha');
		$merchant_currencies[] = array('currency'=>'ZWD','currency_label'=>'Zimbabwean Dollar');
		*/

		return $merchant_currencies;
	}

	/**
	 * Setup the Braintree configuration
	 */
	public static function setBraintreeConfiguration() {
		global $merchant_currency, $braintree_settings;

		// Get setting values
		$braintree_settings['sandbox_mode']     			= get_option( 'braintree_sandbox_mode' );
		$braintree_settings['sandbox_private_key'] 			= get_option( 'braintree_sandbox_private_key' );
		$braintree_settings['sandbox_public_key']  			= get_option( 'braintree_sandbox_public_key' );
		$braintree_settings['sandbox_merchant_id']			= get_option( 'braintree_sandbox_merchant_id' );
		$braintree_settings['sandbox_merchant_currency']	= get_option( 'braintree_sandbox_merchant_currency' );

		$braintree_settings['production_private_key'] 		= get_option( 'braintree_production_private_key' );
		$braintree_settings['production_public_key']  		= get_option( 'braintree_production_public_key' );
		$braintree_settings['production_merchant_id']		= get_option( 'braintree_production_merchant_id' );
		$braintree_settings['production_merchant_currency']	= get_option( 'braintree_production_merchant_currency' );

		$braintree_settings['settlement_type']     			= get_option( 'braintree_settlement_type' );
		$braintree_settings['threedee_secure']     			= get_option( 'braintree_threedee_secure' );
		$braintree_settings['threedee_secure_only']   		= get_option( 'braintree_threedee_secure_only' );

		// Retrieve the correct Braintree settings, depednign on whether
		// sandbox mode is turne on or off
		if ($braintree_settings['sandbox_mode'] == 'on' ) {

			Braintree_Configuration::environment( 'sandbox' );
			Braintree_Configuration::merchantId( $braintree_settings['sandbox_merchant_id'] );
			Braintree_Configuration::publicKey( $braintree_settings['sandbox_public_key'] );
			Braintree_Configuration::privateKey( $braintree_settings['sandbox_private_key'] );
			$merchant_currency = $braintree_settings['sandbox_merchant_currency'];

		} else {

			Braintree_Configuration::environment( 'production' );
			Braintree_Configuration::merchantId( $braintree_settings['production_merchant_id'] );
			Braintree_Configuration::publicKey( $braintree_settings['production_public_key'] );
			Braintree_Configuration::privateKey( $braintree_settings['production_private_key'] );
			$merchant_currency = $braintree_settings['production_merchant_currency'];

		}
	}

	/**
	 * Checks whether a Braintree transaction ID is valid
	 */
	public function checkBraintreeTransaction() {

		self::setBraintreeConfiguration();

		$transaction_id = $_POST['transaction_id'];

		if ( !empty( $transaction_id ) ) {
			$transaction = Braintree_Transaction::find( $transaction_id );
			return $transaction_id;
		} else {
			throw new BraintreeTransactionException;
		}
	}

	/**
	 * Retrieves a specific transaction
	 */
	public function retrieveBraintreeTransaction($transaction_id) {

		self::setBraintreeConfiguration();

		if ( !empty( $transaction_id ) ) {
			$transaction = Braintree_Transaction::find( $transaction_id );
			return $transaction;
		} else {
			throw new BraintreeTransactionException;
		}
	}

	/**
	 * Submits a transaction for refunding
	 */
	public function submitBraintreeRefund() {

		self::setBraintreeConfiguration();

		$transaction_id = $_POST['refund_payment'];

		try {
			$result = Braintree_Transaction::refund( $transaction_id );

			if ($result->success) {
				$_SESSION['refund_state'] = 'success';
				wpsc_update_purchase_log_details( $transaction_id, array( 'processed' => WPSC_Purchase_Log::REFUNDED ), 'transactid' );
			} else {
				$_SESSION['refund_state'] = 'failure';
				$_SESSION['braintree_errors'] = $result->message;
			}

			$_SESSION['braintree_transaction_id'] = $transaction_id;
		}
		catch (Braintree\Exception\Configuration $bec) {
			$output = '<p style="font-weight: bold; color: red; padding: 10px; text-align: center;">There is a problem with the Braintree payment gateway configuration</p>';

			$gateway_checkout_form_fields['wpsc_merchant_braintree_v_zero'] = $output;
		}
		catch (Exception $e) {
			// There is not a valid Braintree connection so display nothing.
			$output = '<p style="font-weight: bold; color: red; padding: 10px; text-align: center;">There is a problem with the Braintree payment gateway</p>';

			$gateway_checkout_form_fields['wpsc_merchant_braintree_v_zero'] = $output;
		}
	}

	/**
	 * Displays the transaction refund form
	 */
	public function displayBraintreeRefundForm() {

		$braintree_transaction = null;
		$braintree_errors = null;
		$braintree_refund_state = null;

		if ( !empty( $_SESSION['braintree_transaction_id'] ) ) {
			try {
				$braintree_transaction = retrieveBraintreeTransaction( $_SESSION['braintree_transaction_id'] );
			}
			catch (BraintreeTransactionException $bte) {
				$braintree_errors = 'You have not entered a Transaction ID';
			}

			unset( $_SESSION['braintree_transaction_id'] );
		}

		if ( !empty( $_SESSION['braintree_errors'] ) ) {
			$braintree_errors = $_SESSION['braintree_errors'];
			unset( $_SESSION['braintree_errors'] );
		}
	?>
		<h3><?php _e( 'Refund a Customer', 'wp-e-commerce' ); ?></h3>
		<p><?php _e( 'This page allows you to make refunds to customers that paid via the Braintree gateway. If the Transaction has been settled and has not yet been refunded you will be able to submit the Transaction for refunding.', 'wp-e-commerce' ); ?></p>

		<div>
	<?php
		if ( !empty( $_SESSION['refund_state'] ) ) {
			$braintree_refund_state = $_SESSION['refund_state'];
			unset( $_SESSION['$refund_state'] );

			if ($braintree_refund_state == 'success') {
	?>
			<p>
				Your Transaction has been refunded.
			</p>
	<?php
			}
		}

		if ($braintree_errors != null) {
			print '<p style="margin-top: 10px; padding: 10px; border: 1px solid red; font-weight: bold; background-color: darksalmon;">'.$braintree_errors.'</p>';
		}
	?>
			<p>
				Braintree Transaction ID: <input type="text" id="transaction_id" name="transaction_id" value="" /> <button type="submit" id="retrieve_transaction" name="retrieve_transaction" value="retrieve_transaction">Find Transaction</button>
			</p>
		</div>
	<?php
		if ($braintree_transaction != null) {
			//var_dump($braintree_transaction);
	?>
		<p>
			<b>Braintree Transaction ID:</b> <?php print $braintree_transaction->id; ?>
		</p>
	<?php
			if ($braintree_transaction->type == 'credit') {
	?>
		<p>
			<b>Transaction Type:</b> <span style="color: red;">Credit</span>
		</p>
	<?php
			} else {
	?>
		<p>
			<b>Transaction Type:</b> <span style="color: green;">Sale</span>
		</p>
	<?php
			}
	?>
		<p>
			<b>Transaction Status:</b> <?php print $braintree_transaction->status; ?>
		</p>
		<p>
			<b>Amount:</b> <?php print $braintree_transaction->currencyIsoCode; ?> <?php print $braintree_transaction->amount; ?>
		</p>
		<p>
			<b>Order ID:</b> <?php print $braintree_transaction->orderId; ?>
		</p>
		<p>
			<b>Customer:</b> <?php print $braintree_transaction->customerDetails->firstName; ?> <?php print $braintree_transaction->customerDetails->lastName; ?>
		</p>
		<p>
			<b>Email:</b> <?php print $braintree_transaction->customerDetails->email; ?>
		</p>
	<?php
			if ( $braintree_transaction->type == 'credit' ) {
				// This transaction is a refund so do not show refund button
			} elseif ( $braintree_transaction->refundId != null ) {
				// A refund has already been performed on his transaction so do not show refund button
			} else {
				if ($braintree_transaction->status == 'settled') {
	?>
		<button type="submit" id="refund_payment" name="refund_payment" value="<?php print $braintree_transaction->id; ?>">Refund Transaction</button>
	<?php
				}
			}
		}
	}

	public static function pp_braintree_enqueue_js() {
		global $merchant_currency, $braintree_settings;
			$braintree_threedee_secure = get_option( 'braintree_threedee_secure' );
			$braintree_threedee_secure_only = get_option( 'braintree_threedee_secure_only' );
			// Check if we are using Auth and connected
			if ( self::bt_auth_can_connect() && self::bt_auth_is_connected() ) {
				$acc_token = get_option( 'wpec_braintree_auth_access_token' );
				$gateway = new Braintree_Gateway( array(
					'accessToken' => $acc_token
				));
				$clientToken = $gateway->clientToken()->generate();
			} else {
				self::setBraintreeConfiguration();
				$clientToken = Braintree_ClientToken::generate();
			}
			
			$pp_sandbox = get_option( 'braintree_pp_sandbox_mode', 'on' );
			$sandbox = $pp_sandbox == 'on' ? true : false ;
			// Set PP Button styles
			$pp_but_label = get_option( 'bt_vzero_pp_payments_but_label' ) != false ? get_option( 'bt_vzero_pp_payments_but_label' ) : 'checkout' ;
			$pp_but_colour = get_option( 'bt_vzero_pp_payments_but_colour' ) != false ? get_option( 'bt_vzero_pp_payments_but_colour' ) : 'gold' ;
			$pp_but_size = get_option( 'bt_vzero_pp_payments_but_size' ) != false ? get_option( 'bt_vzero_pp_payments_but_size' ) : 'small' ;
			$pp_but_shape = get_option( 'bt_vzero_pp_payments_but_shape' ) != false ? get_option( 'bt_vzero_pp_payments_but_shape' ) : 'pill' ;
			if ( $braintree_threedee_secure == 'on' ) {
				echo '
				<style>
				#pp-btree-hosted-fields-modal {
				  position: absolute;
				  top: 0;
				  left: 0;
				  display: flex;
				  align-items: center;
				  height: 100vh;
				  z-index: 100;
				}
				.pp-btree-hosted-fields-modal-hidden {
					display: none !important;
				}
				.pp-btree-hosted-fields-bt-modal-frame {
				  height: 480px;
				  width: 440px;
				  margin: auto;
				  background-color: #eee;
				  z-index: 2;
				  border-radius: 6px;
				}
				.pp-btree-hosted-fields-bt-modal-body {
				  height: 400px;
				  margin: 0 20px;
				  background-color: white;
				  border: 1px solid lightgray;
				}
				.pp-btree-hosted-fields-bt-modal-header, .pp-btree-hosted-fields-bt-modal-footer {
				  height: 40px;
				  text-align: center;
				  line-height: 40px;
				}
				.pp-btree-hosted-fields-bt-mask {
				  position: fixed;
				  top: 0;
				  left: 0;
				  height: 100%;
				  width: 100%;
				  background-color: black;
				  opacity: 0.8;
				}
				</style>';
			}
			?>
			<script src="https://js.braintreegateway.com/web/3.16.0/js/client.min.js"></script>
			<script src="https://js.braintreegateway.com/web/3.16.0/js/hosted-fields.min.js"></script>
			<script src="https://js.braintreegateway.com/web/3.16.0/js/paypal-checkout.min.js"></script>
			<script src="https://www.paypalobjects.com/api/checkout.js" data-version-4></script>
			<script src="https://js.braintreegateway.com/web/3.16.0/js/three-d-secure.min.js"></script>

			<script type='text/javascript'>
			var clientToken = "<?php echo $clientToken; ?>";
			var components = {
			  client: null,
			  threeDSecure: null,
			  hostedFields: null,
			  paypalCheckout: null,
			};
			var my3DSContainer;
			var modal = document.getElementById('pp-btree-hosted-fields-modal');
			var bankFrame = document.querySelector('.pp-btree-hosted-fields-bt-modal-body');
			var closeFrame = document.getElementById('pp-btree-hosted-fields-text-close');
			var form = document.querySelector('.wpsc_checkout_forms');
			var submit = document.querySelector('.make_purchase.wpsc_buy_button');
			var paypalButton = document.querySelector('#pp_braintree_pp_button');
			
			function create3DSecure( clientInstance ) {
				// DO 3DS
				<?php if ( $braintree_threedee_secure == 'on' ) { ?>
					braintree.threeDSecure.create({
						client:  clientInstance
					}, function (threeDSecureErr, threeDSecureInstance) {
						if (threeDSecureErr) {
						  // Handle error in 3D Secure component creation
						  console.error('error in 3D Secure component creation');
						  return;
						}
						components.threeDSecure = threeDSecureInstance;
					});
				<?php } ?>
			}
			function addFrame(err, iframe) {
				// Set up your UI and add the iframe.
				bankFrame.appendChild(iframe);
				modal.classList.remove('pp-btree-hosted-fields-modal-hidden');
				modal.focus();
			}
			function removeFrame() {
				var iframe = bankFrame.querySelector('iframe');
				modal.classList.add('pp-btree-hosted-fields-modal-hidden');
				iframe.parentNode.removeChild(iframe);
				submit.removeAttribute('disabled');
			}
			function createHostedFields( clientInstance ) {
				braintree.hostedFields.create({
					client: clientInstance,
					styles: {
					  'input': {
						'font-size': '14px',
						'font-family': 'monospace',
						'font-weight': 'lighter',
						'color': '#ccc'
					  },
					  ':focus': {
						'color': 'black'
					  },
					  '.valid': {
						'color': '#8bdda8'
					  }
					},
					fields: {
					  number: {
						selector: '#bt-cc-card-number',
						placeholder: '4111 1111 1111 1111'
					  },
					  cvv: {
						selector: '#bt-cc-card-cvv',
						placeholder: '123'
					  },
					  expirationDate: {
						selector: '#bt-cc-card-exp',
						placeholder: 'MM/YYYY'
					  },
					}
				}, function (hostedFieldsErr, hostedFieldsInstance) {
					if (hostedFieldsErr) {
						console.error(hostedFieldsErr.code);
						alert(hostedFieldsErr.code);
						return;
					}
					components.hostedFields = hostedFieldsInstance;
					submit.removeAttribute('disabled');
					form.addEventListener('submit', function (event) {
						if ( jQuery('input[name=custom_gateway]:checked').val() !== 'wpsc_merchant_braintree_v_zero_cc' ) { return; }
						event.preventDefault();
						components.hostedFields.tokenize(function (tokenizeErr, payload) {
							if (tokenizeErr) {
								console.error(tokenizeErr.message);
								alert(tokenizeErr.message);
								return;
							}
							if ( components.threeDSecure ) {
								components.threeDSecure.verifyCard({
									amount: <?php echo wpsc_cart_total(false); ?>,
									nonce: payload.nonce,
									addFrame: addFrame,
									removeFrame: removeFrame
									}, function (err, response) {
										// Handle response
										if (!err) {
											var liabilityShifted = response.liabilityShifted; // true || false
											var liabilityShiftPossible =  response.liabilityShiftPossible; // true || false
											if (liabilityShifted) {
												// The 3D Secure payment was successful so proceed with this nonce
												document.getElementById('pp_btree_method_nonce').value = response.nonce;
												jQuery(".wpsc_checkout_forms").submit();
											} else {
												// The 3D Secure payment failed an initial check so check whether liability shift is possible
												if (liabilityShiftPossible) {
													// LiabilityShift is possible so proceed with this nonce
													document.getElementById('pp_btree_method_nonce').value = response.nonce;
													jQuery(".wpsc_checkout_forms").submit();
												} else {
													<?php if ( $braintree_threedee_secure_only == 'on' ) { ?>
														// Check whether the 3D Secure check has to be passed to proceeed. If so then show an error
													  console.error('There was a problem with your payment verification');
													  alert('There was a problem with your payment verification');
													  return;
													  <?php
													} else { ?>
														// ...and if not just proceed with this nonce
														document.getElementById('pp_btree_method_nonce').value = response.nonce;
														jQuery(".wpsc_checkout_forms").submit();
													<?php } ?>
												}
											}
											// 3D Secure finished. Using response.nonce you may proceed with the transaction with the associated server side parameters below.
											document.getElementById('pp_btree_method_nonce').value = response.nonce;
											jQuery(".wpsc_checkout_forms").submit();
										} else {
											// Handle errors
											console.log('verification error:', err);
											return;
										}
									});
								} else {
									// send the nonce to your server.
									document.getElementById('pp_btree_method_nonce').value = payload.nonce;
									jQuery(".wpsc_checkout_forms").submit();
								}
						});
					}, false);
				});
			};
			function createPayPalCheckout( clientInstance ) {
				  braintree.paypalCheckout.create({
					client: clientInstance
				  }, function (paypalErr, paypalCheckoutInstance) {
					if (paypalErr) {
					  console.error('Error creating PayPal:', paypalErr);
					  alert(paypalErr.code);
					  return;
					}
					components.paypalCheckout = paypalCheckoutInstance;
					paypalButton.removeAttribute('disabled');
					// Set up PayPal with the checkout.js library
					paypal.Button.render({
						env: '<?php if ( $sandbox ) { echo 'sandbox'; } else { echo 'production'; } ?>',
						style: {
							label: '<?php echo $pp_but_label; ?>',
							size:  '<?php echo $pp_but_size; ?>',
							shape: '<?php echo $pp_but_shape; ?>',
							color: '<?php echo $pp_but_colour; ?>'
						},
					  payment: function () {
						return components.paypalCheckout.createPayment({
						  flow: 'checkout', // Required
						  intent: 'sale',
						  amount: <?php echo wpsc_cart_total(false); ?>, // Required
						  currency: '<?php echo wpsc_get_currency_code(); ?>', // Required
						  locale: 'en_US',
						  useraction: 'commit',
						  enableShippingAddress: false,
						  <?php
						  if ( wpsc_uses_shipping() ) {
						  ?>
						  enableShippingAddress: true,
						  shippingAddressEditable: false,
						  shippingAddressOverride: {
							recipientName: jQuery( 'input[title="billingfirstname"]' ).val() + jQuery( 'input[title="billinglastname"]' ).val(),
							line1: jQuery( 'textarea[title="billingaddress"]' ).text(),
							city: jQuery( 'input[title="billingcity"]' ).val(),
							countryCode: 'US',
							postalCode: jQuery( 'input[title="billingpostcode"]' ).val(),
							state: replace_state_code( jQuery( 'input[title="billingstate"]' ).val() ),
						  }
						  <?php
						  }
						  ?>
						});
					  },
					  onAuthorize: function (data, actions) {
						return components.paypalCheckout.tokenizePayment(data)
						  .then(function (payload) {
							// Submit `payload.nonce` to your server
							paypalButton.setAttribute('disabled', true);
							document.getElementById('pp_btree_method_nonce').value = payload.nonce;
							jQuery(".wpsc_checkout_forms").submit();
						  });
					  },
					  onCancel: function (data) {
						console.log('checkout.js payment cancelled', JSON.stringify(data, 0, 2));
					  },
					  onError: function (err) {
						console.error('checkout.js error', err);
					  }
					}, paypalButton).then(function () {
					  // The PayPal button will be rendered in an html element with the id
					  // `paypal-button`. This function will be called when the PayPal button
					  // is set up and ready to be used.
					});
				  });
			};

			function replace_state_code( state ) {
					var states = {
						'Alabama':'AL',
						'Alaska':'AK',
						'Arizona':'AZ',
						'Arkansas':'AR',
						'California':'CA',
						'Colorado':'CO',
						'Connecticut':'CT',
						'Delaware':'DE',
						'Florida':'FL',
						'Georgia':'GA',
						'Hawaii':'HI',
						'Idaho':'ID',
						'Illinois':'IL',
						'Indiana':'IN',
						'Iowa':'IA',
						'Kansas':'KS',
						'Kentucky':'KY',
						'Louisiana':'LA',
						'Maine':'ME',
						'Maryland':'MD',
						'Massachusetts':'MA',
						'Michigan':'MI',
						'Minnesota':'MN',
						'Mississippi':'MS',
						'Missouri':'MO',
						'Montana':'MT',
						'Nebraska':'NE',
						'Nevada':'NV',
						'New Hampshire':'NH',
						'New Jersey':'NJ',
						'New Mexico':'NM',
						'New York':'NY',
						'North Carolina':'NC',
						'North Dakota':'ND',
						'Ohio':'OH',
						'Oklahoma':'OK',
						'Oregon':'OR',
						'Pennsylvania':'PA',
						'Rhode Island':'RI',
						'South Carolina':'SC',
						'South Dakota':'SD',
						'Tennessee':'TN',
						'Texas':'TX',
						'Utah':'UT',
						'Vermont':'VT',
						'Virginia':'VA',
						'Washington':'WA',
						'West Virginia':'WV',
						'Wisconsin':'WI',
						'Wyoming':'WY'
					};
					return states[state];
				}
			function wpscCheckSubmitStatus( e ) {
				var pp_button = jQuery(".make_purchase.wpsc_buy_button");
				if ( jQuery('input[name=custom_gateway]:checked').val() == 'wpsc_merchant_braintree_v_zero_pp' ) {
					if ( e && e.keyCode == 13 ) {
						e.preventDefault();
						return;
					}
					if ( pp_button.is(":visible") ) {
						pp_button.hide();
						return;
					}		
				}
				pp_button.show();
			}
			function wpscBootstrapBraintree() {
				//Disable the regular purchase button if using PayPal
				wpscCheckSubmitStatus();
				if ( jQuery('input[name=custom_gateway]:checked').val() !== 'wpsc_merchant_braintree_v_zero_cc' && jQuery('input[name=custom_gateway]:checked').val() !== 'wpsc_merchant_braintree_v_zero_pp' ) {
					return;
				}
				if ( components.client ) {
					return;
				}
				braintree.client.create({
				  authorization: clientToken
				}, function(err, clientInstance) {
				  if (err) {
					console.error(err);
					return;
				  }
				  components.client = clientInstance;
				  <?php
				  if ( (bool) get_option( 'bt_vzero_cc_payments' ) == true ) { ?>
					  create3DSecure( clientInstance );
					  createHostedFields(clientInstance);
				  <?php }
				  if ( (bool) get_option( 'bt_vzero_pp_payments' ) == true ) { ?>
					createPayPalCheckout(clientInstance );
				  <?php } ?>
				});
				if ( components.threeDSecure ) {
					closeFrame.addEventListener('click', function () {
					  components.threeDSecure.cancelVerifyCard(removeFrame());
					});
				}
			}

			jQuery( document ).ready( wpscBootstrapBraintree );
			jQuery( document ).on( 'keypress', '.wpsc_checkout_forms', wpscCheckSubmitStatus );
			jQuery( 'input[name=\"custom_gateway\"]' ).change( wpscBootstrapBraintree );
			</script>
		<?php
	}
}