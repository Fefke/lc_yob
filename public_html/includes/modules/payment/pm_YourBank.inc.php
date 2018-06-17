<?php

class pm_YourBank {
  public $id = __CLASS__;
  public $name = 'Your Bank';
  public $description = 'Build your own Bank accounts for customers, to pay with an virtual account.';
  public $author = 'SWK | Felix B.';
  public $version = '1.0';
  public $website = 'https://github.com/Fefke';
  public $priority = 1;   
  
  
  #########################################################################
  ########################### Front-End options ###########################
  #########################################################################
 
  public function options($items, $subtotal, $tax, $currency_code, $customer) {
	
	
	 if (empty($this->settings['status'])) return;
	
    $yob_method = array(
      'title' => $this->yob_load_settings("bank_name"),
      'options' => array(
        array(
          'id' => 'yob',
          'icon' => $this->settings['icon'],
          'name' => $this->yob_ammount(),//$this->yob_ammount()
          'description' => "",//language::translate(__CLASS__.':description_yob', 'Pay with your own virtual bank account'),
          'fields' => $this->test_for_money(),
          'cost' => 0,
          'tax_class_id' => 0,
          'confirm' => language::translate(__CLASS__.':title_confirm_order', 'Confirm Order'),
        ),
      )
    );
	return $yob_method;
  }
  
  
  #########################################################################
  ############################# YOB Functions #############################
  #########################################################################
	
