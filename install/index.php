<?php
error_reporting(E_ALL); //Setting this to E_ALL showed that that cause of not redirecting were few blank lines added in some php files.

// Determine filesystem path to the necessary files.
$basedir = explode("/", dirname(__FILE__));
$installdir = implode("/", array_splice($basedir, 0, count($basedir)-1));

$database_config_file = $installdir.'/application/config/database.php';
$bitcoin_config_file = $installdir.'/application/config/bitcoin.php';
$config_config_file = $installdir.'/application/config/config.php';
$storage_directory = $installdir.'/application/storage/';

// Die if there appears to be contents in the config files.
if(strlen(@file_get_contents($config_config_file)) > 20 && strlen(@file_get_contents($bitcoin_config_file)) > 20 && strlen(@file_get_contents($database_config_file)) > 20 && $_SERVER['QUERY_STRING'] !== 'end')
	die();

// Modules which must be installed.
$check['mcrypt_module'] 	= ( ! function_exists('mcrypt_encrypt')) ? FALSE : TRUE;
$check['gmp_module']		= ( ! function_exists('gmp_init')) ? FALSE : TRUE;
$check['curl_module']		= ( ! function_exists('curl_init')) ? FALSE : TRUE;
$check['openssl_module'] 	= ( ! function_exists('openssl_random_pseudo_bytes')) ? FALSE : TRUE;
$check['gd_module'] 		= ( ! (extension_loaded('gd') && function_exists('gd_info'))) ? FALSE : TRUE;
$check['gpg_module'] 		= ( class_exists('gnupg') && function_exists('gnupg_init')) ? TRUE : FALSE;
// Files which need to be writable
$check['storage_directory_writable'] 	= ( ! is_writable($storage_directory)) ? FALSE : TRUE;
$check['database_config_file_writable'] = ( ! is_writable($database_config_file)) ? FALSE : TRUE;
$check['bitcoin_config_file_writable'] 	= ( ! is_writable($bitcoin_config_file)) ? FALSE : TRUE;
$check['config_config_file_writable'] 	= ( ! is_writable($config_config_file)) ? FALSE : TRUE;

