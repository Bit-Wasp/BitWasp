          <div class="span6 mainContent">
            <h2>Register</h2>
            <div class='alert'><?php echo (isset($returnMessage)) ? $returnMessage.'<br />' : 'Complete the following form to register an account.'; ?></div>
            
            <?php 
			$registerPage = 'register';
			if(isset($token) && $token !== NULL)
				$registerPage .= "/$token";
			
			echo form_open($registerPage, array('class' => 'form-horizontal', 'name' => 'registerForm')); 
			echo validation_errors();
			?>
              <fieldset>
                <div class="control-group">
                  <label class="control-label" for="user_name">Username</label>
                  <div class="controls">
                    <input type='text' name='user_name' value="<?php echo set_value('user_name'); ?>" size='12' />
                    <span class="help-inline"><?php echo form_error('user_name'); ?></span>
                  </div>
                </div> 

                <div class="control-group">
                  <label class="control-label" for="password0">Password</label>
                  <div class="controls">
                    <input type='password' name='password0' value='' size='12' autocomplete="off" />
                    <span class="help-inline"><?php echo form_error('password0'); ?></span>
                  </div>
                </div>

                <div class="control-group">
                  <label class="control-label" for="password1">Password (confirm)</label>
                  <div class="controls">
                    <input type='password' name='password1' value='' size='12' autocomplete="off" />
                    <span class="help-inline"><?php echo form_error('password1'); ?></span>
                  </div>
                </div>
                
                <div class="control-group">
                  <label class="control-label" for="message_pin0">Message PIN</label>
                  <div class="controls">
                    <input type='password' name='message_pin0' value='' size='12' autocomplete="off" />
                    <br />
                    <span class="help-inline"><?php echo form_error('message_pin0')."<br />"; ?>Do not forget this PIN.</span>
                  </div>
                </div>

                <div class="control-group">
                  <label class="control-label" for="message_pin1">Message PIN (confirm)</label>
                  <div class="controls">
                    <input type='password' name='message_pin1' value='' size='12' autocomplete="off" />
                    <span class="help-inline"><?php echo form_error('message_pin1'); ?></span>
                  </div>
                </div>

<?php
if(isset($token_info) && $token_info !== FALSE){?>
			    <div class="control-group">
                  <label class="control-label" for="user_type">Role</label>
	              <div class="controls">
					<label class='control-label'><?php echo $token_info['user_type']['txt'];?></label>
					<input type='hidden' name='user_type' value='<?php echo $token_info['user_type']['int']; ?>' />
	              </div>
	            </div>
<?php } else { ?>
				<div class="control-group">
				  <label class="control-label" for="user_type">Role</label>
				  <div class="controls">
					<select name='user_type' value='1'>
					  <option value='1'>Buyer</option>
					  <option value='2'>Vendor</option>
					</select><br />
					<div class="span8"><?php 
echo form_error('user_type');
if($force_vendor_pgp == 'Enabled')
	echo "<br />If you are registering as a vendor, it is required you upload a PGP public key. Please have one ready on your first login.";
?></div>
				  </div>
				</div>
<?php } ?>
				<span class="help-inline"><?php
	        if($force_vendor_pgp == 'Enabled' && isset($token_info) && $token_info['user_type']['txt'] == 'Vendor'){ 
				echo "If you are registering as a vendor, it is required you upload a PGP public key. Please have one ready on your first login.";
	        } ?></span><br />

				<div class="control-group">
                  <label class="control-label" for="location">Location</label>
                  <div class="controls">
				    <select name='location' value='1'>
<?php foreach($locations as $location) { ?>
					  <option value='<?php echo $location['id']; ?>'><?php echo $location['country']; ?></option>
<?php } ?>
				    </select>
                    <span class="help-inline"><?php echo form_error('location'); ?></span>
                  </div>
                </div> 

				<div class="control-group">
				  <label class="control-label" for="local_currency">Local Currency</label>
				  <div class="controls">
				    <select name='local_currency'>
<?php foreach($currencies as $currency) : ?>
					  <option value='<?php echo $currency['id']; ?>'<?php echo ($currency['id'] == '0') ? ' selected="selected"' : NULL; ?>><?php echo $currency['name']; ?></option>
<?php endforeach; ?>
					</select>
                    <span class="help-inline"><?php echo form_error('location'); ?></span>					
				  </div>
                </div>
                
                <?php if($terms_of_service !== FALSE) { ?>
                <div class="control-group">
				  <label class="control-label" for="terms_of_service">Terms of Service</label>
				  <div class="controls">
					<textarea class='span12' cols='6' rows='7' readonly><?php echo $terms_of_service; ?></textarea>
					<br />
					<input type='checkbox' name='tos_agree' value='1' /> Click to agree to the terms of service.
				  </div>
                </div>
                <?php } ?>

                <!-- Captcha -->
                <div class="control-group">
                  <label class="control-label" for="captcha">Captcha</label>
                  <div class="controls">
                    <div class="captcha-img"><?php echo $captcha;?></div>
                  </div>
                </div>
                <div class="control-group">
                  <div class="controls">
                    <input type="text" name='captcha' size='12'/>
                    <span class="help-inline"><?php echo form_error('captcha'); ?></span>
                  </div>
                </div>
                <!-- /Captcha -->

			    <noscript><div style="display:none"><input type='hidden' name='js_disabled' value='1' /></div></noscript>

                <div class="form-actions">
                  <input type='submit' class="btn btn-primary" value="Register" onclick='make_hash()' />                  
                  <?php echo anchor('login', 'Cancel', 'title="Cancel" class="btn"');?>
                </div>
              </fieldset>
            </form>
          </div>
