        <div class="span9 mainContent" id="admin-panel">
		  
		  <?php echo $nav; ?>
			
		  <div class="container-fluid">
			  
			<div class="row-fluid">
			  <span class="span3">Site Status</span>
			  <span class="span7"><strong><?php echo ($config['maintenance_mode'] == TRUE) ? 'maintenance mode' : 'online'; ?></strong></span>
			</div>			  
			  
			<div class='row-fluid'>
			  <span class='span3'>Site Title</span>
			  <span class='span7'><?php echo $site_title; ?></span>
			</div>
			
			<div class="row-fluid">
			  <span class="span3">Site Description</span>
			  <span class="span7"><?php echo $site_description; ?></span>
			</div>
			
			<div class="row-fluid">
			  <div class="span3">Terms Of Service</div>
			  <div class="span5"><?php echo ($config['terms_of_service_toggle'] == FALSE) ? 'Disabled' : 'Enabled'; ?></div>
			</div>
			
			<div class='row-fluid'>
			  <span class='span3'>Allow Guests to Browse?</span>
			  <span class='span7'><?php echo ($config['allow_guests'] == 1) ? 'Enabled' : 'Disabled'; ?></span>
			</div>
			
			<?php if(isset($gpg)) { ?>
			<div class="row-fluid">
			  <span class="span3">GnuPG Version</span>
			  <span class="span7"><?php echo $gpg; ?></span>
			</div>
			<?php } ?>
			
			<div class="row-fluid">
			  <span class="span3">OpenSSL Version</span>
			  <span class="span7"><?php echo $openssl; ?></span>
			</div>

			<div class="row-fluid">
			  <span class="span3">OpenSSL Keysize</span>
			  <span class="span7"><?php echo $config['openssl_keysize']; ?></span>
			</div>
			
			<div class="row-fluid">
			  <div class="span3">Global Proxy</div>
			  <div class="span5"><?php echo ($config['global_proxy_type'] == 'Disabled') ? 'Disabled' : '('.$config['global_proxy_type'].') '.$config['global_proxy_url'];?></div>
			</div>
			
			
		  </div>
		</div>
