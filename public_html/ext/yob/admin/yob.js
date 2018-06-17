//Deklare safety variable
	var safety = 0;	
//Functions


//For Automail

function mail_info(){
		
		if (validate() == true && safety == 0) {
			safety = 1;
			show_element('good', true);
			setTimeout(function(){
				show_element('good', false);
				$( ".EMail" ).animate({
					opacity: 0.25,
					height: "toggle"
				}, 600, function() {
						var displayed_mail = $( ".EMail" ).text();
						$( ".EMail" ).html("<a title='Go to your Control Panel \nto get Information about this\nYour Mail:\n  " + displayed_mail +"' style='background-color: #0033ff;color: white; padding: 1px 10px;'>Info</a>" ); //Need to renew the Link!
						
				});
				$( ".EMail" ).animate({
					opacity: 0.8,
					height: "toggle"
				}, 600, function() {});
			},3400);
			
		} else if (safety > 0 && safety != 0) {
			
		} else {
			document.getElementById('bad').style.display = 'inline';
			setTimeout(function(){
				show_element('bad', false);
				location.reload();
			},3400);
		}
}

function validate() {

var mail = $( ".EMail" ).text();

var host = window.location.hostname;
// trenne anhand von punkt
//mail = mail.split(".");
mail = mail.split("@");
host = host.split(".");
 
// letzten eintrag im array bestimmen
posm = mail.length - 1;
posh = host.length - 1;

// array eintrag als varibale
mail = mail[posm];
host = host[posh];

if (mail == host || mail == "Info") {
	return true;
} else {
	return false;
}

}

function show_element(id, showelement) {
	
	switch (showelement) {
		case true:
			document.getElementById( id ).style.display = 'inline';
		break;
		case false:
			document.getElementById( id ).style.display = 'none';
		break;
	}
}


//For Automail


//Programm start -> afte document ready in raw JS
document.addEventListener("DOMContentLoaded", function(event) { 

//Einmalige Änderungen
	var farbe = $("input[name='settings[bank_color]']").val();
	
	$( "input[name='settings[bank_color]']" ).css("background-color", farbe);


//Dynamische Änderungen
	//Automail
	$( "div:contains('@')" ).addClass("EMail");
	$( ".page" ).removeClass("EMail");//Entfernen einer Dummy Klasse
	var shop_mail = document.getElementsByClassName('.EMail').innerHTML;
	
	if (validate() == false) {
		$( ".EMail" ).css("color","red");
		$( ".EMail" ).children().attr('title', 'Unvalid! -> Has to be same host!\nChange it under:\nSettings/Store Email\n\n(<- In the Menu on the left)');
		$(this).find("input:radio[name='settings[autocont]'][value='1']").parent().css("display","none");
	} 
	
	$(this).find("input:radio[name='settings[autocont]'][value='1']").click(function() {
			setTimeout(mail_info, 100);
	});
	
	
	$( "input[name='settings[bank_color]']" ).change(function() {
		var farbe = $(this).val();
		$(this).css("background-color", farbe);
	});
});



