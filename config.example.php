<?php
$config = array(
	"db_location"	=> "/var/www/databases", //Location to store the database. Recommend that the db_location directory NOT be publicly accessible, place it where the webserver can edit it, but not were it can serve it.
	"db"			=> "miab_accounts", //Name of the database for user account and subdomain data
	"hostname"  	=> "box.example.com", //Mail-In-A-Box URI 
	"admin"			=> "admin@example.com", //Admin account username/email
	"pass"			=> "P@SSw0Rd123", //Admin account password
	"domain"    	=> "example.com", //FQDN of the domain users will be using for subdomains.
	"registration"	=>	false //Enable or disable registration.
	);
