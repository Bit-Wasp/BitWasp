		<div class="span9 mainContent" id="vendor_public_keys">
			<h2>Bitcoin Public Keys</h2>

			<?php if(isset($returnMessage)) echo '<div class="alert'.((isset($success) && $success == TRUE) ? 'alert-sucecss' : '').'">'.$returnMessage.'</div>'; ?>
		  
			<?php echo form_open('accounts/public_keys', array('class' => 'form-horizontal')); ?>
				<div class='row-fluid'>
					<span class='span10'>Use this form to enter public keys in advance of orders. Public keys should be separated by a new line.</span>
				</div>
				<br />
				<div class='row-fluid'>
					<div class='span2'>Public Keys</div>
					<div class='span7'><textarea name='public_key_list' class='span12'></textarea></div>
				</div>
				<div class="form-actions">
					<input type='submit' name='submit_public_keys' value='Upload Public Keys' class='btn btn-primary' />
					<?php echo anchor('account','Cancel', array('class'=>'returnLink btn'));?>
				</div>
			</form>
          
          
			<?php echo form_open('accounts/public_keys', array('class' => 'form-horizontal')); ?>
				<?php if($available_public_keys !== FALSE && count($available_public_keys) > 0 ) { ?>
				<h2>Current Public Keys</h2>
				<div class='row-fluid'>
					<span class='span10'>You have <?php echo count($available_public_keys); ?> public keys available.</span>
				</div>
          
				<div class='row-fluid offset1'>
					<?php foreach($available_public_keys as $public_key) { ?>
					<div class='row-fluid'>
						<?php echo $public_key['public_key']; ?><br />
						Bitcoin Address: <?php echo $public_key['address']; ?>
					</div><Br />
					<?php } ?>
				</div>
				<?php } ?>
			  
			</form>
		</div>
