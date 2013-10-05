        <div class="span9 mainContent" id="send-message">
          <h2>Send Message</h2>
            <div class="alert">            
				<?php if(isset($returnMessage)){ echo $returnMessage; } else { ?>
				Enter your message below:
				<?php } ?>
			</div>
          <?php echo form_open($action_uri, array('name'=>'sendMessageForm', 'class' => 'form-horizontal')); ?>

            <fieldset>
              <div class="control-group">
                <label class="control-label" for="recipient">Recipient</label>
                <div class="controls">
                  <input type='text' class="span9" name='recipient' value="<?php echo $to_name;?>" />
                  <span class="help-inline"><?php echo form_error('recipient'); ?></span>
                </div>
              </div>

              <div class="control-group">
                <label class="control-label" for="subject">Subject</label>
                <div class="controls">
                  <input type='text' class="span9" name='subject' value="<? if(isset($subject) && $subject !== '[no subject]'){ echo $subject; } ?>" />
                  <span class="help-inline"><?php echo form_error('subject'); ?></span>
                </div>
              </div>

              <div class="control-group">
                <label class="control-label" for="message">Message</label>
                <div class="controls">
                  <textarea name="message" class="span9" rows='6'><?php echo set_value('message'); ?></textarea>
                  <span class="help-inline"><?php echo form_error('message'); ?></span>
                </div>
              </div>

<?php if($public_key !== '') { ?>
	          <noscript>
	            <div class="control-group">
                  <label class="control-label" for="pgp_encrypt">PGP Encryption?</label>
                  <div class="controls">
                    <label class="checkbox inline">
	                  <input type='checkbox' name='pgp_encrypt' value='1' /> 
                    </label>
	              </div>
                </div>
              </noscript>
<?php } ?>
	
			  <div class="control-group">
				<label class="control-label" for="delete_on_read">Delete After Reading?</label>
				<div class="controls">
				  <label class="checkbox inline">
					<input type='checkbox' name='delete_on_read' value='1' />
				  </label>
				</div>
			  </div>
		
              <textarea style="display:none;" name="public_key"><?php echo $public_key; ?></textarea>

              <div class="form-actions">
                <input type='submit' class="btn btn-primary" value="Send" onclick='messageEncrypt()' />
                <?php echo anchor('messages', 'Cancel', 'class="btn"');?>
              </div>
            </fieldset>
          </form>
		</div>
