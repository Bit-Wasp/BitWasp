          <div class="mainContent span9">
            <h2>Welcome <?php echo $user['user_name']; ?>!</h2>
				 <br />
			  <span class="span6">
			  <?php echo $returnMessage; 
			  
			  if(isset($entry_payment['received']) && $entry_payment['received'] > 0) { ?>
			  So far you have paid BTC <?php echo $entry_payment['received']; ?> towards this fee. 6 confirmations are required before this payment is accepted.<br />
			  <?php } ?>
			  <br />
			  <?php echo anchor('register/payment', 'Refresh', 'class="btn btn-success"'); ?>
			  </span>
			<br />
          </div>
