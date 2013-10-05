        <div class="span9 mainContent" id="admin_edit_bitcoin">

		  <?php echo $nav; ?>

  		  <fieldset>
		    <?php echo form_open('admin/edit/bitcoin', array('class' => 'form-horizontal')); ?>
			  <div class="row-fluid">
				<div class="span2"></div>
				<div class="span5"><b>Fetch Exchange Rates?</b></div>
			  </div>				
			  
			  <div class="row-fluid">
				  <div class="span1"></div>
				  <div class="span2">Use A Bitcoin Index?</div>
				  <div class="span4">
					<select name='price_index' autocomplete="off">
<?php foreach($config['price_index_config'] as $key => $index_config){ ?>
					  <option value='<?php echo $key; ?>'<?php echo ($key == $config['price_index']) ? ' selected="selected"' : ''; ?>><?php echo $key; ?></option>
<?php } ?>
					</select>
				  </div>
			  </div>

		      <div class="row-fluid">
			    <div class="span3"></div>
			    <div class="span7"><input type="submit" name="update_price_index" value="Update" class="btn" /></div>
	          </div>
	          			  
			</form>
			
			<?php echo form_open('admin/edit/bitcoin', array('class' => 'form-horizontal')); ?>  	
			  <div class="row-fluid">
				<div class="span2"></div>
				<div class="span5"><b>Transfer Between Accounts</b></div>
			  </div>				
			  
		      <div class="row-fluid">
				<div class="span1"></div>
		        <div class="span2">From</div>
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
				<div class="span1"></div>
		        <div class="span2">To</div>
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
				<div class="span1"></div>
		        <div class="span2">Amount</div>
				<div class="span5"><input type="text" name="amount" value="<?php echo set_value('amount'); ?>" /></div>
				<span class='help-inline'><?php if(isset($transfer_bitcoins_error)) { 
				    echo $transfer_bitcoins_error;
				} else {
					echo form_error('amount');
				}?></span>
	          </div>		

		      <div class="row-fluid">
			    <div class="span3"></div>
			    <div class="span7"><input type="submit" name="admin_transfer_bitcoins" value="Send" class="btn" /></div>
	          </div>
	          
            <!--  <div class="form-actions">
		        <input type="submit" value="Update" class="btn btn-primary" />
                <?php echo anchor('account','Cancel', array('class'=>'returnLink btn'));?>
		      </div>-->

		    </fieldset>
		  </form>
		</div>
