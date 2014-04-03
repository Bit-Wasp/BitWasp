		<div class="mainContent span9">
			<h2>Welcome <?php echo $user['user_name']; ?>!</h2>
			<?php if(isset($returnMessage)) echo $returnMessage; ?>
		
			Please pay an entry fee to <?php echo $entry_payment['bitcoin_address']; ?>.
			
			So far you have paid <?php echo $coin['symbol']; ?> <?php echo $paid; ?> of the <?php echo $coin['symbol']; ?> <?php echo $entry_payment['amount']; ?> fee. Once the full amount has one confirmation your account will be activated.
			  
        </div>
