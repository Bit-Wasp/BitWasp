        <div class="span9 mainContent" id="admin_edit_bitcoin">

		  <?php echo $nav; ?>

  		  <fieldset>
			  
  		    <?php echo form_open('admin/edit/bitcoin', array('class' => 'form-horizontal')); ?>
			 
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
		      
		      <div class="form-actions">
		        <input type='submit' name='submit_edit_bitcoin' value='Update' class='btn btn-primary' />
                <?php echo anchor('account','Cancel', array('class'=>'returnLink btn'));?>
		      </div>
		    </form>
			  			
			<?php echo form_open('admin/edit/bitcoin', array('class' => 'form-horizontal')); ?>  	
			  <div class="row-fluid">
				<div class="span5 offset2"><strong>Transfer Between Accounts</strong></div>
			  </div>				
			  
		      <div class="row-fluid">
		        <div class="span2 offset1">From</div>
			    <div class="span4">
				  <select name='from'>
					<?php foreach($accounts as $acc => $bal) { 
					if($acc !== '') { ?>
					<option value='<?php echo $acc; ?>'><?php echo $acc; ?> (<?php echo $bal; ?>)</option>
				    <?php } } ?>
				  </select>
			    </div>
			    <span class="help-inline"><?php echo form_error('from'); ?></span>
	          </div>				  
				
		      <div class="row-fluid">
		        <div class="span2 offset1">To</div>
			    <div class="span4">
				  <select name='to'>
					<?php foreach($accounts as $acc => $bal) { 
					if($acc !== '') { ?>
					<option value='<?php echo $acc; ?>'><?php echo $acc; ?></option>
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
	          
		    </fieldset>
		  </form>
		</div>
