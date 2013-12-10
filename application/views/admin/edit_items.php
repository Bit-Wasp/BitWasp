        <div class="span9 mainContent" id="admin_edit_items">

		  <?php echo $nav; ?>

		  <?php echo form_open('admin/edit/items', array('class' => 'form-horizontal')); ?>
			
			<div class="row-fluid">
		      <div class="span3">Auto Finalize/Refund:</div>
		      <div class="span7"><input type='text' class='span2' name='auto_finalize_threshold' value='<?php echo $config['auto_finalize_threshold']; ?>' /> days</div>
			</div>
			<div class="row-fluid">
		      <div class="span7 offset3"><input type='checkbox' name='auto_finalize_threshold' value='1' <?php echo ($config['auto_finalize_threshold'] == '0') ? ' checked' : NULL; ?>' /> Disabled</div>
			</div>
		    <span class="help-inline offset2"><?php echo form_error('auto_finalize_threshold'); ?></span>

            <div class="form-actions">
		      <input type='submit' name='admin_edit_items' value='Update' class='btn btn-primary' />
              <?php echo anchor('account','Cancel', array('class'=>'returnLink btn'));?>
		    </div>
			
		  </form>


		  <?php echo form_open('admin/edit/items', array('class' => 'form-horizontal')); ?>
				
		    <div class="row-fluid">
			  <div class="span4 offset2"><strong>Add A Category</strong></div>
		    </div>
			  
		    <div class="row-fluid">
		      <div class="span2 offset1">Name</div>
			  <div class="span4"><input type='text' name='create_name' value='' /></div>
			  <span class="help-inline"><?php echo form_error('create_name'); ?></span>	
	        </div>	
	        
		    <div class="row-fluid">
		      <div class="span2 offset1">Parent Category</div>
			  <div class="span4">
			    <select name='category_parent' autocomplete="off">					
				  <option value=''></option>
                  <option value='0'>Root Category</option>				  
<?php foreach($categories as $category) { ?>
				  <option value='<?php echo $category['id']; ?>'><?php echo $category['name']; ?></option>
<?php } ?>		
				</select>	
			  </div>
	          <span class="help-inline"><?php echo form_error('category_parent'); ?></span>			  
		    </div>	
				
		    <div class="row-fluid">
			  <div class="span4 offset3"><input type='submit' name='add_category' value='Add' class='btn btn-tiny' /></div>
	        </div>
		  </form>

			
		  <?php echo form_open('admin/edit/items', array('class' => 'form-horizontal')); ?>
		    <div class="row-fluid">
			  <div class="span4 offset2"><strong>Rename Category</strong></div>
		    </div>
		    
		    <div class="row-fluid">
			  <div class="span2 offset1">Category</div>
			  <div class="span4">
				<select name='rename_id' autocomplete="off">
				  <option value=''></option>
<?php foreach($categories as $category) { ?>
				  <option value='<?php echo $category['id']; ?>'><?php echo $category['name']; ?></option>
<?php } ?>				  
				</select>
			  </div>
	          <span class="help-inline"><?php echo form_error('rename_id'); ?></span>			  			  
		    </div>
		    
		    <div class="row-fluid">
			  <div class="span2 offset1">New Name</div>
			  <div class="span4"><input type='text' name='category_name' value='' /></div>
	          <span class="help-inline"><?php echo form_error('category_name'); ?></span>			  			  
		    </div>
		    
		    <div class="row-fluid">
			  <div class="span4 offset3"><input type='submit' name='rename_category' value='Rename' class='btn btn-tiny' /></div>
	        </div>
		  </form>
			
		  <?php echo form_open('admin/edit/items', array('class' => 'form-horizontal')); ?>
		    
			<div class="row-fluid">
			  <div class="span4 offset2"><strong>Delete A Category</strong></div>
			</div>
			    
			<div class="row-fluid">
			  <div class="span2 offset1">Category</div>
			  <div class="span4">
				<select name='delete_id' autocomplete="off">
				  <option value=''></option>
<?php foreach($categories as $category) { ?>
				  <option value='<?php echo $category['id']; ?>'><?php echo $category['name']; ?></option>
<?php } ?>		
				</select>
			  </div>
			  <span class="help-inline"><?php echo form_error('delete_id'); ?></span>
			</div>
			
		    <div class="row-fluid">
			  <div class="span4 offset3"><input type='submit' name='delete_category' value='Delete' class='btn btn-tiny' /></div>
	        </div>
	        
	      </form>
		</div>
