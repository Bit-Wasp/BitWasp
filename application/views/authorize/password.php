          <div class="span9 mainContent">
            <h2>Authorize Request</h2>	
            <div class="alert">            
				<?php if(isset($returnMessage)){ echo $returnMessage; } else { ?>
				As this page has heightened security, you must enter your login details to continue.
				<?php } ?>
			</div>
			
			<?php echo form_open('authorize', array('class' => 'form-horizontal', 'name' => 'authorizeForm')); ?>
              <div class="control-group">
                <label class="control-label" for="password">Password</label>
                <div class="controls">
                  <input type='password' name='password' value='' />
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
                <input type='submit' class="btn btn-primary" value="Continue" onclick='make_hash()' />
                <?php echo anchor('home', 'Cancel', 'title="Cancel" class="btn"');?>
              </div>
			</form>
