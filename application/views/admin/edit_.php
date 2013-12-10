        <div class="span9 mainContent" id="admin_edit_">

		  <?php echo $nav; ?>
		  
		  <?php echo form_open('admin/edit', array('class' => 'form-horizontal')); ?>
  		    <fieldset>
			
			  <div class="row-fluid">
				<div class="span3">Site Status</div>
				<div class="span8"><?php echo ($config['maintenance_mode'] == TRUE) ? 'maintenance mode' : 'online'; ?> - <?php echo anchor('admin/maintenance', 'Configure'); ?></div>
			  </div>
			
			  <div class="row-fluid">
			    <div class="span3">Site Title</div>
			    <div class="span8"><input type="text" name="site_title" value="<?php echo $config['site_title']; ?>" /></div>
			    <span class="help-inline"><?php echo form_error('site_title'); ?></span>			    
			  </div>
			  <br />

			  <div class="row-fluid">
			    <div class="span3">Site Description</div>
			    <div class="span8"><input type="text" name="site_description" class='span9' value="<?php echo $config['site_description']; ?>" /></div>
			    <span class="help-inline"><?php echo form_error('site_description'); ?></span>			    
			  </div>
			  <br />
			  
			  <div class="row-fluid">
			    <div class="span3">Terms Of Service</div>
			    <div class="span8"><?php echo anchor('admin/tos', 'Configure'); ?></div>
			  <br />
			  
			  <div class="row-fluid">
				<div class="span3">Allow Guests to Browse?</div>
				<div class="span5">
                  <label class="radio inline"><input type='radio' name='allow_guests' value='0' <?php echo ($config['allow_guests'] == '0') ? 'checked' : ''; ?> /> Disabled</label>
                  <label class="radio inline"><input type='radio' name='allow_guests' value='1' <?php echo ($config['allow_guests'] == '1') ? 'checked' : ''; ?> /> Enabled</label>
				</div>
			  </div>
			  <br />
			  
    	      <div class="row-fluid">
				<div class="span3">OpenSSL Key Size</div>
				<div class="span5">
				  <select name='openssl_keysize'>
					<?php
					// 2048,4096. Should probably hard code that..
					$seed = 1024;
					for($i = 1; $i < 3; $i++){ 
						$size = 2*$seed; $seed *=2; $selected = false;
						if($size == $config['openssl_keysize']) 
							$selected = true;
					?>
					<option value='<?php echo $size; ?>' <?php if($selected == true) { ?>selected="selected"<?php } ?>><?php echo $size; ?></option>
					<?php }	?>
				  </select>
				</div>
     	        <span class="help-inline"><?php echo form_error('openssl_keysize'); ?></span>			    
    	      </div>
    	      
			  <div class="row-fluid">
				<div class="span3">Global Proxy</div>
				<div class="span7">
				  <select name="global_proxy_type" class="span3" autocomplete="off">
					<option value='HTTP' <?php echo ($config['global_proxy_type'] == 'HTTP') ? 'selected="selected"' : NULL ; ?>>HTTP</option>
					<option value='SOCKS5' <?php echo ($config['global_proxy_type'] == 'SOCKS5') ? 'selected="selected"' : NULL ; ?>>SOCKS5</option>
				  </select>
				  <input type='text' name='global_proxy_url' value='<?php echo $config['global_proxy_url']; ?>' />
				</div>
			  </div>
			  <div class="row-fluid">
				<div class="span7 offset3">Disabled <input type='checkbox' name='global_proxy_disabled' value='1' <?php echo ($config['global_proxy_type'] == 'Disabled') ? 'checked' : NULL; ?> /></div>
  			  </div>
  			  <div class="row-fluid">
				<span class="help-inline offset3"><?php if(isset($proxy_error)) echo $proxy_error; ?> <?php echo form_error('global_proxy_type'); ?> <?php echo form_error('global_proxy_url'); ?></span>			    
  			  </div>
			  
              <div class="form-actions">
		        <input type='submit' value='Update' class='btn btn-primary' />
                <?php echo anchor('account','Cancel', array('class'=>'returnLink btn'));?>
		      </div>
		      
			</fieldset>
		  </form>
		</div>
