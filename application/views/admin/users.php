	    <div class="span9 mainContent" id="admin-panel">
		  
		  <?php echo $nav; ?>
			
		  <div class="container-fluid">
			<div class="row-fluid">
			  <span class="span3">User Count</span>
			  <span class="span7"><?php echo $user_count; ?> (<?php echo anchor('admin/users/list','User List'); ?>)</span>
			</div>
			
			<div class="row-fluid">
			  <span class='span3'>Session Timeout</span>
			  <span class='span7'><?php echo $config['login_timeout']; ?> minutes</span>			  
			</div>
			
			<div class="row-fluid">
			  <span class='span3'>Captcha Length</span>
			  <span class='span7'><?php echo $config['captcha_length']; ?> characters</span>			  
			</div>
			
			<div class="row-fluid">
			  <span class='span3'>Registration Allowed?</span>	
			  <span class='span7'><?php echo ($config['registration_allowed'] == TRUE) ? 'Enabled' : 'Disabled'; ?></span>
			</div>

			<div class="row-fluid">
			  <span class='span3'>Vendor Registration Allowed?</span>	
			  <span class='span7'><?php echo ($config['vendor_registration_allowed'] == TRUE) ? 'Enabled' : 'Disabled'; ?></span>
			</div>
			
			<div class="row-fluid">
			  <span class="span3">Vendor Registration Fee:</span>
			  <span class="span7"><?php echo ($config['entry_payment_vendor'] > 0) ? 'BTC '.$config['entry_payment_vendor'] : 'Not Required'; ?></span>
			</div>

			<div class="row-fluid">
			  <span class="span3">Buyer Registration Fee:</span>
			  <span class="span7"><?php echo ($config['entry_payment_buyer'] > 0) ? 'BTC '.$config['entry_payment_buyer'] : 'Not Required'; ?></span>
			</div>
			
			<div class="row-fluid">
			  <span class="span3">Registration Tokens</span>
			  <span class="span7"><?php echo anchor('admin/tokens','Manage'); ?></span>
			</div>
						
			<div class="row-fluid">
			  <span class='span3'>Encrypted PM's</span>
			  <span class='span7'><?php echo ($config['encrypt_private_messages'] == TRUE) ? 'Enabled' : 'Disabled'; ?></span>
			</div>
			
			<div class="row-fluid">
			  <span class='span3'>Force Vendor PGP?</span>
			  <span class='span7'><?php echo ($config['force_vendor_pgp'] == TRUE) ? 'Enabled' : 'Disabled'; ?></span>
			</div>
			
			<div class="row-fluid">
			  <span class="span3">Refund After Inactivity</span>
			  <span class="span7"><?php echo ($config['refund_after_inactivity'] == '0') ? 'Disabled' : $config['refund_after_inactivity'].' days'; ?></span>
			</div>
			
			<div class="row-fluid">
			  <span class="span3">Delete Messages After</span>
			  <span class="span7"><?php echo ($config['delete_messages_after'] == '0') ? 'Disabled' : $config['delete_messages_after'].' days'; ?></span>
			</div>
			  
		  </div>
		</div>
