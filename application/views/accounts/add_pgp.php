
          <div class="span9 mainContent">
            <h2>Add PGP Public Key</h2>	
            
            <div class="alert">            
				<?php if(isset($returnMessage)){ echo $returnMessage; } else { ?>
				Enter your public key to continue.
				<?php } ?>
			</div>
			
            <?php echo form_open('pgp/add', array('class' => 'form-horizontal')); ?>
              <fieldset>
                <div class="control-group">
                  <label class="control-label" for="public_key">Public Key</label>
                  <div class="controls">
                    <textarea name='public_key' class='span11' rows='10' ></textarea>
                    <span class="help-inline"><?php echo form_error('public_key'); ?></span>
                  </div>
                </div>

                <div class="form-actions">
                  <input type='submit' value='Submit' class="btn btn-primary" />
                  <?php echo anchor('account', 'Cancel', 'title="Cancel" class="btn"');?>
                </div>
                
              </fieldset>
            </form>
          </div>
