        <div class="span9 mainContent" id="admin_edit_users">

		  <?php echo $nav; ?>

		  <?php echo form_open('admin/edit/users', array('class' => 'form-horizontal')); ?>
  		    <fieldset>
			
			  <div class="row-fluid">
		        <div class="span3">Session Timeout</div>
		        <div class="span7"><input type='text' class='span2' name='login_timeout' value='<?php echo $config['login_timeout']; ?>' /> minutes</div>
			  </div>
		      <span class="help-inline offset2"><?php echo form_error('login_timeout'); ?></span>

			  <div class="row-fluid">
				<div class="span3">Captcha Length</div>
				<div class="span7"><input type='text' class='span2' name='captcha_length' value='<?php echo $config['captcha_length']; ?>' /> characters</div>
			  </div>
		      <span class="help-inline offset2"><?php echo form_error('captcha_length'); ?></span>
		      
			  <div class="row-fluid">
				<div class="span3">Registration Allowed?</div>
				<div class="span7">
                  <label class="radio inline"><input type='radio' name='registration_allowed' value='0' <?php echo ($config['registration_allowed'] == '0') ? 'checked' : ''; ?> /> Disabled</label>
                  <label class="radio inline"><input type='radio' name='registration_allowed' value='1' <?php echo ($config['registration_allowed'] == '1') ? 'checked' : ''; ?> /> Enabled</label>
				</div>
			  </div>
		      <span class="help-inline offset2"><?php echo form_error('registration_allowed'); ?></span>
		      
		      <div class="row-fluid">
				<div class="span3">Vendor Registration Allowed?</div>
				<div class="span7">
			      <label class="radio inline"><input type='radio' name='vendor_registration_allowed' value='0' <?php echo ($config['vendor_registration_allowed'] == '0') ? 'checked' : ''; ?> /> Disabled</label>
                  <label class="radio inline"><input type='radio' name='vendor_registration_allowed' value='1' <?php echo ($config['vendor_registration_allowed'] == '1') ? 'checked' : ''; ?> /> Enabled</label>
				</div>
			  </div>
		      <span class="help-inline offset2"><?php echo form_error('vendor_registration_allowed'); ?></span>
			
			  <div class="row-fluid">
				<div class="span3">Vendor Registration Fee</div>
				<div class="span7">
				  <div class="input-prepend">
				    <span class="add-on"><i>BTC</i></span>
					<input type='text' class='span10' name='entry_payment_vendor' value='<?php echo $config['entry_payment_vendor']; ?>' /> 
				  </div>
				  <label class="inline"><input type='checkbox' name='entry_payment_vendor_disabled' <?php echo ($config['entry_payment_vendor'] > 0) ? NULL : 'checked'; ?>	value='1' /> Disabled</label>
				</div>
			  </div>
		      <span class="help-inline offset2"><?php echo form_error('entry_payment_vendor'); ?></span>
			
			  <div class="row-fluid">
				<div class="span3">Buyer Registration Fee</div>
				<div class="span7">
				  <div class="input-prepend">
				    <span class="add-on"><i>BTC</i></span>
					<input type='text' class='span10' name='entry_payment_buyer' value='<?php echo $config['entry_payment_buyer']; ?>' /> 
				  </div>
				  <label class="inline"><input type='checkbox' name='entry_payment_buyer_disabled' <?php echo ($config['entry_payment_buyer'] > 0) ? NULL : 'checked'; ?> value='1' /> Disabled</label>
				</div>
			  </div>
		      <span class="help-inline offset2"><?php echo form_error('entry_payment_buyer'); ?></span>
			
			  <div class="row-fluid">
			    <div class="span3">Encrypt Private Message's?</div>
			    <div class="span7">
                  <label class="radio inline"><input type='radio' name='encrypt_private_messages' value='0' <?php echo ($config['encrypt_private_messages'] == '0') ? 'checked' : ''; ?> /> Disabled</label>
                  <label class="radio inline"><input type='radio' name='encrypt_private_messages' value='1' <?php echo ($config['encrypt_private_messages'] == '1') ? 'checked' : ''; ?> /> Enabled</label>
			    </div>
			  </div>
			  <span class="help-inline offset2"><?php echo form_error('encrypt_private_messages'); ?></span>
			
			  <div class="row-fluid">
			    <div class="span3">Force Vendor PGP?</div>
			    <div class="span7">
                  <label class="radio inline"><input type="radio" name="force_vendor_pgp" value="0" <?php echo ($config['force_vendor_pgp'] == '0') ? 'checked' : ''; ?> /> Disabled</label>
                  <label class="radio inline"><input type="radio" name="force_vendor_pgp" value="1" <?php echo ($config['force_vendor_pgp'] == '1') ? 'checked' : ''; ?> /> Enabled</label>
			    </div>
			  </div>
			  <span class="help-inline offset2"><?php echo form_error('force_vendor_pgp'); ?></span>			
			
			  <div class="row-fluid">
				<div class="span3">Ban After Inactivity</div>
				<div class="span7">
				  <label class="inline"><input type='text' class='span2' name='ban_after_inactivity' value='<?php echo $config['ban_after_inactivity']; ?>' /> days</label>
				  <label class="inline"><input type='checkbox' name='ban_after_inactivity_disabled' <?php echo ($config['ban_after_inactivity'] == '0') ? 'checked ' : NULL; ?>	value='1' /> Disabled</label>
				</div>
			  </div>
			  <span class="help-inline offset2"><?php echo form_error('ban_after_inactivity'); ?></span>

			  <div class="row-fluid">
				<div class="span3">Delete Messages After</div>
				<div class="span7">
				  <label class="inline"><input type='text' class='span2' name='delete_messages_after' value='<?php echo $config['delete_messages_after']; ?>' /> days</label>
				  <label class="inline"><input type='checkbox' name='delete_messages_after_disabled' <?php echo ($config['delete_messages_after'] == '0') ? 'checked ' : NULL; ?> value='1' /> Disabled</label>
				</div>
			  </div>
			  <span class="help-inline offset2"><?php echo form_error('delete_messages_after'); ?></span>
			
			
              <div class="form-actions">
		        <input type='submit' value='Update' class='btn btn-primary' />
                <?php echo anchor('account','Cancel', array('class'=>'returnLink btn'));?>
		      </div>

			</fieldset>
		  </form>
		</div>
