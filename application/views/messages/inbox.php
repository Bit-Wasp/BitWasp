        <div class="span9 mainContent" id="inbox">
		<h2>Inbox</h2>		
		
		<?php if(is_array($messages)){ ?>
          <table class="table">
            <thead>
              <tr>
                <th>From</th>
                <th>Subject</th>
                <th>Time</th>
                <th></th>
                <th></th>
              </tr>
            </thead>
	        <?php foreach ($messages as $message){ ?>
            <tr<?php if(!$message['viewed']){?> class="info"<?}?>>
		          <td><?php echo anchor('user/'.$message['from']['user_hash'], $message['from']['user_name']);?></td>
		          <td><?php if(!$message['viewed']) echo '<strong>'; ?>
					  <?php if(strlen($message['subject']) > 35) $message['subject'] = substr($message['subject'],0,35).'...'; ?>
					  <?php echo anchor('message/'.$message['hash'], $message['subject']);?> 
					  <?php if(!$message['viewed']) echo '</strong>'; ?></td>
		          <td><?php echo $message['time_f'];?></td>
				  <td><?php if($message['encrypted'] == '1') {?>[encrypted]<?php } ?> 
				  <?php if($message['remove_on_read'] == '1') {?>[auto-delete]<?php } ?></td>
		          <td><?php echo anchor('message/'.$message['hash'], 'View', 'class="btn btn-mini"');?>
                  <?php echo ($message['viewed'] == '1') ? anchor('message/send/'.$message['hash'], 'Reply', 'class="btn btn-mini"') : NULL;?>
				  <?php echo anchor('message/delete/'.$message['hash'], 'Delete', 'class="btn btn-danger btn-mini"');?>				  </td>
		    </tr>
	        <?php } ?>
		  </table>
		<?php } else { ?>
		<p>No messages in your inbox.</p>
		<?php } ?>
		
		 <div class="form-actions">
	     <?php echo anchor('message/send','Compose message', 'class="btn btn-primary"');?>
         <!-- <?php echo anchor('messages/#','Mark all read', 'class="btn"');?> -->
	     <?php if(is_array($messages)) { echo anchor('message/delete/all', 'Delete All!', 'class="btn btn-danger"'); }?>
         </div>
		</div>
