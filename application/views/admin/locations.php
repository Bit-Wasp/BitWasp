		  <div class="span9 mainContent" id="admin-dispute-form">
			<h2><?php echo anchor('admin', 'Back', 'class="btn"'); ?> Locations</h2>

			<?php if(isset($returnMessage)) echo '<div class="alert">' . $returnMessage . '</div>'; ?>

			<div class="row-fluid">
			  <div class="span8">When users are entering locations on the site, they can use the
		default country list, or you can configure the site to a specific
		area.</div>
			</div>
		  <br />
		
		  <?php echo form_open('admin/locations', array('class' => 'form-horizontal')); ?>
			<div class="row-fluid">
				<div class="span5 offset2"><strong>Select A List Of Locations</strong></div>
			</div>
			<div class="row-fluid">
			  <div class="span2 offset1">List</div>
			  <div class="span5">
				<select name="location_source" autocomplete="off">
				  <option value=""></option>
				  <option value="Default"<?php echo ($list_source == 'Default') ? ' selected="selected"' : NULL ; ?>>Default</option>
				  <option value="Custom"<?php echo ($list_source == 'Custom') ? ' selected="selected"' : NULL ; ?>>Custom</option>
				</select>
			  </div>
			</div>
			<div class="row-fluid">
			  <div class='span5 offset3'>
				<span class="help-inline"><?php echo form_error('location_source'); ?></span><br />
				  <input type='submit' name='update_location_list_source' value='Submit' class="btn"/>
				</div>
			  </div>
			</form>
		  <br />
		
		  <?php echo form_open('admin/locations', array('class' => 'form-horizontal')); ?>
			<div class="row-fluid">
			  <div class="span5 offset2"><strong>Add Custom Location</strong></div>
			</div>
			<div class="row-fluid">
			  <div class="span2 offset1">Location Name</div>
			  <div class="span5"><input type='text' name='create_location' value='' /></div>
			  <span class="help-inline"><?php echo form_error('create_location'); ?></span>
			</div>
			<div class="row-fluid">
			  <div class="span2 offset1">Parent Location</div>
			  <div class="span5">
				<?php echo $locations_parent; ?>
			  </div>
			  <span class="help-inline"><?php echo form_error('location'); ?></span>
			</div>
			<div class="row-fluid">
			  <div class='span5 offset3'>
				<input type='submit' name='add_custom_location' value='Submit' class="btn" />
			  </div>
			</div>
		  </form>
		  <br />
		
		  <?php echo form_open('admin/locations', array('class' => 'form-horizontal')); ?>
			<div class="row-fluid">
			  <div class="row-fluid">
				<div class="span5 offset2"><strong>Delete Custom Location</strong></div>
			  </div>
			  <div class="row-fluid">
				<div class="span2 offset1">Location</div>
				<div class="span5"><?php echo $locations_delete; ?></div>
			  </div>
			  <div class="row-fluid">
				<div class="span5 offset3">
				  <span class="help-inline"><?php echo form_error('location_delete'); ?></span><br />
				  <input type="submit" name="delete_custom_location" value="Submit" class="btn" />
				</div>
			  </div>
			</div>
		  </form>
		  <br />
		
		  <div class="row-fluid">
		    <div class="span5 offset2"><strong>Custom List Preview</strong></div>
		    <div class="span5 offset2"><?php echo $locations_human_readable; ?></div>
		  </div>
		
		</div>
