        <div class="span9 mainContent" id="read-message">
          <h2>View Message</h2>
          
          <div class="container-fluid">
			  
            <div class="row-fluid">
              <div class="span2"><strong>From</strong></div>
              <div class="span7"><?php echo anchor('user/'.$message['from']['user_hash'], $message['from']['user_name']);?></div>
            </div>
            
            <div class="row-fluid">
              <div class="span2"><strong>Subject</strong></div>
              <div class="span7"><?php echo $message['subject'];?></div>
            </div>
            
            <div class="row-fluid">
              <div class="span2"><strong>Time</strong></div>
              <div class="span7"><?php echo $message['time_f']; ?></div>
            </div>
            
<?php if($message['remove_on_read']) { ?>
            <div class="row-fluid">
              <div class="span4">This message will now be deleted</div>
            </div>
<?php } ?>

            <div class="row-fluid">
              <div class="span2"></div>
              <div class="span9"><br />
<?php 
if($message['encrypted']) { echo '<pre>'; $message['message'] = str_replace('<br />','',$message['message']); }
echo $message['message'];
if($message['encrypted']) echo '</pre>'; 
?>
              </div>
            </div>
          </div>

          <div class="form-actions">
<?php 
echo anchor('message/send/'.$message['hash'], "Reply", 'class="btn btn-primary"').' ';
echo anchor('message/delete/'.$message['hash'], 'Delete Message', 'class="btn btn-danger"').' ';
echo anchor('inbox','Inbox', array('class'=>'returnLink btn'));
?>
          </div>
        </div>
