          <div class="span9 mainContent">
			<h2>Message's PIN</h2>
            <div class="alert">            
				<?php if(isset($returnMessage)){ echo $returnMessage; } else { ?>
				You must enter your PIN to decrypt your messages.
				<?php } ?>
			</div>
            <?php echo form_open('message/pin', array('class' => 'form-horizontal')); ?>
            <fieldset>
              <div class="control-group">
                <label class="control-label" for="pin">PIN</label>
                <div class="controls">
                  <input type='password' name='pin' value="" autocomplete="off"/>
                  <span class="help-inline"><?php echo form_error('pin'); ?></span>
                </div>
              </div>

              <div class="form-actions">
                <button type='submit' class="btn btn-primary">Submit</button>
                <?php echo anchor('', 'Cancel', 'title="Cancel" class="btn"');?>
              </div>
            </fieldset>
          </form>
        </div>
