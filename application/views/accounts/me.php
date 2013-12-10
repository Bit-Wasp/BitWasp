        <div class="span9 mainContent" id="own-account">
          <div class="container-fluid">
			  
            <div class="row-fluid">
              <div class="span9 btn-group">
				<h2><?php echo anchor('account/edit', 'Edit', 'class="btn"'); ?>  
					<?php echo $user['user_name']; ?></h2>
              </div>
            </div>
			<br />
			<div class="row-fluid">
			  <div class="span3"><strong>Profile URL</strong></div>
			  <div class="span7"><?php echo anchor('user/'.$user['user_hash']); ?></div>
			</div>
			
			<div class="row-fluid">
			  <div class="span3"><strong>Location</strong></div>
			  <div class="span7"><?php echo $user['location_f']; ?></div>
			</div>
			
			<div class="row-fluid">
			  <div class="span3"><strong>Local Currency</strong></div>
			  <div class="span7"><?php echo $user['currency']['name']." (".$user['currency']['symbol'].")"; ?></div>
			</div>
			
            <div class="row-fluid">
              <div class="span3"><strong>Date Registered</strong></div>
              <div class="span7"><?php echo $user['register_time_f']; ?></div>
            </div>

			<div class="row-fluid">
			  <div class="span3"><strong>Display activity?</strong></div>
			  <div class="span7"><?php echo ($user['display_login_time'] == '1') ? 'Enabled':'Disabled'; ?></div>
			</div>
			
  	        <div class="row-fluid">
	          <div class="span3"><strong>Last Login</strong></div>
	          <div class="span7"><?php echo $user['login_time_f']; ?></div>
	        </div>

<?php if(isset($user['pgp']['public_key'])) { ?>
            <div class="row-fluid">
              <div class="span3"><strong>PGP Fingerprint</strong></div>
              <div class="span7"><?php echo $user['pgp']['fingerprint_f']; ?></div>
            </div>
            
			<div class="row-fluid">
			  <div class="span3"><strong>Two Factor Authentication</strong></div>
			  <div class="span7"><?php echo ($user['two_factor_auth'] == '1') ? 'Enabled':'Disabled'; ?></div>
			</div>
            
            <div class="row-fluid">
			  <div class="span3"><strong>Force PGP Messages?</strong></div>
			  <div class="span7"><?php echo ($user['force_pgp_messages'] == '1') ? 'Enabled':'Disabled'; ?></div>
            </div>

            <div class="row-fluid">
			  <div class="span3"><strong>Block non-PGP messages?</strong></div>
			  <div class="span7"><?php echo ($user['block_non_pgp'] == '1') ? 'Enabled':'Disabled'; ?></div>
            </div>
            
<?php } else { ?>
			<div class="row-fluid">
			  <div class="span3"><strong>PGP Features</strong></div>
			  <div class="span6"><?php echo anchor('pgp/add', 'Add a PGP key'); ?> to enable features such as two-factor authentication, or automatic encryption of messages.</div>
			</div>
<?php }?>

          </div>
        </div>
 
