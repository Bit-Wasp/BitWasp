	    <div class="span9 mainContent" id="admin-disputes-list">
		  <h2><?php echo anchor('admin', 'Back', 'class="btn"'); ?> Disputes</h2>
			
		  <?php if(isset($returnMessage)) { ?>
		  <div class='alert<?php if($success == TRUE) echo ' alert-success'; ?>'><?php echo $returnMessage; ?></div>
		  <?php } ?>
			  
<?php if($disputes !== FALSE) { ?>
		  <table class='table' cellspacing='0'>
			<thead>
			  <tr>
				<th class='span1'>Order</th>
			    <th class='span2'>Disputing User</th>
			    <th class='span5'>Issue</th>
			    <th class='span2'>Other User</th>
			    <th class='span2'>Last Update</th>
			  </tr>
			</thead>
			<tbody>
<?php foreach($disputes as $dispute) { ?>
			  <tr>
				<td>#<?php echo $dispute['order_id']; ?></td>
				<td><?php echo anchor('user/'.$dispute['disputing_user']['user_hash'], $dispute['disputing_user']['user_name']); ?></td>
				<td><?php echo anchor('admin/dispute/'.$dispute['order_id'], substr($dispute['dispute_message'], 0, 100)); ?></td>
				<td><?php echo anchor('user/'.$dispute['other_user']['user_hash'], $dispute['other_user']['user_name']); ?></td>
				<td><?php echo $dispute['last_update_f']; ?></td>
			  </tr>
<?php } ?>
			</tbody>
		  </table>
<?php } else {?>
There are no disputes at this time.
<?php } ?>
		</div>
