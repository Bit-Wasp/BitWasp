	    <div class="span9 mainContent" id="admin-dispute-form">
		  <h2><?php echo anchor('admin/logs', 'Back', 'class="btn"'); ?> Log Record: <?php echo $log['id']; ?></h2>
			
		  <div class='container-fluid'>	
		  
		  <div class="row-fluid">
			<div class='span3'>Warning Level</div>
			<div class='span7'><?php echo $log['info_level']; ?></div>
		  </div>
		  
		  <div class="row-fluid">
		    <div class='span3'>Time</div>
		    <div class='span7'><?php echo $log['time_f']; ?></div>
		  </div>
		  
		  <div class="row-fluid">
		    <div class='span3'>By</div>
		    <div class='span7'><?php echo $log['caller']; ?></div>
		  </div>
		  
		  <div class="row-fluid">
		    <div class='span3'>Message</div>
		    <div class='span7 well'><?php echo $log['message']; ?></div>
		  </div>
		  </div>
		</div>
