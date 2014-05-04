        <div class="span9 mainContent" id="admin-items-panel">
		  
		  <?php echo $nav; ?>

		<?php if(isset($returnMessage)) echo '<div class="alert">' . $returnMessage . '</div>'; ?>
			
		  <div class="container-fluid">

			<div class="row-fluid">
			  <span class="span3">Item Count</span>
			  <span class="span7"><?php echo $item_count; ?></span>
			</div>  

			<div class="row-fluid">
			  <span class="span3">Fees Configuration</span>
			  <span class="span7"><?php echo anchor('admin/items/fees','Manage'); ?></span>
			</div>
		  </div>
		</div>
