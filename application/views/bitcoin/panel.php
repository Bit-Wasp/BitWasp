
          <div class="span9 mainContent">
            <h2>Bitcoin</h2>	
            <?php if(isset($returnMessage)) { ?>
            <div class='alert alert-success'><?php echo $returnMessage; ?></div><?php } ?>
            
            <?php echo form_open('bitcoin/panel'); ?>
			  <div class="row-fluid">
			    <div class="span2">Top Up Address</div>
			    <div class="span5"><?php echo $topup_address; ?></div>
			    <div class="span2"><input type="submit" name="generate_new" value="Replace" class="btn btn-small" /></div>
              </form>
			</div> 
			
			<?php echo form_open('bitcoin/panel'); ?>
			  <div class="row-fluid">
			    <div class="span2">Cashout Address</div>
			    <div class="span5"><input type="text" name="cashout_address" class="span12" value="<?php echo $cashout_address; ?>" /></div>
			    <div class="span1"><input type="submit" name="update_cashout" value="Update" class="btn btn-small" /></div>
			    <?php if($cashout_address !== '') { ?>
			    <div class="span1"><?php echo anchor('cashout','Cashout','class="btn btn-small"'); ?></div>
			    <?php } ?>
                <span class="help-inline"><?php echo form_error('cashout_address'); ?></span>
              </form>
			</div>
			
<?php if($unverified_balance > 0) { ?>
			<div class="row-fluid">
			  <div class="span2">Unverified Balance</div>
			  <div class="span7">BTC <?php echo $unverified_balance; ?></div>
			</div>   
			<br/ >
<?php } ?>
<?php
if(is_array($transactions)) { ?>
            
			<table class='table table-condensed table-hover'>
			  <thead>
			    <tr>
				  <th>Value</th>
				  <th>Conf</th>
				  <th>Receiving Address</th>
				  <th>Transaction ID</th>
				  <th>Time</th>
				  <th></th>
			    </tr>
			  </thead>
			  <tbody>  
<?php foreach($transactions as $transaction) { ?>

				<tr class='<?php echo ($transaction['category'] == 'receive') ? 'success' : 'error'; ?>'>
				  <td><?php echo $transaction['value_f']; ?></td>
				  <td><?php echo ($transaction['address'] == '[payment]' || $transaction['confirmations'] == '>50' ) ? '<i class="icon-check icon-black"></i>' : $transaction['confirmations']; ?></td>
				  <td><?php echo $transaction['address']; ?></td>
				  <td><?php echo $transaction['txn_id_f']; ?></td>
				  <td><?php echo $transaction['time_f']; ?></td>
				  <td><?php echo ($transaction['credited'] == '1') ? 'applied' : ''; ?></td>
				</tr>				
<?php } ?>

			  </tbody>
			</table>

<?php } ?>
          </div>
