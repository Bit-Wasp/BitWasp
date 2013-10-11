        <div class="span9 mainContent" id="admin_edit_bitcoin">
		  <h2>Fix Orphans</h2>
		  
		  <p>Deleting this category will orphan <?php echo $list; ?>, please select another category to contain them.</p>
		  
		  <?php echo form_open('admin/category/orphans/'.$category['hash'], array('class' => 'form-horizontal') ); ?>
		  
		  <div class="row-fluid">
			<div class="span1"></div>
			<div class="span2">New Category</div>
			<div class="span4">
			  <select name='category_id'>
			    <?php // Filter children of the category to be deleted, and the category itself, from the displayed list.
			    $remove = $children;
			    $remove[] = $category;
			    foreach($categories as $cat) { 
					$banned = false;
					foreach($remove as $tmp){
						if($tmp['id'] == $cat['id'])
							$banned = true;
					}
					if($banned == false) {?>
			    <option value='<?php echo $cat['id']; ?>'><?php echo $cat['name']; ?></option>
			    <?php } } ?>
			  </select>
			</div>
			<span class='help-inline'><?php echo form_error('category_id'); ?></span>
		  </div>
		  
		  <div class="form-actions">
			<input type='submit' value='Update' class='btn btn-primary' />
			<?php echo anchor('admin/edit/items','Cancel', array('class'=>'returnLink btn'));?>
		  </div>		  
		</div>