  ################################ HEAD ###################################
public function yob_load_settings($keyword) {
	/*
	if ($keyword == "confirm") {
		$load_settings = database::query("
			SELECT `order_status_id` FROM " . DB_TABLE_ORDER_STATUSES_INFO . " WHERE name = 'Confirmation';
		");
		while ($row = database::fetch($load_settings)) {
			$loaded_settings = $row['order_status_id'];
		}
		echo $loaded_settings;
		return $loaded_settings;
	}
	
	*/
		$load_settings = database::query("
			SELECT settings FROM " . DB_TABLE_MODULES . " WHERE module_id = 'pm_YourBank';
		");
		
		if (mysqli_num_rows($load_settings) == 0) { //Check if request was successfull
			return 0;
		}
		
		while ($row = database::fetch($load_settings)) {
			$loaded_settings = $row['settings'];
		}

		$json_array = json_decode($loaded_settings);
		return $json_array->$keyword;
	
 }
 //yob_load_settings($keyword)
 
private function read_mail() {
	$store_mail_raw = database::query('
			SELECT `value` FROM ' . DB_TABLE_SETTINGS . ' WHERE `key` = "store_email"	
		');
	
	while ($row = database::fetch($store_mail_raw)) {
			$store_mail = $row['value'];
		}
	return "<p title='This is your Store E-Mail' style='margin: 0; padding: 0;'>" . $store_mail . "</p>";
}//read_mail()

  ############################## In Program ###############################
  
private function yob_current() {//NOT WORKING! Wenn Benutzer abgemeldet?!
	
	$cu_id = customer::$data['id']; //Kundennummer

		if (!empty($cu_id) || $cu_id > 0) {
			$current_ammount = database::query("
				SELECT * FROM yob_accounts WHERE PersonID = '$cu_id'
			");
			
			while ($yob = database::fetch($current_ammount)) {
					$yob_id_var = $yob['PersonID']; 
					$yob_ammount_var = base64_decode($yob['Ammount']);
					$yob_fname_var = $yob['FirstName'];
					$yob_lname_var = $yob['LastName'];
					$yob_email_var = $yob['Email'];
			}
			
			//Make sure that the user realy exist -> Exclude other cases	
			$current_customer = database::query('
				SELECT  *  FROM ' . DB_TABLE_CUSTOMERS . ' WHERE `id` = ' . $cu_id . ';
			');
			while ($lc = database::fetch($current_customer)) {
				$lc_id_var = $lc['id'];
				$lc_firstname_var = $lc['firstname'];
				$lc_lastname_var = $lc['lastname'];
				$lc_email_var = $lc['email'];
			}
			
			if (mysqli_num_rows($current_ammount) <= 0) {
				$yob_ammount_var = 0;
			}
			
			//Validate User Data
			//if ($yob_id_var == $lc_id_var && $yob_fname_var == $lc_firstname_var && $yob_lname_var == $lc_lastname_var && $yob_email_var == $lc_email_var) {
				//Decode Userdata
				$yob_ammount_var = currency::convert($yob_ammount_var, settings::get('store_currency_code'), currency::$selected['code']);
			//}
		} else {//If User is not same as in LC
			$yob_ammount_var = "EruAvwPgtstQTABP";
		}		
		
		//return the right value
		return $yob_ammount_var;
}  
 //yob_current()
 
private function yob_ammount() {
	
	//( i ) Alle Texte innerhalb dieser und untergeordneter Funktionen/ Methoden stehen im Checkout Fenster, des Add-On's
	
	$yob_ammount_name = language::translate(__CLASS__.':yob_ammount_name', 'Ammount');
	$cu_id = customer::$data['id']; //Kundennummer
	$yob_ammount_readable_var = 0;
	
	$cu_co_array = $this->yob_currency_code();
	$prefix = $cu_co_array["pr"];
	$suffix = $cu_co_array["su"];
	
	// Erhalten des Aktuellen Guthabens
	$yob_ammount_var = $this->yob_current();


	//Abfragen ob Kunde existiert
	if ($cu_id < 1) {
		$yob_text = language::translate(__CLASS__.':yob_un_useable', 'Please login to use');
		return "<p style='color:red; font-weight: bold;padding: 0; margin: 0;'>$yob_text</p>";
		
	} else if (!is_numeric($yob_ammount_var)) {
		//Ausgabe für den Fall eines Ghosts oder eines Fehlerhaften Ablaufes/Eintrags
			$yob_no_database_insert = "<p style='text-align: center; display: flex; align-items: center; justify-content: center;'>" . language::translate(__CLASS__.':yob_no_database_insert', "It seems like you don't have an Bank account");
			
			//Einstellungen abfragen: Wie Transfer unterbrochen wird, falls nötig -> Automailer
			if ($this->yob_load_settings('autocont') == '1') {
				$this->mail_if_error();
				$yob_admin_contact_info = "<p style='transform: translateY(-10px); text-align: center; display: flex; font-size: 10px; align-items: center; justify-content: center;'>" . language::translate(__CLASS__.':yob_admin_auto_contact_info', 'An administrator has already been informed about the problem.') . "</p>";
			} else {
				$yob_admin_contact_info = "<p style='transform: translateY(-15px); display: flex; align-items: center; justify-content: center;'>" . language::translate(__CLASS__.':yob_admin_contact_info', 'Please contact an admin!') . "</p>";
			}
			
			return "$yob_no_database_insert <br>
					$yob_admin_contact_info";
					
	} else {
		
		$yob_ammount_readable_var = number_format(
				$yob_ammount_var, 	// zu konvertierende zahl
				2,     				// Anzahl an Nochkommastellen
				",",  				 // Dezimaltrennzeichen
				"."   				 // 1000er-Trennzeichen
			);
		//echo var_dump(session::$data);
		//Letzte -> im besten Fall richtige und einzige Ausgabe
		return $yob_ammount_name . ": " . $prefix . $yob_ammount_readable_var . " " . $suffix;
			
		}
 }
 //yob_ammount()
 
private function yob_currency_code() {
	$cu_cu_co = currency::$selected['code']; //Gewählte Währung  CODE Format
	$cu_cu_su = currency::$selected['suffix']; //Gewählte Währung suffix Format
	$cu_cu_pr = currency::$selected['prefix']; //Gewählte Währung prefix Format
	
	
	if ($this->yob_load_settings('cc') == 1) {
		$ergebnis = array(
		"pr" => "",
		"su" => $cu_cu_co,
		);
	} else {
		$ergebnis = array(
		"pr" => $cu_cu_pr,
		"su" => $cu_cu_su,
		);
	}
	
	return $ergebnis;
	
 }
 //yob_currency_code()
 
private function test_for_money() {
	$yob_ammount_var = $this->yob_current();
	//Abfragen, ob der offene Betrag in der Speicherform "€" ist und falls nicht wird der richtige Betrag berechnet
	if (currency::$selected['value'] == 1) {
		$yob_total_due = cart::$total['value'] + cart::$total['tax'];
	} else {
		$yob_pre_total_due = cart::$total['value'] + cart::$total['tax'];
		$yob_total_due = $yob_pre_total_due * currency::$selected['value']; //Der Ausganswert (Im "€") Format wird mit dem aktuellen Wert der Zielwährung verrechnet.
		//$yob_total_due = round($yob_pre_total_due * currency::$selected['value'], 2); //Rounded Format -> Nicht zwanghaft nötig
	}
	
	/*
	Due = 11.90€
	currency::$selected['suffix'] = € (Danach)
	currency::$selected['prefix'] = $ (Davor) 
	currency::format(cart::$total['value'])
	cart::$total['value'] = 10
	cart::$total['tax'] = 1.9
	customer::$data['display_prices_including_tax'] = 1
	*/
	 
	if ( $yob_ammount_var == "EruAvwPgtstQTABP") {
		return ""/*"Failure! There is no account for testing money"*/;
	} else {
		$total_test = $yob_ammount_var - $yob_total_due;
	}

	if ($total_test < 0 && $this->yob_load_settings('overp') == 0 ) {
		$yob_recharge_info = language::translate(__CLASS__.':yob_recharge_info', 'Please recharge your account to pay with it.');
	return "
		<p style='font-size: 0.7em;color:cyan; padding: 5px; background-color: black; font-weight: bold; display: flex; align-items: center; justify-content: center;'> $yob_recharge_info </p>
	";
	} else {
		return 0;
	}

 }//test_for_money()
 
private function currency_calculator($to_calculate) {
	//calculate to the right currency => default Euro | trying to make it universal!
	$calculated_total = $to_calculate * currency::$selected['value'];
			
	return $calculated_total;
}//currency_calculator($to_calculate)
 
private function mail_if_error() {
//Kunden-/Daten
$cu_name = customer::$data['lastname'];
$cu_vname = customer::$data['firstname'];
$cu_id = customer::$data['id'];
$cu_status = customer::$data['status'];
$cu_coco = customer::$data['country_code'];
$cu_phone = customer::$data['phone'];
$cu_yob_ammount = $this->yob_current();

//Ammount umwandeln in lesbare Info
if ($cu_yob_ammount == "EruAvwPgtstQTABP") {
	$cu_yob_ammount = "Default value.";
	
}
$yob_solutions = "The user is not registered || is Ghosting || was logged out until the error report";
$cu_registered = customer::$data['date_created'];
$cu_lastupdate = customer::$data['date_updated'];

//E-Mail schreiben
$head = "

<title>YOB - Automailer</title>
	
	<style>
	
	h1 {
	height: 75px;
	width: 100%;
	text-align: center;
	color: white;
	background: #ff2222;
	}
	h3, p {
	margin: 0;
	padding: 0;
	}
	table {
    font-family: arial, sans-serif;
	border
    border-collapse: collapse;
    width: 100%;
	}

	td, th {
    border: 1px solid #dddddd;
    text-align: left;
    padding: 8px;
	}

	.even {
    background-color: #dddddd;
	}
	</style>

";
$body = '
	<h1>YOB error message</h1>
	<p>An customer has an problem with using the add-on.</p>
	 
	<h3>Customer DATA</h3>
	<table>
	  <tr>
		<td>Firstname</td>
		<td>' . $cu_vname .'</td>
	  </tr>
	  <tr class="even">
		<td>Lastname</td>
		<td>' . $cu_name . '</td>
	  </tr>
		<tr>
		<td>ID</td>
		<td>' . $cu_id . '</td>
	  </tr>
	   <tr class="even">
		<td>Status</td>
		<td>' . $cu_status . '</td>
	  </tr>
	   <tr>
		<td>Country Code</td>
		<td>' . $cu_coco . '</td>
	  </tr>
	   <tr class="even">
		<td>Phone</td>
		<td>' . $cu_phone . '</td>
	  </tr>
	  <tr>
		<td>YOB - Ammount</td>
		<td>' . $cu_yob_ammount . '</td>
	  </tr>
	   <tr class="even">
		<td>Registered since</td>
		<td>' . $cu_registered . '</td>
	  </tr>
	   <tr>
		<td>Last Account Update</td>
		<td>' . $cu_lastupdate . '</td>
	  </tr>
	</table>
	' . $yob_solutions . '
	<p>Try to update the "YOB" Add-on on the admin panel within <br>Modules -> Paymnt Modules -> Your Bank -> Update</p>

	
';

 //E-Mail schreiben und senden:
$mailtext = '
<html>

<head>' . $head .'</head> 
<body>' . $body . '</body>

</html>
';
 
$empfaenger = $this->read_mail(); //Mailadresse
$absender   = $this->read_mail();
$betreff    = "YOB Add-On | error message";
$antwortan  = "";
 
$header  = "MIME-Version: 1.0\r\n";
$header .= "Content-type: text/html; charset=utf-8\r\n";
 
$header .= "From: $absender\r\n";
$header .= "Reply-To: $antwortan\r\n";
// $header .= "Cc: $cc\r\n";  // falls an CC gesendet werden soll
$header .= "X-Mailer: PHP ". phpversion();
 
 
 
//Zum Schluss -> wird die Mail abgeschickt
mail( $empfaenger,
      $betreff,
      $mailtext,
      $header);
}//mail_if_error()
  
  
  #########################################################################
  ######################### Front-End controller ##########################
  #########################################################################
    
  public function pre_check($order) {

	if (customer::$data['id'] < 1) {
		$yob_text_unlogged = language::translate(__CLASS__.':yob_un_useable', 'Please login to use');
		$yob_admin_ext_contact_info = language::translate(__CLASS__.':yob_admin_ext_contact_info', 'Problems? contact an admin!');
		
		echo "
		<style>
			h3 {
				text-align: center;
				padding: 0; 
				margin: 15px 0 0;
			}
			
			h5 {
				text-align: center;
				padding: 0;
				margin: 0;
			}
		
		
			a {
				position: absolute;
				height: 20px;
				width: 75px;
				padding: 5px;
				margin: auto;
				text-decoration: none;
				border: 1px solid rgba(0,0,0,0);
				color: #ffffff;
				display: flex;
				align-items: center;
				justify-content: center;
				background-color: #000044;
				box-shadow: 0 10px 15px 0 #888888 ;
				font-weight: bold;
			}
		
			a:hover {
				color: #000044; 
				background: #eeeeee;
				border: 1px solid #000044;
			}
			
			div {
				width: 300px;
				margin:25px auto 0;
				padding: 15px;
				background-color: #efefef;
				font-family: Verdana;
				box-shadow: -3px 7px 15px #888888;
			}
			
			
			div p:first-of-type {
				font-size: 0.7em;
				color: #fefefe; 
				padding: 5px;
				background-color: red;
				font-weight: bold;
				display: flex; 
				align-items: center;
				justify-content: center;
			}
			
			div p:nth-of-type(2) {
				transform: translateY(-15px); 
				display: flex;
				align-items: center;
				justify-content: center;
			}
			
			#admin_info.visible {
				position: fixed; 
				bottom: 0; 
				left: 0; 
				font-family: Verdana; 
				font-size: 9px; 
				color: #cccccc;
			}
			
			#admin_info {
				display: none;
			}
			
		</style>
		
		
		
		<div>
			<h3>YOB</h3>
			<h5>Error!</h5>
			<p>$yob_text_unlogged</p>
			<p>$yob_admin_ext_contact_info</p>
			<a href='..' title='zurück'>BACK...</a>
		</div>";
		exit;
	  }
  }
  
  public function transfer($order) {
	$cu_id = customer::$data['id']; //Kundennummer
	$order_raw = $order->data['payment_due']; //Rechnugns/Offener Betrag
	
	//Convert the raw order total to right currency
	$order_readable = floatval(currency::convert($order_raw, currency::$selected['code']));
	
	//aktuellen Betrag einlesen und auf Fehler prüfen
	$yob_ammount_var = $this->yob_current();	
	
	//Erneute Ghost und unangemeldet abfrage -> zur Sicherheit
	if ($yob_ammount_var == "EruAvwPgtstQTABP") {
		//Texte
		$yob_no_database_insert = language::translate(__CLASS__.':yob_no_database_insert', 'It seems like you dont have an Bank account');
		//Einstellungen abfragen: Wie Transfer unterbrochen wird
		if ($this->yob_load_settings('autocont') == '1') {
			$this->mail_if_error();
			$yob_admin_contact_info = "<p style='transform: translateY(-10px); text-align: center; display: flex; font-size: 10px; align-items: center; justify-content: center;'>" . language::translate(__CLASS__.':yob_admin_auto_contact_info', 'An administrator has already been informed about the problem.') . "</p>";
			$admin_info_visibility = "visible" ;
		} else {
			$admin_info_visibility = "invisible";
			$yob_admin_contact_info = "<p style='transform: translateY(-15px); display: flex; align-items: center; justify-content: center;'>" . language::translate(__CLASS__.':yob_admin_contact_info', 'Please contact an admin!') . "</p>";
		}
		
		
			echo "
		<style>
			h3 {
				text-align: center;
				padding: 0; 
				margin: 15px 0 0;
			}
			
			h5 {
				text-align: center;
				padding: 0;
				margin: 0;
			}
		
		
			a {
				position: absolute;
				height: 20px;
				width: 75px;
				padding: 5px;
				margin: auto;
				text-decoration: none;
				border: 1px solid rgba(0,0,0,0);
				color: #ffffff;
				display: flex;
				align-items: center;
				justify-content: center;
				background-color: #000044;
				box-shadow: 0 10px 15px 0 #888888 ;
				font-weight: bold;
			}
		
			a:hover {
				color: #000044; 
				background: #eeeeee;
				border: 1px solid #000044;
			}
			
			div {
				width: 300px;
				margin:25px auto 0;
				padding: 15px;
				background-color: #efefef;
				font-family: Verdana;
				box-shadow: -3px 7px 15px #888888;
			}
			
			
			div p:first-of-type {
				font-size: 0.7em;
				color: #fefefe; 
				padding: 5px;
				background-color: red;
				font-weight: bold;
				display: flex; 
				align-items: center;
				justify-content: center;
			}
			
			div p:nth-of-type(2) {
				transform: translateY(-15px);
				margin-top: 15px;
				font-size: 12px;
				display: flex;
				align-items: center;
				justify-content: center;
			}
			
			#admin_info.visible {
				display: inline;
				position: fixed; 
				bottom: 0; 
				left: 0; 
				font-family: Verdana; 
				font-size: 9px; 
				color: #cccccc;
			}
			
			#admin_info {
				display: none;
			}
			
		</style>
		
		
		
		<div>
			<h3>" . $this->yob_load_settings('bank_name') . "</h3>
			<h5>Error!</h5>
			<p>&#x2192; $order_readable €</p>
			<p>$yob_no_database_insert</p>
			$yob_admin_contact_info
			<a href='..' title='zurück'>BACK...</a>
		</div>
		<p id='admin_info' class=" . $admin_info_visibility . " > *We will send some private Data for debugging, but we DEFINITLY delete them after this process!</p>";
			exit;			
	} else {
			//Safer vor der Totalen Ammount ausgabe
			if ($yob_ammount_var == "null" || $yob_ammount_var == "NULL" || $yob_ammount_var == "") {
				$yob_ammount_var = 0;
			}
	}
	
	//Einstellungen abfragen: Ob überzogen werden darf (negativ Werte möglich sein sollen)
	if ($this->yob_load_settings('overp') == 0) {
		if ($this->test_for_money()) {
			//Texte
			$yob_no_overdraw_info = language::translate(__CLASS__.':yob_no_overdraw_info', 'You can not overdraw your account!');
			$yob_open_bill = language::translate(__CLASS__.':yob_open_bill', 'Outstanding balance');
			$yob_recharge_info = language::translate(__CLASS__.':yob_recharge_info', 'Please recharge your account to pay with it.');
			
			echo "
			
			<style>
			h3 {
				text-align: center;
				padding: 0; 
				margin: 15px 0 0;
			}
			
			h5 {
				text-align: center;
				padding: 0;
				margin: 0;
			}
		
		
			a {
				position: absolute;
				height: 20px;
				width: 75px;
				padding: 5px;
				margin: auto;
				text-decoration: none;
				border: 1px solid rgba(0,0,0,0);
				color: #ffffff;
				display: flex;
				align-items: center;
				justify-content: center;
				background-color: #000044;
				box-shadow: 0 10px 15px 0 #888888 ;
				font-weight: bold;
			}
		
			a:hover {
				color: #000044; 
				background: #eeeeee;
				border: 1px solid #000044;
			}
			
			div {
				width: 300px;
				margin:25px auto 0;
				padding: 15px;
				background-color: #efefef;
				font-family: Verdana;
				box-shadow: -3px 7px 15px #888888;
			}
						
			#admin_info.visible {
				display: inline;
				position: fixed; 
				bottom: 0; 
				left: 0; 
				font-family: Verdana; 
				font-size: 9px; 
				color: #cccccc;
			}
			
			#admin_info {
				display: none;
			}
			
			#info {
				font-size: 0.7em;
				color: cyan; 
				padding: 5px;
				background-color: black;
				font-weight: bold;
				display: flex; 
				align-items: center; 
				justify-content: center;
			}
			
