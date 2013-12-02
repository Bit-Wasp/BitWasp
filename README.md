BitWasp
===

```
Project Homepage: http://bit-wasp.org
Test site: http://demo.bit-wasp.org
Facebook page: https://facebook.com/BitWasp
```

BitWasp is an open source PHP project which aims to lower the barrier of entry for anyone wishing to set up a bitcoin marketplace. Bitwasp is designed to operate independantly of centralized services, and runs it's own bitcoin server to track payments.

BitWasp is not production ready
===

This project is very much under development, and not yet ready for an alpha. Please be aware the project has not yet underdone extensive security testing and the code is liable to change. Please download and test the code by all means, but don't deploy it on a production system (yet).


Installation
===
Dependencies: 
```
Linux (commands in this reference will assume you're using Linux)
curl
gnupg PHP extension
php-gd or imagemagick extension (for image conversion, metadata scrubbing, resizing)
php-curl
Bitcoin binaries
SQL Database
```

The following is a brief account of setting up BitWasp. For detailed 
instructions, please check out http://bitwasp.tk/index.php/topic,28.0.html

Run the following to import the database schema:
```
mysql -u root -p
CREATE DATABASE bitwasp;
CREATE USER 'bitwasp'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON bitwasp.* to 'bitwasp'@'localhost';
```

You should keep up to date with the latest source code. Pull the project from our Github repository, and unzip in your document root.
You may need to alter permissions for temporary files, so execute the following:

```
chmod 777 ./assets/images -R
```

To use the PGP functions, you need PHP's gnupg extension.
You need PHP's GD library or the ImageMagick library to resize files, but if it can't it'll just default to the normal file (slow.....)
Eventually you will need the GMP extension also..

To run the bitcoin server, you'll need a bitcoin.conf:
```
rpcuser=bitcoinrpc_something_here
rpcpassword=asldhflashdljfasdhfahsdjfalskd
testnet=1
server=1
rpcport=28332
rpcconnect=127.0.0.1
```

Download the bitcoin binaries, unzip them, and cd into the directory for your chipset; ./bitcoin*/bin/32 or ./bitcoin*/bin/64
Execute the following command to run your bitcoin daemon.
```
./bitcoind -daemon -blocknotify="curl http://localhost_or_your_vhost/callback/block/%s" -walletnotify="curl http://localhost_or_your_vhost/callback/wallet/%s"
```

There is no installer, so you need to set up the config files yourself:
./application/config/database.php :
	- This needs your SQL details. See ./application/config/database.php.sample
./application/config/bitwasp.php :
	- This needs your bitcoind JSON-rpc credentials as entered above. See ./application/config/bitcoin.php.sample

Finally, double check your .htaccess. The only setting you need to change is the second line, for RewriteBase. This is the folder BitWasp resides in on your server. Eg; server.com/bitwasp/ would have this set as RewriteBase /bitwasp/
You really should add a GPG key to the administrators account, as when backing up excess funds, the wallet information will be encrypted.

Support BitWasp's Development
===
All money from donations go to fund BitWasp's development, testing, hosting, and bounties for bug's. 

Our Bitcoin Address: 19EkDTAaGWySZv1QsWxyWwYMZpo7jpvPYe

Anyone interested in contributing code or time to help with testing, please get in touch!

Features list: http://bitwasp.tk/index.php/topic,4.msg4.html#msg4
