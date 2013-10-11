        <div class="span9 mainContent" id="admin_edit_items">

		  <?php echo $nav; ?>

		  <?php echo form_open('admin/edit/items', array('class' => 'form-horizontal')); ?>
				
			<p>You can alter the frequency at which certain actions are performed here. To disable something, set the interval to '0'.</p>
				
<?php foreach($intervals as $interval) { 
	if($interval['index'] == 'price_index' && $config['price_index'] == 'Disabled')
		continue;
	
	if($interval['index'] == 'price_index') {
		$disp = round($interval['interval']*60);
		$measure = "minutes";
	} else if($interval['index'] == 'user_inactivity') {
		$disp = $interval['interval'];
		$measure = "hours";
	} else if($interval['index'] == 'transaction_history' || $interval['index'] == 'message_history') {
		$disp = ($interval['interval']/24);
		$measure = "days";
	} ?>
			<div class="row-fluid">
			  <div class="span3"><?php echo $interval['name']; ?></div>
			  <div class="span5"><input type="text" class="span2" name='<?php echo $interval['index']; ?>' value='<?php echo $disp; ?>' /> <?php echo $measure; ?></div>
			</div>
			<div class="row-fluid">
			  <div class="span7 offset1"><i><?php echo $interval['description']; ?></i></div>
			</div>
			<br />
			
<?php } ?>	        
	        
		  </form>
		</div>
