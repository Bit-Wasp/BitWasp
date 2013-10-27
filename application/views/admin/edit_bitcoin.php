        <div class="span9 mainContent" id="admin_edit_bitcoin">

		  <?php echo $nav; ?>

  		  <fieldset>
			
			<?php if(count($accounts) > 0) { ?>
			<?php echo form_open('admin/edit/bitcoin', array('class' => 'form-horizontal')); ?>  	
			  <div class="row-fluid">
				<div class="span5 offset2"><strong>Transfer Between Accounts</strong></div>
			  </div>				
			  
		      <div class="row-fluid">
		        <div class="span2 offset1">From</div>
			    <div class="span4">
				  <select name="from" autocomplete="off">
					<option value=""></option>
<?php foreach($accounts as $acc => $bal) { 
if($acc !== '') { ?>					<option value="<?php echo $acc; ?>"><?php echo $acc; ?> (<?php echo $bal; ?>)</option>
<?php } } ?>
				  </select>
			    </div>
				<span class="help-inline"><?php echo form_error('from'); ?></span>				
	          </div>				  

		      <div class="row-fluid">
		        <div class="span2 offset1">To</div>
			    <div class="span4">
				  <select name='to' autocomplete="off">
					<option value=""></option>
<?php foreach($accounts as $acc => $bal) { 
if($acc !== '') { ?>					<option value='<?php echo $acc; ?>'><?php echo $acc; ?></option>
<?php } } ?>
				  </select>
			    </div>
   			    <span class="help-inline"><?php echo form_error('from'); ?></span>
	          </div>				  

		      <div class="row-fluid">
		        <div class="span2 offset1">Amount</div>
				<div class="span4"><input type="text" name="amount" value="<?php echo set_value('amount'); ?>" /></div>
				<span class='help-inline'><?php if(isset($transfer_bitcoins_error)) { 
				    echo $transfer_bitcoins_error;
				} else {
					echo form_error('amount');
				}?></span>
	          </div>		

		      <div class="row-fluid">
			    <div class="span4 offset3"><input type="submit" name="admin_transfer_bitcoins" value="Send" class="btn" /></div>
	          </div>	          
		    </form>

			<?php echo form_open('admin/edit/bitcoin', array('class' => 'form-horizontal')); ?>  	
			  <div class="row-fluid">
				<div class="span5 offset2"><strong>Topup Wallet Balance</strong></div>
			  </div>				
<?php echo validation_errors(); ?>
		      <div class="row-fluid">
		        <div class="span2 offset1">Account</div>
			    <div class="span4">
				  <select name="topup_account" autocomplete="off">
					<option value=""></option>
<?php foreach($accounts as $acc => $bal) { 
if($acc !== '') { ?>					<option value="<?php echo $acc; ?>"><?php echo $acc; ?> (<?php echo $bal; ?>)</option>
				  <?php } } ?></select>
			    </div>
				<span class="help-inline"><?php echo form_error('topup_account'); ?></span>
	          </div>				  
				
		      <div class="row-fluid">
		        <div class="span2 offset1">Private WIF key</div>
				<div class="span7"><input type="text" name="wif" value="" /></div>
				<span class='help-inline'><?php echo form_error('wif'); ?></span>
	          </div>		
			  <?php if(isset($import_wallet_error)) { ?>
			  <span class="help-inline offset3"><?php echo $import_wallet_error; ?></span>
			  <?php } ?>
			  
		      <div class="row-fluid">
			    <div class="span4 offset3"><input type="submit" name="submit_wallet_topup" value="Topup" class="btn" /></div>
	          </div>
		    </form>

		    <?php } ?>
			  
  		    <?php echo form_open('admin/edit/bitcoin', array('class' => 'form-horizontal')); ?>
 			  <div class="row-fluid">
				<div class="span5 offset2"><strong>Settings</strong></div>
			  </div>				

		      <div class="row-fluid">
				<div class="span3">Delete Transactions After</div>
				<div class="span7">
				  <label class="inline"><input type='text' class='span2' name='delete_transactions_after' value='<?php echo $config['delete_transactions_after']; ?>' /> days</label>
				  <label class="inline"><input type='checkbox' name='delete_transactions_after_disabled' <?php echo ($config['delete_transactions_after'] == '0') ? 'checked ' : NULL; ?> value='1' /> Disabled</label>
				</div>
			  </div>
			  <span class="help-inline offset2"><?php echo form_error('delete_transactions_after'); ?></span>

			  <div class="row-fluid">
				<div class="span3">Use A Bitcoin Index?</div>
				<div class="span4">
				  <select name='price_index' autocomplete="off">
<?php foreach($config['price_index_config'] as $key => $index_config){ ?>
					<option value='<?php echo $key; ?>'<?php echo ($key == $config['price_index']) ? ' selected="selected"' : ''; ?>><?php echo $key; ?></option>
<?php } ?>
				  </select>
				</div>
			  </div>
			  <span class="help-inline offset2"><?php echo form_error('price_index'); ?></span>
			  <?php 
			  foreach($accounts as $account => $balance) { 
			  if($account !== '' && $account !== 'topup') { 
				$var = 'max_'.$account.'_balance'; ?>  <div class="row-fluid">
				<span class="span3">Backup <?php echo ucfirst($account); ?> After</span>
				<span class="span4">
				  <div class="input-prepend">
				    <span class="add-on"><i>BTC</i></span>
					<input type='text' class='span10' name='account[<?php echo $account; ?>]' value='<?php echo (isset($config[$var])) ? $config[$var] : '0.00000000'; ?>' /> 
				  </div>
				</span>
			  </div>
			  
			  <div class="row-fluid">
				<span class="span3"></span>
				<span class="span7"><input type='checkbox' name='backup_disabled[<?php echo $account; ?>]' value='1' <?php if(!isset($config[$var]) || $config[$var] == '0.00000000') echo 'checked '; ?>/> Disabled</span>
			  </div>
			  <span class="help-inline offset2"><?php echo form_error('delete_transactions_after'); ?></span>
			  
			<?php } } ?>

		      <div class="form-actions">
		        <input type='submit' name='submit_edit_bitcoin' value='Update' class='btn btn-primary' />
                <?php echo anchor('account','Cancel', array('class'=>'returnLink btn'));?>
		      </div>
		    </form>
		  </fieldset>
		</div>
