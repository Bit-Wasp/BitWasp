          <div class="span6 mainContent">
            <h2>Register</h2>
            
            <div class='alert'>
<?php 
if(isset($returnMessage)) { 
	echo $returnMessage;
} else { 
	echo "Complete the following form to register an account.";
} 
?>
            </div>
<?php 
$registerPage = 'register';
if(isset($token) && $token !== NULL)
	$registerPage .= "/$token";
			
echo form_open($registerPage, array('class' => 'form-horizontal')); 
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
                    <input type='password' name='password0' value='' size='12' />
                    <span class="help-inline"><?php echo form_error('password0'); ?></span>
                  </div>
                </div>

                <div class="control-group">
                  <label class="control-label" for="password1">Password (confirm)</label>
                  <div class="controls">
                    <input type='password' name='password1' value='' size='12' />
                    <span class="help-inline"><?php echo form_error('password1'); ?></span>
                  </div>
                </div>
<?php
if(isset($token_info) && $token_info !== FALSE){?>
			    <div class="control-group">
                  <label class="control-label" for="user_type">Role</label>
	              <div class="controls"><?php echo $token_info['role']['str'];?>
	              <span class="span6">
<?php
if($force_vendor_pgp == 'Enabled' && $token_info['role']['str'] == 'Vendor') echo "If you are registering as a vendor, it is required you upload a PGP public key. Please have one ready on your first login.";
?>
                   </span><br />
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
					<div class="span8">
<?php echo form_error('user_type')."<br />";
if($force_vendor_pgp == 'Enabled') echo "If you are registering as a vendor, it is required you upload a PGP public key. Please have one ready on your first login.";
?>
					</div>
				  </div>
				  
					
				</div>
<?php } ?>

               <div class="control-group">
                  <label class="control-label" for="location">Location</label>
                  <div class="controls">
				    <select name='location'>
<?php foreach($locations as $location): ?>
					  <option value='<?php echo $location['id']; ?>'><?php echo $location['country']; ?></option>
<?php endforeach; ?>
				    </select>
                    <span class="help-inline"><?php echo form_error('location'); ?></span>
                  </div>
                </div> 
                
                <div class="control-group">
				  <label class="control-label" for="local_currency">Local Currency</label>
				  <div class="controls">
					<select name='local_currency'>
<?php foreach($currencies as $currency) : ?>
					  <option value='<?php echo $currency['id']; ?>'<?php echo ($current['id'] == '0') ? ' selected="selected"' : NULL; ?>><?php echo $currency['name']; ?></option>
<?php endforeach; ?>
					</select>
                    <span class="help-inline"><?php echo form_error('local_currency'); ?></span>

				  </div>
                </div>
                
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

                <div class="form-actions">
                  <button type='submit' class="btn btn-primary">Register</button>
                  <?php echo anchor('login', 'Cancel', 'title="Cancel" class="btn"');?>
                </div>
              </fieldset>
            </form>
          </div>
