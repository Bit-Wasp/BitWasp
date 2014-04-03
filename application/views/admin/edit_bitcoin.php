        <div class="span9 mainContent" id="admin_edit_bitcoin">

		  <?php echo $nav; ?>

  		  <fieldset>
			
  		    <?php echo form_open('admin/edit/bitcoin', array('class' => 'form-horizontal')); ?>
 			  <div class="row-fluid">
				<div class="span5 offset2"><strong>Settings</strong></div>
			  </div>				

			  <div class="row-fluid">
				<div class="span3">Use A <?php echo $coin['name']; ?> Price Index?</div>
				<div class="span4">
				  <select name='price_index' autocomplete="off">
<?php foreach($config['price_index_config'] as $key => $index_config){ ?>
					<option value='<?php echo $key; ?>'<?php echo ($key == $config['price_index']) ? ' selected="selected"' : ''; ?>><?php echo $key; ?></option>
<?php } ?>
				  </select>
				</div>
			  </div>
			  <span class="help-inline offset2"><?php echo form_error('price_index'); ?></span>
			  
			  <div class="row-fluid">
				<div class="span3">Electrum MPK</div>
				<div class="span4"><input type='text' name='electrum_mpk' value='<?php echo $config['electrum_mpk']; ?>' /></div>
			  </div>
			  <span class="help-inline offset2"><?php echo form_error('electrum_mpk'); ?></span>

			  <div class="row-fluid">
				<div class="span3">Address Index</div>
				<div class="span4"><input type='text' name='electrum_iteration' value='<?php echo $config['electrum_iteration']; ?>' /></div>
				<span class='span5'>Only change this if you know what you're doing!</span>
			  </div>
			  	 
		      <div class="form-actions">
		        <input type='submit' name='submit_edit_bitcoin' value='Update' class='btn btn-primary' />
                <?php echo anchor('account','Cancel', array('class'=>'returnLink btn'));?>
		      </div>
		    </form>
		  </fieldset>
		</div>
