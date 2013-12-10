          <div class="mainContent">
            <h2>Two Factor Authentication</h2>
              <?php echo form_open('login/two_factor', array('class' => 'form-horizontal')); ?>
              <fieldset>
				<div class="alert">
<?php if(isset($returnMessage)) { echo $returnMessage; } else { ?>
                Decrypt the following PGP text and enter it below:
<?php } ?>
                </div>
                <div class="control-group">
                <pre class="well span9"><?php echo $challenge;?></pre>
				</div>
				
                <div class="control-group">
                  <label class="control-label" for="answer">Token</label>
                  <div class="controls">
                    <input type="text" name='answer' size='12'/>
                    <span class="help-inline"><?php echo form_error('answer'); ?></span>
                  </div>
                </div>

                <div class="form-actions">
                  <button type='submit' class="btn btn-primary">Continue</button>
                  <?php echo anchor('logout', 'Cancel', 'title="Cancel" class="btn"');?>
                </div>
              <fieldset>
            </form>
          </div>
