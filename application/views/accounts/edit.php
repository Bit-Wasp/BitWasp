        <div class="span9 mainContent" id="edit-account">
          <h2>Edit Account</h2>
		  <br />
		  
          <?php echo form_open('account/edit', array('class' => 'form-horizontal')); ?>
  		    <fieldset>
				<?php echo validation_errors(); ?>
	  
    	      <div class="control-group">
  		        <label class="control-label" for="location">Location</label>
		        <div class="controls">
			      <select name='location'>
<?php foreach($locations as $location): ?>
			        <option value='<?php echo $location['id']; ?>'<?php echo ($location['id'] == $user['location']) ? ' selected="selected"' : ''; ?>><?php echo $location['country']; ?></option>
<?php endforeach; ?>
			      </select>
			      <span class="help-inline"><?php echo form_error('location'); ?></span>
		        </div>
		      </div> 	  
		      
    	      <div class="control-group">
  		        <label class="control-label" for="local_currency">Local Currency</label>
		        <div class="controls">
			      <select name='local_currency'>
<?php foreach($currencies as $currency): ?>
			        <option value='<?php echo $currency['id']; ?>'<?php echo ($currency['id'] == $user['local_currency']) ? ' selected="selected"' : ''; ?>><?php echo $currency['name']." (".$currency['symbol'].")"; ?></option>
<?php endforeach; ?>
			      </select>
			      <span class="help-inline"><?php echo form_error('local_currency'); ?></span>
		        </div>
		      </div> 	  
		
              <div class="control-group">
                <label class="control-label" for="display_login_time">Display login activity?</label>
                <div class="controls">
                  <label class="radio inline"><input type='radio' name='display_login_time' value='0' <?php echo ($user['display_login_time'] == '0') ? 'checked' : ''; ?> /> Disabled</label>
                  <label class="radio inline"><input type='radio' name='display_login_time' value='1' <?php echo ($user['display_login_time'] == '1') ? 'checked' : ''; ?> /> Enabled</label>
   			      <span class="help-inline"><?php echo form_error('display_login_time'); ?></span>
	            </div>
              </div>
  
<?php if(isset($user['pgp'])) { ?>
              <div class="control-group">
                <label class="control-label" for="pgp_key">PGP Fingerprint</label>
                <div class="controls">
                  <label class="control-label" for="pgp_key_fingerprint"><?php echo $user['pgp']['fingerprint_f']; ?></label>
                  <label class="control-label">
                  <?php if($option_replace_pgp == TRUE) { echo anchor('pgp/replace', 'Replace', 'class="btn btn-danger btn-small"'); }
                  else { echo anchor('pgp/delete', 'Delete', 'class="btn btn-danger btn-small"'); } ?>
                  </label>
                </div>
              </div>
          
              <div class="control-group">
			    <label class="control-label" for="two_factor_auth">Two Factor Authentication</label>
			    <div class="controls">
			      <label class="radio inline"><input type='radio' name='two_factor_auth' value='0' <?php echo ($user['two_factor_auth'] == '0') ? 'checked' : ''; ?> /> Disabled</label>
			      <label class="radio inline"><input type='radio' name='two_factor_auth' value='1' <?php echo ($user['two_factor_auth'] == '1') ? 'checked' : ''; ?> /> Enabled</label>
			      <span class="help-inline"><?php echo form_error('two_factor_auth'); ?></span>
			    </div>
              </div>
          
              <div class="control-group">
			    <label class="control-label" for="force_pgp_messages">Force PGP Messages</label>
			    <div class="controls">
			      <label class="radio inline"><input type='radio' name='force_pgp_messages' value='0' <?php echo ($user['force_pgp_messages'] == '0') ? 'checked' : ''; ?> /> Disabled</label>
			      <label class="radio inline"><input type='radio' name='force_pgp_messages' value='1' <?php echo ($user['force_pgp_messages'] == '1') ? 'checked' : ''; ?> /> Enabled</label>
   			      <span class="help-inline"><?php echo form_error('force_pgp_messages'); ?></span>
			    </div>
              </div>
          
              <div class="control-group">
			    <label class="control-label" for="block_non_pgp">Block non-PGP Messages</label>
			    <div class="controls">
			      <label class="radio inline"><input type='radio' name='block_non_pgp' value='0' <?php echo ($user['block_non_pgp'] == '0') ? 'checked' : ''; ?> /> Disabled</label>
			      <label class="radio inline"><input type='radio' name='block_non_pgp' value='1' <?php echo ($user['block_non_pgp'] == '1') ? 'checked' : ''; ?> /> Enabled</label>
   			      <span class="help-inline"><?php echo form_error('block_non_pgp'); ?></span>
			    </div>
              </div>
          
<?php } else { ?>
		      <div class="control-group">
                <label class="control-label" for="pgp">PGP Features</label>
			    <div class="controls">
			      <div class="span7"><?php echo anchor('pgp/add', 'Add a PGP key'); ?> to enable features such as two-factor authentication, or automatic encryption of messages.</div>
			    </div>
		      </div>
<?php } ?>

              <div class="form-actions">
		        <input type='submit' value='Update' class='btn btn-primary' />
                <?php echo anchor('account','Cancel', array('class'=>'returnLink btn'));?>
		      </div>
		    </fieldset>
		  </form>
	    </div>
