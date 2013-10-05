        <div class="span9 mainContent" id="admin_edit_items">

		  <?php echo $nav; ?>

		  <?php echo form_open('admin/edit/items', array('class' => 'form-horizontal')); ?>
				
		    <div class="row-fluid">
			  <div class="span2"></div>
			  <div class="span5"><b>Add A Category</b></div>
		    </div>
			  
		    <div class="row-fluid">
			  <div class="span1"></div>
		      <div class="span2">Name</div>
			  <div class="span4"><input type='text' name='category_name' value='' /></div>
			  <span class="help-inline"><?php echo form_error('category_name'); ?></span>	
	        </div>	
	        
		    <div class="row-fluid">
			  <div class="span1"></div>				
		      <div class="span2">Parent Category</div>
			  <div class="span4">
			    <select name='category_parent'>
                  <option value='0'>Root Category</option>
			      <?php foreach($categories as $cat) { ?>
			      <option value='<?php echo $cat['id']; ?>'><?php echo $cat['name']; ?></option>
			    <?php } ?>
			    </select>
			  </div>
	          <span class="help-inline"><?php echo form_error('category_id'); ?></span>			  
		    </div>	
				
		    <div class="row-fluid">
			  <div class="span3"></div>
			  <div class="span4"><input type='submit' name='add_category' value='Add' class='btn btn-tiny' /></div>
	        </div>
		  </form>
			
		  <?php echo form_open('admin/edit/items', array('class' => 'form-horizontal')); ?>
		    
			<div class="row-fluid">
			  <div class="span2"></div>
			  <div class="span5"><b>Delete A Category</b></div>
			</div>
			    
			<div class="row-fluid">
			  <div class="span1"></div>				
			  <div class="span2">Category</div>
			  <div class="span4">
				<select name='category_id'>
				  <?php foreach($categories as $cat) { ?>
				  <option value='<?php echo $cat['id']; ?>'><?php echo $cat['name']; ?></option>
				  <?php } ?>
				</select>
			  </div>
			  <span class="help-inline"><?php echo form_error('category_id'); ?></span>
			</div>

			
		    <div class="row-fluid">
			  <div class="span3"></div>
			  <div class="span7"><input type='submit' name='delete_category' value='Delete' class='btn btn-tiny' /></div>
	        </div>
	        
	      </form>
		</div>
