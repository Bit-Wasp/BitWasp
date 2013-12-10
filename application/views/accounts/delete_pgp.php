        <div class="span9 mainContent" id="delete-pgp">
          <h2>Edit Account</h2>
		  <br />
		  
          <?php echo form_open('pgp/delete', array('class' => 'form-horizontal')); ?>
          
          Your PGP key can be used to protect your account with two-factor authentication, and for automatic encryption of private messages. Removing your PGP key will disable these features. Confirm that you wish to delete your key:

  		    <fieldset>
    	      <div class="control-group">
  		        <label class="control-label" for="delete">Are you sure?</label>
		        <div class="controls">
                  <label class="radio inline"><input type='radio' name='delete' value='0' checked /> No</label>
                  <label class="radio inline"><input type='radio' name='delete' value='1' /> Yes</label>
			      <span class="help-inline"><?php echo form_error('delete'); ?></span>
		        </div>
		      </div> 	  

              <div class="form-actions">
		        <input type='submit' value='Update' class='btn btn-primary' />
                <?php echo anchor('account','Cancel', array('class'=>'returnLink btn'));?>
		      </div>

		    </fieldset>
		  </form>
	    </div>
