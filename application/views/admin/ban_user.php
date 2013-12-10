        <div class="span9 mainContent" id="admin_edit_">
		  <h2>Ban User</h2>
		  
		  <?php echo form_open('admin/ban_user/'.$user['user_hash'], array('class' => 'form-horizontal')); ?>
		  
		    <p>To flag <?php echo $user['user_name']; ?> as <?php if($user['banned'] == '1') echo 'un'; ?>banned, change and submit this form.</p>
		  
		    <div class="control-group">
              <label class="control-label" for="ban_user">Are you sure?</label>
              <div class="controls">
                <label class="radio inline"><input type='radio' name='ban_user' value='0' <?php echo ($user['banned'] == '0') ? 'checked ' : ''; ?>/> Not Banned</label>
                <label class="radio inline"><input type='radio' name='ban_user' value='1' <?php echo ($user['banned'] == '1') ? 'checked ' : ''; ?>/> Banned</label>
   			    <span class="help-inline"><?php echo form_error('ban_user'); ?></span>
	          </div>
            </div>
			
			<div class="form-actions">
			  <button type='submit' class="btn btn-primary">Confirm</button>
			  <?php echo anchor('user/'.$user['user_hash'], 'Cancel', 'title="Cancel" class="btn"');?>
			</div>
		  
		  </form>
		</div>
