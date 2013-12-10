        <div class="span9 mainContent" id="replace-pgp">
          <h2>Replace PGP key</h2>
		  
          <?php echo form_open('pgp/replace', array('class' => 'form-horizontal')); ?>
          <?php if(isset($returnMessage)) { echo $returnMessage; } else { ?>
          Enter your replacement PGP key below. 
          <?php } ?>
  		    <fieldset>
				<br />
              <div class="control-group">
                <label class="control-label" for="public_key">Public Key</label>
                <div class="controls">
                  <textarea name='public_key' class='span11' rows='10' ></textarea>
                  <span class="help-inline"><?php echo form_error('public_key'); ?></span>
                </div>
              </div>

              <div class="form-actions">
		        <input type='submit' value='Update' class='btn btn-primary' />
                <?php echo anchor('account','Cancel', array('class'=>'returnLink btn'));?>
		      </div>

		    </fieldset>
		  </form>
	    </div>
