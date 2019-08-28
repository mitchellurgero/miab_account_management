## Account Management Script for use with Mail-In-A-Box

This is a flat-file database style, PHP Script that allows a user access to create accounts on your MIAB server.

### Install instructions

**Please be sure you install `php-curl` module for php before trying to use the script**

1. copy ```config.example.php``` to ```config.php``` and change what you need in there. Make sure to create the directory for the db_location if needed.
2. open a browser and run install.php
3. Then login with ```admin``` and ```password```

**Make sure to change your password when you login.**

### Disable registration

To disable, just change the appropriate configuration option in config.php.

### Special Notes

- The last user in a domain will not be allowed to be deleted. So if you only see one user in the list, but no delete button, this is NORMAL.
- Recommend that the db_location directory NOT be publicly accessible, place it where the webserver can edit it, but not were it can serve it.