// Work out if the environment is ready for the installer.
$environment_check = TRUE; 
foreach($check as $key => $outcome) {
	$environment_check &= $outcome;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

		<title>Install | BitWasp</title>

		<style type="text/css">
		  body {
		    font-size: 75%;
		    font-family: Helvetica,Arial,sans-serif;
		    width: 500px;
		    margin: 0 auto;
		  }
		  input, label {
		    font-size: 18px;
		    margin: 0;
		    padding: 10px;
		    border-radius:10px;
		  }
		  label {
		    margin-top: 20px;
		  }
		  input.input_text {
		    width: 270px;
		  }
		  input#submit {
		    margin: 25px auto 0;
		    font-size: 25px;
		  }
		  fieldset {
		    padding: 15px;
		    border-radius:10px;
		  }
		  legend {
		    font-size: 18px;
		    font-weight: bold;
		  }
		  .error {
		    background: #ffd1d1;
		    border: 1px solid #ff5858;
        padding: 4px;
		  }
		</style>
	</head>
	<body>

    <center><h1>Install</h1></center>
    <?php 
    // Do sanity checks on the environment first. 
    if($environment_check == FALSE) { ?>
    
		<form>
			<fieldset>
<?php 	$commands = array();
		
		foreach($check as $key => $outcome) { 
			$errors = array();
			if($outcome == FALSE) {
				// Work out which error to display, and what command will fix it.
				$a = explode("_", $key);
				$last = $a[count($a)-1];
				if($last == 'writable') {
					$type = $a[count($a)-2];
					$name = implode(" ", array_slice($a, 0, count($a)-2));
					echo "<p class=\"error\">You must make the $name $type writable!</p>";
					
					$ref = implode("_", array_slice($a, 0, count($a)-1));
					if($type == 'directory') {
						$cmd = 'chmod 777 '.$$ref;
						echo '$ '.$cmd;
						$commands[] = $cmd;
					}
					if($type == 'file') {
						$cmd = 'touch '.$$ref.' &#38;&#38; chmod 777 '.$$ref;
						echo '$ '.$cmd;
						$commands[] = $cmd;
					}
				} 
			}	
		} ?><br /><br />
		
		Press F5 once you're done.<br /><br />
		<?php if(count($commands) > 1) { ?>
				Copy/paste this box into your terminal to run all of these at once:<br />
				<textarea cols='80'><?php foreach($commands as $cmd) { echo $cmd."\n";	} ?></textarea>
		<?php } ?>
			</fieldset>
		</form>
    
    <?php } else { 
		
		// Environment passes sanity check.
		
		// Complete installation?
		if(isset($_GET['end'])) { ?>
			Your installation is now complete, but there are a number of things to do before you go live. <br /><br />
			
			<ul>
				<li>You can bookmark this page until you are finished. When you are done delete the /install directory.</li><br />
				
				<li>Edit your bitcoin.conf and crontab entry and remove the # symbols.</li><br />
				
				<li>Configure tidy URLs?<br />
				Create a .htaccess file, and ensure your server has AllowOverride set to All.</li><br />
				
				<li>Set up categories for items<br />Admin Panel -> Items tab -> Edit button</li><br />
				
				<li>Set up custom locations, if you want to tailor the system to your locality<br />Admin Panel -> Edit -> Custom Locations</li><br />

				<li>Set up fee's to charge for orders.<br />Admin Panel -> Items -> Configure Fees</li><br />
				
				<li>Enable two factor authentication?<br />Account -> Edit -> Configure Two Factor Authentication</li><br />
				
				<li>Set up a PGP key for your admin user?<br />Account -> Edit -> Add a PGP Key</li><br />
				
				<li>Force incoming messages to be PGP encrypted?<br />Account -> Edit -> Force PGP Messages</li><br />

				<li>Set critical directories to read-only:<br />$ chmod 755 <?php echo $installdir; ?> -R && chmod 777 <?php echo $installdir; ?>/application/storage/ </li><br />
				
				<li><a href='../'>Click here to see your new Bitwasp install!</a></li>
			</ul>
			
		<?php 
			// Starting installation process?
		} else if(!isset($_GET['start'])) { ?>
			Check that your bitcoin.conf looks similar to the following. Note down whatever rpcuser, rpcpassword, rpcport you have. <br /><br />
			Only remove the # symbols once you have finished the install procedure, and bitcoind is fully up-to-date.
		
<pre>rpcuser=bitcoinrpc
rpcpassword=change_me
daemon=1
checkblocks=5
#blocknotify=/usr/bin/php <?php echo $installdir; ?>/index.php callback block %s 
#alertnotify=/usr/bin/php <?php echo $installdir; ?>/index.php callback alert
txindex=1
rpcport=8332
rpcconnect=127.0.0.1</pre><br />

			Run 'crontab -e' without the quotes, and add the following. Again, don't remove the #'s until you have finished the guide.<Br /><Br />
			<pre>
#*/1 * * * * /usr/bin/php <?php echo $installdir; ?>/index.php callback autorun
#*/1 * * * * /usr/bin/php <?php echo $installdir; ?>/index.php callback process
			</pre>

			<a href='./index.php?start'>Click here to continue</a>

		<?php } else { 

		// User on install form.

		// Only load the classes in case the user submitted the form
		if($_POST && $_POST['install'] == 'Install') {

			// Load the classes and create the new objects
			
			require_once('includes/database_class.php');
			require_once('includes/jsonrpcclient.php');
			require_once('includes/core_class.php');

			$database = new Database();
			$core = new Core();

			// Validate the post data
			if($core->validate_post($_POST) == true)
			{
				$btc_conn_url = ((isset($_POST['ssl']) && $_POST['ssl'] == '1') ? 'https://' : 'http://').$_POST['btc_username'].':'.$_POST['btc_password'].'@'.$_POST['btc_ip'].':'.$_POST['btc_port'].'/';

				$bitcoin = new Jsonrpcclient(array('url' => $btc_conn_url));
				
				$data = $_POST;
				$data['encryption_key'] = $core->random_key_string();
				
				// First create the database, then create tables, then write config file
				if ($bitcoin == NULL || $bitcoin->getinfo() == NULL) {
					$message = "Unable to make connection to the bitcoin daemon. Is it running? Are your settings correct?";
					 
				} else if($database->create_database($data) == false) {
					$message = $core->show_message('error',"The database could not be created, please verify your settings.");
					
				} else if ($database->create_tables($data) == false) {
					$message = $core->show_message('error',"The database tables could not be created, please verify your settings.");
					
				} else if ($core->write_database_config($data) == false) {
					$message = $core->show_message('error',"The database configuration file covuld not be written, please chmod application/config/database.php file to 777");
					
				} else if ($database->add_config_entries($data) == false) {
					$message = $core->show_message('error','error db config');

				} else if ($core->write_config_config($data) == false) {
					$message = $core->show_message('error',"Unable to write config.");
					
				} else if ($core->write_bitcoin_config($data) == false) {
					$message = $core->show_message('error',"The bitcoin configuration file could not be written, please chmod application/config/bitcoin.php file to 777");
				}
				// If no errors, redirect to registration page
				if(!isset($message)) {
					$redir = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "https" : "http");
					$redir .= "://".$_SERVER['HTTP_HOST'];
					$redir .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
					$redir .= '?end';
					header( 'Location: ' . $redir . '' ) ;
				}

			}
			else {
				$message = $core->show_message('error','Not all fields have been filled in correctly. ');
			}
		}



		 if(isset($message)) {echo '<p class="error">' . $message . '</p>';} 
		?>

		  <form id="install_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?start">
		<fieldset>
          <legend>Database settings</legend><br />
          <label for="db_hostname">Hostname</label><br />
          <input type="text" id="hostname" value="localhost" class="input_text" name="db_hostname" /><br /><br />
          <label for="db_username">Username</label><br />
          <input type="text" id="username" class="input_text" name="db_username" /><br /><br />
          <label for="db_password">Password</label><br />
          <input type="password" id="password" class="input_text" name="db_password" /><br /><br />
          <label for="db_database">Database Name</label><br />
          <input type="text" id="database" class="input_text" name="db_database"  /><br />
        </fieldset>
        <br /><br />
        <fieldset>
          <legend>Bitcoin settings</legend><br />
          <label for="hostname">IP Address</label><br />
          <input type="text" id="hostname" value="127.0.0.1" class="input_text" name="btc_ip" /><br /><br />
          <label for="hostname">Port</label><br />
          <input type="text" id="hostname"  class="input_text" name="btc_port" /><br /><br />
          <label for="username">Username</label><br />
          <input type="text" id="username" class="input_text" name="btc_username"   /><br /><br />
          <label for="password">Password</label><br />
          <input type="password" id="password" class="input_text" name="btc_password"  /><br />
          <input type='checkbox' id='hostname' value='1' />Select if bitcoind is using SSL<br />
          
        </fieldset>
		<fieldset>
          <legend>Bitwasp Configuration</legend><br />
          <label for="admin">Administrative Password:</label><br />
          This password is for the 'admin' account, the default account with administrative powers - make it a good one. This account is required.
          <input type="text" id="hostname" class="input_text" name="admin_password" /><br /><br />

          <label for="admin">Electrum MPK:</label><br />
          This is used to create determinstic addresses without needed private keys on the server. This MPK will receive fee's from orders and registrations, and will also be used to create public keys for multi-signature order addresses.
          <input type="text" id="hostname" class="input_text" name="electrum_mpk"  /><br /><br />

<!--          <label for="">Proxy Settings: (optional)</label><br />
			<select name='proxy_type'>
				<option value='HTTP'>HTTP</option>
				<option value='SOCKS5'>SOCKS5</option>
			</select>
			<input type="text" id="hostname" value="" class="input_text" name="proxy_host" placeholder='ip:port'/><br /><br />-->

		  <label for='tidy_urls'>Tidy URL's?</label><br />
		  If you would like to remove index.php from your URL's, select Yes. For this setting to work you must copy htaccess.sample to .htaccess, and make the following change:<br />
		    <input type='radio' name='tidy_urls' value='1' checked> Yes
			<input type='radio' name='tidy_urls' value='0' > No
			<br /><br />

		  <label for="encrypt_private_messages">Encrypt Private Messages?</label><br />
		  This will ask your users to enter a password before viewing or sending a message, but if enabled they will encrypted with RSA.<br />
			<input type='radio' name='encrypt_private_messages' value='1' checked> Yes
			<input type='radio' name='encrypt_private_messages' value='0' > No
			<br /><br />
		  If you choose to enable this option, provide a password to protect your message keys:
		    <input type='text' id='hostname' class='input_text' name='admin_pm_password'  /><br /><br />
		  
		  <label for="allow_guests">Allow Guests to Browse?</label><br />
		  Turning this setting on will force users to register and login before they can see items.<br />
		    <input type='radio' name='allow_guests' value='1' checked> Yes
			<input type='radio' name='allow_guests' value='0' > No
			<br /><br />
			
		  <label for="allow_guests">Force Vendors to use PGP?</label><br />
		  Turning this setting on will force vendors to upload a PGP public key, which will allow for client-side encryption of messages, for ultimate privacy.<br />
		    <input type='radio' name='force_vendor_pgp' value='1' checked> Yes
			<input type='radio' name='force_vendor_pgp' value='0' > No
			<br /><br />
		  
        </fieldset>
		
          <input type="submit" name='install' value="Install" id="submit" />
		  </form>

	  <?php } } ?>

	</body>
</html>
