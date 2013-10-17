        <div class="span9 mainContent" id="admin_edit_items">

		  <?php echo $nav; ?>

		  <?php echo form_open('admin/edit/items', array('class' => 'form-horizontal')); ?>
				
		    <div class="row-fluid">
			  <div class="span4 offset2"><strong>Add A Category</strong></div>
		    </div>
			  
		    <div class="row-fluid">
		      <div class="span2 offset1">Name</div>
			  <div class="span4"><input type='text' name='category_name' value='' /></div>
			  <span class="help-inline"><?php echo form_error('category_name'); ?></span>	
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
				<select name='category_id' autocomplete="off">
				  <option value=''></option>
<?php foreach($categories as $category) { ?>
				  <option value='<?php echo $category['id']; ?>'><?php echo $category['name']; ?></option>
<?php } ?>				  
				</select>
			  </div>
	          <span class="help-inline"><?php echo form_error('category_id'); ?></span>			  			  
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
				<select name='category_id' autocomplete="off">
				  <option value=''></option>
<?php foreach($categories as $category) { ?>
				  <option value='<?php echo $category['id']; ?>'><?php echo $category['name']; ?></option>
<?php } ?>		
				</select>
			  </div>
			  <span class="help-inline"><?php echo form_error('category_id'); ?></span>
			</div>
			
		    <div class="row-fluid">
			  <div class="span4 offset3"><input type='submit' name='delete_category' value='Delete' class='btn btn-tiny' /></div>
	        </div>
	        
	      </form>
		</div>
