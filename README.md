BitWasp
===

BitWasp is an open source PHP project which aims to lower the barrier of entry for anyone wishing to set up a bitcoin marketplace. Bitwasp is designed to operate independantly of centralized services, and runs it's own bitcoin server to track payments.

BitWasp is not production ready
===

This project is very much under development, and not yet ready for an alpha. Please be aware the project has not yet underdone extensive security testing and the code is liable to change. Please download and test the code by all means, but don't deploy it on a production system (yet).


Installation
===
Dependencies: 
```
Linux (commands in this reference will assume you're using Linux)
gnupg PHP extension
php-gd or imagemagick extension (for image conversion, metadata scrubbing, resizing)
Bitcoin binaries
SQL Database
```

Pull the project from our repository, and unzip in your document root.
You may need to alter permissions for temporary files, so execute the following:

```
chmod 777 ./assets/images -R
```

Create a database on your server, and import the schema.sql file. 

To use the PGP functions, you need PHP's gnupg extension.
You need PHP's GD library or the ImageMagick library to resize files, but if it can't it'll just default to the normal file (slow.....)

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

To enable currency conversion, set up a cronjob:
```
*/10 * * * * curl http://localhost_or_your_vhost/callback/rates
```

Finally, double check your .htaccess. The only setting you need to change is the second line, for RewriteBase. This is the folder BitWasp resides in on your server. Eg; server.com/bitwasp/ would have this set as RewriteBase /bitwasp/

Support BitWasp's Development
===
All money from donations go to fund BitWasp's development, testing, hosting, and bounties for bug's. 

Our Bitcoin Address: 19EkDTAaGWySZv1QsWxyWwYMZpo7jpvPYe

Anyone interested in contributing code or time to help with testing, please get in touch!


Details
===
- Automatic RSA encryption of private messages. Users messages are encrypted with password protected private keys, and require a user to enter a message PIN if this feature is enabled.
- PGP encryption - Users can set up PGP encryption of messages. Javascript encryption is enabled on message forms and the 'Send Address' form, and can be automatically encrypted with PGP on the server side if the vendor has this setting enabled.
- PGP Two Factor Authentication - To secure your account, we can encrypt a challenge token with your public key, which you can decrypt with your private key. Enter the token to complete your login. Simples.
- EXIF scrubbing of images - Heard about that hacker who was busted for posting a pic of his girlfriend with GPS meta-data? We did too. All images are scrubbed of meta-data, and converted to PNG's.
- Bitcoin Escrow system (Still in testing, re disputes) - Vendors can require a buyer to finalize early if they rating isn't great, but we have an escrow system in place. Final thing to wrap up is disputes resolution.
- Bitcoin wallet backup (yet to be added) - Storing all your coins in a live wallet is silly. So we're going to generate wallets as you go, and encrypt them with your PGP key, for storing until your ready to download. Top up the sites account as you need. (The functionality of this hasn't been finalized)
- Fee's System (minor, need to add) - Have yet to set up a panel for controlling fee's but this will be ready shortly.
- Optional: Live Exchange Rates - Get current exchange rates from CoinDesk or BitcoinAverage.com every 10 minutes to keep things simple. Vendors set their price in their local currency, and everything afterwards will use the live exchange rates.

