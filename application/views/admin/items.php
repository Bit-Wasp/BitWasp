        <div class="span9 mainContent" id="admin-items-panel">
		  
		  <?php echo $nav; ?>
			
		  <div class="container-fluid">

			<div class="row-fluid">
			  <span class="span3">Item Count</span>
			  <span class="span7"><?php echo $item_count; ?></span>
			</div>  

			<div class="row-fluid">
			  <span class="span3">Auto-Finalize Threshold</span>
			  <span class="span7"><?php echo ($config['auto_finalize_threshold'] == '0') ? 'Disabled' : $config['auto_finalize_threshold']." days"; ?></span>
			</div>
			
		  </div>
		</div>
