<?php

class yobfu {
	
	public function email_style() {
		return'
		
		<style>
					/*For the following*/
					
					.infobox {
						display: none;
						position: fixed;
						top: 0;
						color: white;
						padding: 15px;
						left: 50%;
						min-left: 250px!important;
						min-width: 120px!important;
						font-weight: bold;
					}	
					#good {
						background-color: rgba(0,180,0,0.9);
					}
					
					#bad {
						background-color: rgba(200,0,0,0.8);
					}
		</style>
		<div id="good" class="infobox">This E-Mail will work!</div>
		<div id="bad" class="infobox">Change your Adress to the same host!</div>
		<';
	}
	
	
	public function tbuttons($module_id) {
		
		if ($module_id == "pm_YourBank") {
				
			if (isset($_REQUEST['db_update'])) {
						//Erstellen neuer Benutzerkonten, falls noch nicht vorhanden => only id
						$yob_create_users = database::query("
							INSERT INTO `yob_accounts`(`PersonID`)
							SELECT id
							FROM " . DB_TABLE_CUSTOMERS . "
							WHERE id NOT IN (SELECT PersonID FROM yob_accounts)
							"
						);
						
						//Update Users Data
						$yob_update_users = database::query("
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
					}
					
			//Abfrage ob bereits installiert
			$yob_ask_exist = database::query('SELECT `id` FROM ' . DB_TABLE_MODULES . ' WHERE `module_id` = "pm_YourBank"');	
			$followingdata = $yob_ask_exist->fetch_array(MYSQLI_ASSOC);
					
			if ($followingdata != NULL) {
				
				 ?>
					<style>
						/*For the following*/
					
					#yob_tr {
						background: #f9f9f9;
						border-bottom: 2px solid black; /*same color: #f9f9f9*/
					}
			
					#yob_tr td a, #yob_tr td input {
						padding: 2px 7px;
						background: #007bd2;
						color: white;
						font-weight: bold;
						border-radius: 15px;
						text-decoration: none;
						border: none;
					}
					
					#yob_tr td a:hover, #yob_tr td input {
						background: white;
						background-color: #000b70;
					}
					</style>
					<tr id="yob_tr">
						<td>
							<?php echo language::translate('pm_yourbank:yob_update_db_info', 'Update your Database. (Does not harm)'); ?>
						</td>
						<td>
							<form action="" method="post">
								<input id="update" type="submit" value="<?php echo language::translate('pm_yourbank:yob_update', 'Update');?>" name="db_update" />
							<form>
						</td>
					</tr>
					
					
						<?php
						/*<!--
					//Code for upper "Control Page"!
					
					<tr id="yob_tr">
						<td>
							<label>' . language::translate('pm_yourbank:yob_link_to_controller_info', 'Control your users and their accounts.') . '</label>
						</td>
						<td>
							 <a href="yob/" target="_blank">' . language::translate('pm_yourbank:yob_link_to_controller', 'Control Page') . '</a>
						</td>
					</tr>
					
					-->*/
			}
					
		}
	}
	
	
	public function umtable($module) {
		//Sicherungseinheit
		$yob_abfrage = database::query("SELECT * FROM ".DB_TABLE_MODULES." WHERE module_id = 'pm_YourBank'");	
		
		while ($zeile = database::fetch($yob_abfrage)) { 
			$id = $zeile['module_id'];
		}
		
		//UMS Bereich
			if (isset($id) && $id == "pm_YourBank" && $module == "pm_YourBank") {

				//SQL Query
				$yob_accounts = database::query("SELECT * FROM `yob_accounts`");	
				
				//Variablen
				$id = 0;
				$everysecondline = 0;
				
				
				echo '<div style="min-height:350px;">';
				############### erstellen der Tabelle ###############
				echo '<br><br><h2 style="border-top: 2px solid black;padding-top:10px;">Usermanagement</h2>';
				echo '<table id="um" class="board">';
				
				################ Dynamischer Inhalt #################
				//Für Benutzergruppen spezifische Ausgabe
				//Für Admins
				
				//Farben holen
				$pm_class = new pm_YourBank();
				$bank_farbe = $pm_class->yob_load_settings('bank_color');
				
				$bankfarbeindicator = hexdec($bank_farbe);

				
				echo '<tr title="More Color in Future Versions!" style="background-color:' . $bank_farbe . '; color: ' . $this->color_tester($bank_farbe, "textcolor") . ';"><th>ID</th> <th>Name</th> <th>Ammount (in ' . currency::$selected["code"] . ')</th> <th>LastAmmount</th> <th>LastTrans</th></tr>';//Kopfzeile
				
				while ($zeile = database::fetch($yob_accounts)) {  
				
					$id = $id + 1;
					$everysecondline = $everysecondline + 1;
					
					if ($everysecondline == 2) {
					echo '<tr style="background-color: #f1f1f1; height:50px!important;">';
					$everysecondline = 0;
					} else {
					echo '<tr style="height:50px!important;">';
					}
					
					//Decrypt
					$ammount = (float) base64_decode($zeile['Ammount']);
					$lam = base64_decode($zeile['LastAmmount']);
					$last = base64_decode($zeile['LastTrans']);
					
					//RUNDEN
					$ammount = number_format($ammount, 2);
					
					
					echo '<td><input class="boardids" name="PersonID[]" type="hidden" value="' . $zeile['PersonID'] . '"/>' . $id .'</td>';
					echo '<td>' . $zeile['FirstName'] . " " . $zeile['LastName'] . "</td>";
					echo '<td><input class="pkt form-control" step="0.01" min="-999999" max="999999" name="Ammount[]" type="number" value="' . $ammount . '"/></td>';
					echo '<td>' . $lam . '</td>';
					echo '<td>' . $last . '</td>';
					echo '</tr>';

				}
				
				echo '</table>';//Tabelle Abgeschlossen
				echo '</div>';
				
				#####  Fußzeile #####
				echo '<p style="border-top: 2px solid black; padding-top:5px;margin-top: 50px;">- Users were Insertet after every Update of the Database throug the module.<br>- Please Remember we are stil in Beta Phase of Programming,<br>Bugs can occur and we would be pleased about a thread in the forum.<br><br> ( ! ) We assume no liability for problems and errors of any kind<br><br><br>Version 1.0<p>';
				
			}
	}
	
	
	public function save() {
		
		if (isset($_POST["PersonID"])) {
			$PeronID = $_POST["PersonID"];
			$ammount = $_POST["Ammount"];
			
			
			foreach ($PeronID as $id) {
			
			$rightindex = $id - 1;//For Arrays
			
			if($rightindex < 0) {
				$rightindex = 0;
			}
			
			//Encode Ammounts
			$summe = base64_encode($ammount[$rightindex]);
			
			
			database::query('
				UPDATE `yob_accounts` SET `Ammount`="' . $summe . '"  WHERE PersonID = ' . $PeronID[$rightindex] .';
			');
			
			}
			unset($_POST["PersonID"]);
			unset($_POST["Ammount"]);

		}
	}
	
	
	
	private function color_tester($hexval, $type = "textcolor") {//For right Color Values within Dynamic Colors
		$red = hexdec(substr($hexval, 1, 2));
		$green = hexdec(substr($hexval, 3, 2));
		$blue = hexdec(substr($hexval, 5, 2));
		
		switch($type) {
			case "textcolor":
				//Differenz der einzelnen Werte von 255 ermitteln
				$dif_white_red=255-$red;
				$dif_white_green=255-$green;
				$dif_white_blue=255-$blue;
				 
				$dif_white_sum=$dif_white_red+$dif_white_green+$dif_white_blue;
				
				if ($dif_white_sum > 200) { //Vermittlung zwischen Max 765 und min 0
					return "#fff"; 
				}
				
			break;
			case "tc_background":
				//Differenz zu beliebiger Hintergrundfarbe
				$bg_red = hexdec(substr($hexval, 1, 2));
				$bg_green = hexdec(substr($hexval, 3, 2));
				$bg_blue = hexdec(substr($hexval, 5, 2));
				 
				//durch abs wird sichergestellt, dass die Werte nicht negativ werden.
				$dif_bg_red=abs($bg_red-$red);
				$dif_bg_green=abs($bg_red-$green);
				$dif_bg_blue=abs($bg_red-$blue);
				 
				$dif_bg_sum=$dif_bg_red+$dif_bg_green+$dif_bg_blue;
			break;
			
		}
		 
	}
	
	
}//yob_functions
