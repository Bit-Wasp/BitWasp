        <div class="span9 mainContent" id="admin_maintenance">
		  <h2>Maintenance Settings</h2>
		  
		  <div class='row-fluid'>
			<div class='span8'>You can use this panel to put the website into maintenance mode. Guests will not be able to view the site, and non-administrative users will be logged out. User wallet related features will be disabled. Once reenabled, your previous settings will be restored.</div>
		  </div>
		  <br />
		  
		  <div class='row-fluid'>
			<div class='span8'>If maintenance mode has been triggered by the bitcoin daemon, or due to an alert reported on github, a message will be disabled informing you of the reason and giving advice.</div>
		  </div>
		  <br />
		  
		  <?php echo form_open('admin/maintenance', array('class' => 'form-horizontal')); ?>
		  <?php echo validation_errors(); ?>
		    <div class='row-fluid'>
			  <div class='span3'><strong>Current Status</strong></div>
			  <div class='span4'><i><?php echo ($config['maintenance_mode'] == TRUE) ? 'Enabled' : 'Disabled'; ?></i></div>
		    </div>
		    
		    <div class='row-fluid'>
			  <div class='span3'><strong>New Setting</strong></div>
			  <div class='span4'>
			    <select name='maintenance_mode' autocomplete='off'>
				  <option value='0'<?php echo ($config['maintenance_mode'] !== FALSE) ? ' selected="selected"': '' ; ?>>Disabled</option>
				  <option value='1'<?php echo ($config['maintenance_mode'] !== TRUE) ? ' selected="selected"' : '' ; ?>>Enabled</option>
			    </select>
			  </div>
		    </div>
		    <span class='help-inline'><?php echo form_error('maintenenace_mode'); ?></span>

   			<div class="form-actions">
			  <input type='submit' name='set_maintenance_mode' value='Update' class="btn btn-primary" />
			  <?php echo anchor('admin', 'Cancel', 'title="Cancel" class="btn"');?>
			</div>

		    
		  </form>
		  
		</div>
