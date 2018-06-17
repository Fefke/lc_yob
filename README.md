	
	
	
	
	
	  ___   ___   ______________
	   \  \_/  / /  ___  \	 _  \
  	    \     / |  /   \  |	|_| |
         ____\___/__|_|_____|_|______/
        |____/__/___|_|_____|_|______\
            /  /    | |     | |  _    |
	   /  /	    |  \___/  | |_|   |
	  /__/	     \_______/_______/                                         
				  Your Own Bank
_______________________________________________________________________		

#    Prolog    #


**YOB** is an Add-on for the OpenSource Shopping System "litecart"
With this Add-on you are allowed to give your customers virtual
bank accounts and that with an high level of Security.


# Instructions #


1. Always backup data before making changes to your store.
    
2. Upload the contents of the folder public_html/ to the corresponding path of your installation.

3. Install the **YOB** Add-on.

4. Configure it howerver you need it.

5. Let your customers pay with it. Done!


# Modification Instructions #

**YOB** is an easy to use add-on.
No complicated Instructions are needed!
The Programm does it all by itself.



#         DATABASE          #


We create an Table called:
**yob_accounts**
On your Shop Database.
These is used for Validation and Account Data.
Users that dos not have the same Data in the **yob_accounts** and the lc_**customers**
were not allowed to pay with their Virtual Bank Account.
For More look at **Security**


#         Security          #

1.	**Security** is not only written bold with us.
	We don't want any user Data raw to see.
	About this we encrypt all of your user data in
	**base64** (Ammounts and Transactions)

2.	Furthermore are all processes double safed.
	The user get's the Info that he can not pay.
	If he goes on, we break the payment process and
	return and beautiful designed error message.
	The User can not go on and has the chance to go back.

	2.1	You can recive an Loopback E-Mail with the //Automatic Error E-mail// Function
		in the Add-on settings.
		But remember! The user is going to get informed about this, because of
		the data protection.
		
3.	An another Security part is the **Confirmation**
	During the Install of the **YOB** Add-on we bring in 
	an Order Status called **Confirmation**.
	So if you want it, an Support Employee is allowed to check
	the Order.

	
	
#          Images           #

Default Images were saved in:
"/images/payment/yob/"


#   Vqmod Modification's    #

We have to Modificate some LC Files for
the best User Experience.
These are the following:

1. "admin/modules.app/edit_module.inc.php"
	- Before Translation, add Update Button
	- Hidden Info for E-Mail input
	- INPUT yob admin JS
	- Usermanagement Segment (UMS)
	
2. "/includes/controllers/ctrl_module.inc.php"
	- Insert Updating availability for the UMS
	
For clarity we moved big code snippets to:
"includes/classes/yobfu.inc.php"
-> In there is an extra class for YOB with extra Methods and Functions

_______________________________________________________________________
( i ) We want to give our customers the absolute transparency of our Add-on

Version 1.0





-----------------------------------------------------------------------
We checked our product from top to bottom, but ...
( ! ) We assume no liability for problems and errors of any kind ( ! )
Even if this leads to the loss of many and important data.
-----------------------------------------------------------------------
