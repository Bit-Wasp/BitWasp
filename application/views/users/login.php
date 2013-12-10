          <div class="mainContent span6">
            <h2>Login</h2>

            <div class="alert">            
<?php if(isset($returnMessage)){ echo $returnMessage; } else { ?>
				Enter your user name and password to login.
<?php } ?>
			</div>
            <?php echo form_open('login', array('class' => 'form-horizontal', 'name' => 'loginForm')); ?>
            <fieldset>
              <div class="control-group">
                <label class="control-label" for="user_name">Username</label>
                <div class="controls">
                  <input type='text' name='user_name' value="<?php echo set_value('user_name'); ?>" />
                  <span class="help-inline"><?php echo form_error('user_name'); ?></span>
                </div>
              </div>

              <div class="control-group">
                <label class="control-label" for="password">Password</label>
                <div class="controls">
                  <input type='password' name='password' value='' autocomplete="off" />
                  <span class="help-inline"><?php echo form_error('password'); ?></span>
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
                  <input type="text" name='captcha' />
                  <span class="help-inline"><?php echo form_error('captcha'); ?></span>
                </div>
              </div>
              <!-- /Captcha -->

			  <noscript><div style="display:none"><input type='hidden' name='js_disabled' value='1' /></div></noscript>

              <div class="form-actions">
                <input type='submit' class="btn btn-primary" value="Login" onclick='make_hash()' />
                <?php echo anchor('register', 'Register?', 'title="Register" class="btn"');?>
              </div>
            </fieldset>
          </form>
        </div>
