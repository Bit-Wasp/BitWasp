        <div class="span9 mainContent" id="view-account">
          <div class="container-fluid">
			  
            <div class="row-fluid">
              <div class="span9 btn-group">
				<h2><?php if($logged_in == TRUE) echo anchor('message/send/'.$user['user_hash'],'Message', 'class="btn"'); ?>  
					<?php echo $user['user_name']; ?></h2>
              </div>
            </div>

			<div class="row-fluid">
			  <div class="span2"><strong>Location</strong></div>
			  <div class="span7"><?php echo $user['location_f']; ?></div>
			</div>

            <div class="row-fluid">
              <div class="span2"><strong>Registered</strong></div>
              <div class="span7"><?php echo $user['register_time_f']; ?></div>
            </div>

<?php if($user['display_login_time'] == '1') { ?>
  	        <div class="row-fluid">
	          <div class="span2"><strong>Last Activity</strong></div>
	          <div class="span7"><?php echo $user['login_time_f']; ?></div>
	        </div>
<?php } ?>

<?php if(isset($user['pgp']['public_key'])) { ?>

			<div class="row-fluid">
			  <div class="span2"><strong>PGP Fingerprint</strong></div>
			  <div class="span7"><?php echo $user['pgp']['fingerprint_f']; ?></div>
			</div>

            <div class="row-fluid">
              <div class="span2"><strong>PGP Public Key</strong></div>
              <pre id="publicKeyBox" class="span9 well"><?php echo $user['pgp']['public_key']; ?>
			  </pre>
            </div>
<?php } ?>
          </div>
        </div>
