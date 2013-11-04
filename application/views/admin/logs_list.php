	    <div class="span9 mainContent" id="admin-logs-list">
		  <?php echo $nav; ?>
			  
		  <?php if(isset($returnMessage)) { ?>
		  <div class='alert<?php if($success == TRUE) echo ' alert-success'; ?>'><?php echo $returnMessage; ?></div>
		  <?php } ?>
			  
<?php if($logs !== FALSE) { ?>
		  <table class='table' cellspacing='0'>
			<thead>
			  <tr>
				<th>Time</th>
			    <th>Level</th> 
			    <th>Title</th>
			    <th>Called By</th>
			    <th></th>
			  </tr>
			</thead>
			<tbody>
<?php foreach($logs as $log) { ?>
			  <tr>
				<td><?php echo $log['time_f']; ?></td>
				<td><?php echo $log['info_level']; ?></td>
				<td><?php echo anchor('admin/logs/'.$log['hash'], $log['title']); ?></td>
				<td><?php echo $log['caller']; ?></td>
				<td></td>
			  </tr>
<?php } ?>
			</tbody>
		  </table>
<?php } else {?>
There are no logs at this time.
<?php } ?>
		</div>
