
          <div class="nav-collapse collapse">
            <ul class="nav pull-right">
              <li><?php echo anchor('', 'Home', 'title="Home"');?></li>
              <li><?php echo anchor('items', 'Items', 'title="Items"'); ?></li>
<?php $inbox_str = 'Inbox'; if($count_unread_messages > 0) $inbox_str .= " ($count_unread_messages)"; ?>
              <li><?php echo anchor('inbox', $inbox_str, 'title="Inbox"'); ?></li>
              <li><?php echo anchor('account', 'Account', 'title="Account"');?></li>
              <li><?php echo anchor('logout', 'Logout', 'title="Logout"');?></li>
            </ul>
          </div>
