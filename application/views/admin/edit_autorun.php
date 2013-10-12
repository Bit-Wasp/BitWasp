        <div class="span9 mainContent" id="admin_edit_items">

		  <?php echo $nav; ?>

		  <?php echo form_open('admin/edit/autorun', array('class' => 'form-horizontal')); ?>
				
			<p>You can alter the frequency at which certain actions are performed here. To disable something, set the interval to '0'.</p>
				
<?php foreach($jobs as $index => $job) { 
	if($index == 'price_index' && $config['price_index'] == 'Disabled')
		continue;
?>
			<div class="row-fluid">
			  <div class="span3"><?php echo $job['name']; ?></div>
			  <div class="span5"><input type="text" class="span2" name='jobs[<?php echo $index; ?>]' value='<?php echo $job['interval']; ?>' /> <?php echo $job['interval_type']; ?></div>
			</div>
			<div class="row-fluid">
			  <div class="span7 offset1"><i><?php echo $job['description']; ?></i></div>
			</div>
			<br />
			
<?php } ?>	        
		    <div class="form-actions">
			  <input type='submit' value='Update' class='btn btn-primary' />
			  <?php echo anchor('admin/edit/items','Cancel', array('class'=>'returnLink btn'));?>
		    </div>		  	        
	        
		  </form>
		</div>
