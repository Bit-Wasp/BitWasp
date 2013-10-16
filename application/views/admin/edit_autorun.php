        <div class="span9 mainContent" id="admin_edit_items">

		  <?php echo $nav; ?>

		  <?php echo form_open('admin/edit/autorun', array('class' => 'form-horizontal')); ?>
				
			<p>Jobs can be added to the Autorun script by placing them in ./application/libraries/Autorun. From there, you can choose to change the frequency or disable it altogether. To disable a job, check the box beside it and click submit.</p>
			<?php echo validation_errors(); ?>	
<?php foreach($jobs as $index => $job) { 
	if($index == 'price_index' && $config['price_index'] == 'Disabled')
		continue;
?>
			<div class="row-fluid">
			  <div class="span3"><?php echo $job['name']; ?></div>
			  <div class="span2"><input type="text" class="span4" name='jobs[<?php echo $index; ?>]' value='<?php echo $job['interval']; ?>' /> <?php echo $job['interval_type']; ?></div>
			  <div class="span3"><input type='checkbox' name='disabled_jobs[<?php echo $index; ?>]' value='1' <?php if($job['interval'] == '0') echo 'checked '; ?>/> Disabled</div>
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
