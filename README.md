kennethware-2.0
===============

Tools to facilitate rapid course development in Instructure Canvas.

To learn about these tools and how to install them visit: https://usu.instructure.com/courses/305202

Composer
========
Install composer:
curl -sS https://getcomposer.org/installer | php

Run composer to get packages:
php composer.phar install

If you make changes to composer.json:
php composer.phar update

Environment Vars
================
The database type is the DSN prefix. Use mysql for mysql, sqlite for sqlite, pgsql for postgresql, etc.
Run the following command on a php page to get what is available on your system.
var_dump(PDO::getAvailableDrivers());

export DB_TYPE="put your db type here"
export DB_NAME="put your db name here"
export DB_HOST="put your db host here"
export DB_PORT="put your db port here"
export DB_USER="put your db user here"
export DB_PASS="put your db password here"
export CLIENT_ID="put your canvas id here"
export CLIENT_SECRET="put your canvas secret here"
export ENCRYPTION_KEY="put your encryption key here" #This should be the same as the encryption key used to encrypt the apiToken.
export ASSETS_SERVER="https://assets.lmstools.org"


Environment Vars with MAMP
================

Edit the Apache environment variables file here (Mac):

/Applications/MAMP/conf/apache/httpd.conf

Copy and paste the following into that file changing values to match your configuration

# Client id and secret obtained from Instructure. (Get these values from: https://docs.google.com/forms/d/1C5vOpWHAAl-cltj2944-NM0w16AiCvKQFJae3euwwM8/viewform)
SetEnv CLIENT_ID "17000000000xxxx"
SetEnv CLIENT_SECRET "it's a secret"

SetEnv ENCRYPTION_KEY "Generate an encryption key"

# Change this to the domain that will be serving global.js and global.css
SetEnv ASSETS_SERVER "assets.lmstools.org"

SetEnv DB_TYPE "pgsql"
SetEnv DB_NAME "unizin_manager_development"
SetEnv DB_HOST "localhost"
SetEnv DB_PORT ""
SetEnv DB_USER ""
SetEnv DB_PASS ""

Deploy
======
cap production deploy
