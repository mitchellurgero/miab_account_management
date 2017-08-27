## Dynamic DNS Script for use with Mail-In-A-Box

This is a flat-file database style, PHP Script that allows your users to create subdomains on your MIAB server.

### Install instructions

1. copy ```config.example.php``` to ```config.php``` and change what you need in there. Make sure to create the directory for the db_location if needed.
2. open a browser and run install.php
3. Then login with ```admin``` and ```password```

Make sure to change your password when you login.

### Disable registration

To disable registration, just put a ```die();``` on line 32 of index.php.