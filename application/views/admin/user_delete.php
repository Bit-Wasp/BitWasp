	    <div class="span9 mainContent" id="admin-users-list">
		  <h2><?php echo anchor('admin/users/list', 'Back', 'class="btn"'); ?> Delete Account: <?php echo $user['user_name']; ?></h2>
			  
		  <?php if(isset($returnMessage)) { ?>
		  <div class='alert<?php if($success == TRUE) echo ' alert-success'; ?>'><?php echo $returnMessage; ?></div>
		  <?php } ?>
		  
		  <?php echo form_open('admin/users/delete/'.$user['user_hash'], array('class' => 'form-horizontal')); ?>
		  
		  Are you sure you want to delete this user account? This will remove all userdata from the site.<br />
		  <br />
		  <div class="control-group">
            <label class="control-label" for="delete_user"><strong>Delete?</strong></label>
            <div class="controls">
              <label class="radio inline"><input type='radio' name='delete_user' value='0' /> No</label>
              <label class="radio inline"><input type='radio' name='delete_user' value='1' /> Yes</label>
   			  <span class="help-inline"><?php echo form_error('delete_user'); ?></span>
	        </div>
          </div>
			
		  <div class="form-actions">
		    <button type='submit' class="btn btn-primary">Confirm</button>
			<?php echo anchor('user/'.$user['user_hash'], 'Cancel', 'title="Cancel" class="btn"');?>
		  </div>
		</form>
	  </div>
		 