			#outstanding {
				font-size: 1em;
				transform: translate(-10px,15px);
				display: flex; 
				align-items: center;
				justify-content: center;
			}
			
		</style>
			
			
		<div >
			<h3>" . $this->yob_load_settings('bank_name') . "</h3>
			<h5>$yob_no_overdraw_info</h5>
			<p id='outstanding'> $yob_open_bill &#x2192; $order_raw €</p>
			<p id='info'> $yob_recharge_info </p>
			<a href='..' title='zurück'>BACK...</a>
		</div>";
		
		//Use WS_DIR_IMAGES for inclueding Images
			exit;
		}
	}
	
	
	
	################# Calculate ################
	//nach der Umformung wird der Summand der Gschicht umgeformt
	$yob_ammount_var = $yob_ammount_var / currency::$selected['value'];
	//Calculate the total
	$summe = $yob_ammount_var - $order_raw;

	//base64 encrypt
	$yob_ammount_var = base64_encode($yob_ammount_var);
	$order_readable = base64_encode($order_readable);
	$summe = base64_encode($summe);
	
	####### Write new Values in Database #######
	//Ammount in yob Tabelle aktualisieren
	$yob_update_last_ammount = database::query("
		UPDATE yob_accounts SET
			LastAmmount = '$yob_ammount_var'
			WHERE PersonID = '$cu_id' ;
	");	
	$yob_update_last_transfer = database::query("
		UPDATE yob_accounts SET
			LastTrans = '$order_readable'
			WHERE PersonID = '$cu_id';
	");	
	
	//Ammount in yob Tabelle aktualisieren
	$yob_update_ammount = database::query("
		UPDATE yob_accounts SET
			Ammount = '$summe'
			WHERE PersonID = '$cu_id'
	");	
  
  
}

  public function verify($order) {
	return array(
        'order_status_id' => $this->settings['order_status_id'],
        'payment_transaction_id' => '',
        'errors' => '',
      );
  }
  
  public function after_process($order) {
	$yob_create_users = database::query("
		UPDATE yob_accounts SET
			Email =
				(SELECT email FROM " . DB_TABLE_CUSTOMERS . "
					WHERE yob_accounts.PersonID = " . DB_TABLE_CUSTOMERS . ".id),
			FirstName = 
				(SELECT firstname FROM " . DB_TABLE_CUSTOMERS . "
					WHERE yob_accounts.PersonID = " . DB_TABLE_CUSTOMERS . ".id),
			LastName = 
				(SELECT lastname FROM " . DB_TABLE_CUSTOMERS . "
					WHERE yob_accounts.PersonID = " . DB_TABLE_CUSTOMERS . ".id)"
	);	
  }
  
  public function receipt($order) {
	  
  }

 
 
  #########################################################################
  ############################ ADMIN Settings #############################
  #########################################################################

  function settings() {
	
    return array(
      array(
        'key' => 'status',
        'default_value' => false,
        'title' => 'Status',
        'description' => language::translate(__CLASS__.':description_status', 'Enables or disables the module.'),
        'function' => language::translate(__CLASS__.':settings_function', 'toggle("e/ds")'),
      ),
      array(
          'key' => 'icon',
          'default_value' => WS_DIR_IMAGES . 'payment/yob/yob.png',
          'title' => language::translate(__CLASS__.':title_icon', 'Icon'),
          'description' => language::translate(__CLASS__.':description_icon', 'Web path of the icon to be displayed.'),
          'function' => 'input()',
        ),
      array(
        'key' => 'order_status_id',
        'default_value' => 6,
        'title' => language::translate('title_order_status', 'Order Status'),
        'description' => language::translate('modules:description_order_status', 'Give orders made with this payment method the following order status.'),
        'function' => 'order_status()',
      ),
      array(
		'key' => 'priority',
        'default_value' => '0',
        'title' => language::translate('title_priority', 'Priority'),
        'description' => language::translate('modules:description_priority', 'Process this module in the given priority order.'),
        'function' => 'int()',
      ),
	  array(
        'key' => 'bank_name',
        'default_value' => 'Your Own Bank',
        'title' => language::translate(__CLASS__.':yob_bank_name_title', 'Your Bank Name'),
        'description' => language::translate(__CLASS__.':yob_bank_name_description', 'Give your Bank an personal Name'),
        'function' => 'smallinput()',
      ),
	  array(
        'key' => 'bank_color',
        'default_value' => '#a2ddd9',
        'title' => language::translate(__CLASS__.':yob_bank_color_title', 'Choose an Bank color'),
        'description' => language::translate(__CLASS__.':yob_bank_color_description', 'Colorize your own Bank'),
        'function' => 'color()',
      ),
	  array(
        'key' => 'autocont',
        'default_value' => 'false',
        'title' => language::translate(__CLASS__.':auto_error_mail_title', 'Automatic Error E-Mail'),
        'description' => $this->read_mail(),//language::translate(__CLASS__.':auto_error_mail_description', 'Get an E-Mail if an user had a problem with this add-on'),
        'function' => 'toggle()',
      ),
	  array(
        'key' => 'cc',
        'default_value' => '0',
        'title' => language::translate(__CLASS__.':settings_currency_code_title', 'Currency Code'),
        'description' => language::translate(__CLASS__.':settings_currency_code_description', "Choose True for 'EUR' or False for '€' currency Code "),
        'function' => 'toggle()',
      ),
	  array(
        'key' => 'overp',
        'default_value' => 'false',
        'title' => language::translate(__CLASS__.':coating_control_title', 'Overdraw'),
        'description' => language::translate(__CLASS__.':coating_control_description', 'Users can overdraw their accounts'),
        'function' => 'toggle()',
      ),
    );
  }
    
	
  #########################################################################
  ######################### Installationsprozesse #########################
  #########################################################################

   public function install() {
   
	database::query(
		"CREATE TABLE IF NOT EXISTS yob_accounts (
			PersonID INT,
			Email TEXT,
			FirstName VARCHAR(255),
			LastName VARCHAR(255),
			Ammount TEXT,
			LastAmmount TEXT,
			LastTrans TEXT
		);"
	);
	
	//Erstellen neuer Benutzerkonten, falls noch nicht vorhanden => only id
	database::query("
		INSERT INTO `yob_accounts`(`PersonID`)
		SELECT id
		FROM " . DB_TABLE_CUSTOMERS . "
		WHERE id NOT IN (SELECT PersonID FROM yob_accounts)
		"
	);
	
	//Updaten aller Benutzer-Daten & eintragen der Daten zu passender id
	database::query("
			UPDATE yob_accounts SET
				Email =
					(SELECT email FROM " . DB_TABLE_CUSTOMERS . "
						WHERE yob_accounts.PersonID = " . DB_TABLE_CUSTOMERS . ".id),
				FirstName = 
					(SELECT firstname FROM " . DB_TABLE_CUSTOMERS . "
						WHERE yob_accounts.PersonID = " . DB_TABLE_CUSTOMERS . ".id),
				LastName = 
					(SELECT lastname FROM " . DB_TABLE_CUSTOMERS . "
						WHERE yob_accounts.PersonID = " . DB_TABLE_CUSTOMERS . ".id)
		"
	);	
	
	//Abfrage ob Bestellstatus-Info bereits besteht
	$yob_osi_1 = database::query('
			SELECT * FROM ' . DB_TABLE_ORDER_STATUSES_INFO . ' WHERE `name` = "Bestätigung";
	');
	
	$yob_osi_2 = database::query('
			SELECT * FROM ' . DB_TABLE_ORDER_STATUSES_INFO . ' WHERE `name` = "Confirmation";
	');
	
	$yob_os_1 = database::query('
			SELECT * FROM ' . DB_TABLE_ORDER_STATUSES . ' WHERE `color` = "#e80000" && `icon` = "fa-gavel" ;
	');
	
	if (mysqli_num_rows($yob_osi_1) <= 0 || mysqli_num_rows($yob_osi_2) <= 0 || mysqli_num_rows($yob_os_1) <= 0) {
		
		//Bestellstatus: bestätigen; Einfügen
			//Abfrage nach ID
			$yob_get_order_status_info_id = database::query("
				SELECT max(id) FROM " . DB_TABLE_ORDER_STATUSES_INFO . ";
				"
			);
			//Verarbeiten/suchen der ID
			while ($row = database::fetch($yob_get_order_status_info_id)) {
				$yob_order_status_end = (int) $row['max(id)'];
			}
			
			$yob_order_status_end1 = $yob_order_status_end + 1;
			$yob_order_status_end2 = $yob_order_status_end1 + 1;
			
			//Info anlegen	
				$yob_write_order_status_info_id = database::query("
					INSERT INTO " . DB_TABLE_ORDER_STATUSES_INFO . "(`id`) VALUE (" . $yob_order_status_end1 . ");
					"
				);
					//Updaten (de)
					$yob_create_users = database::query("
						UPDATE " . DB_TABLE_ORDER_STATUSES_INFO . " SET `order_status_id`=" . $yob_order_status_end1 . ",`language_code`='de',`name`='Bestätigung',`description`='Bestätigen Sie die Bestellung im YOB Controller',`email_subject`='Ihre Bestellung | Bezahl-ID: %payment_transaction_id',`email_message`='Sehr geehrter Herr %lastname,\r\n\r\nIhre Bestellung wird von einem berechtigten* bestätigt.\r\n\r\nSofort nach diesem Vorgang werden weitere Prozesse in Gang gesetzt.\r\n\r\n\r\nMit Freundlichen Grüßen\r\n\r\n%store_name\r\n\r\n\r\n\r\n*\'berechtigte\' sind Administratoren oder Moderatoren\r\n\r\nWeitere Informationen finden Sie unter %store_url' WHERE `id` = " . $yob_order_status_end1 . ";
							"
					);	
					
				$yob_write_order_status_info_id = database::query("
					INSERT INTO " . DB_TABLE_ORDER_STATUSES_INFO . "(`id`) VALUE (" . $yob_order_status_end2 . ");
					"
				);
					//Updaten (en)
					$yob_write_order_status_info_id = database::query("
					UPDATE " . DB_TABLE_ORDER_STATUSES_INFO . " SET `order_status_id`=" . $yob_order_status_end1 . ",`language_code`='en',`name`='Confirmation',`description`='Confirm the order in the YOB controler',`email_subject`='Your Order | Payment-ID: %payment_transaction_id',`email_message`='Dear Mr. %lastname,\r\n\r\nYour order will be confirmed by an authorized *.\r\n\r\nImmediately after this process, further processes are set in motion.\r\n\r\n\r\nBest regards\r\n\r\n%store_name\r\n\r\n\r\n\r\n* \'authorized\' are administrators or moderators\r\n\r\nFor more information, see %store_url' WHERE `id` = " . $yob_order_status_end2 . ";
							"
				);
			############### Icon ###############
				//Abfrage nach maximalwert
				$yob_get_order_status_info_id = database::query("
					SELECT max(id) FROM " . DB_TABLE_ORDER_STATUSES . ";
					"
				);
				//Verarbeiten/suchen des maximalwerts
				while ($row = database::fetch($yob_get_order_status_info_id)) {
					$icon_id = (int) $row['max(id)'];
				}
				
				$icon_id = $icon_id + 1;
				//Create the Insert
				$yob_write_status_icon = database::query("
					INSERT INTO " . DB_TABLE_ORDER_STATUSES . "(`id`, `icon`, `color`) VALUES (" . $icon_id . ",'fa-gavel', '#e80000')
				");
	}
		############### ENDE ###############
	
	}
  
   public function uninstall() {
	$yob_uninstall_info_text = language::translate(__CLASS__.':yob_uninstall_info_text', "You are going to uninstall the YOB Add-on!\nWe won't delete our Database\n -> If you change your mind.\n\n Please give us an little Feedback\nover the LC Forum or via E-Mail\nWe want to grow with our failures!");
	return $yob_uninstall_info_text;

}

}