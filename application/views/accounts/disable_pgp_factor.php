          <div class="mainContent span7">
			<div class='row-fluid'>
				<h2>Disable Two Factor Authentication</h2>
				  <?php echo form_open('account/disable_2fa', array('class' => 'form-horizontal')); ?>
				  <fieldset>
					<div class="alert">
	<?php if(isset($returnMessage)) { echo $returnMessage; } else { ?>
Decrypt the following PGP message to remove two factor challenge on login:
	<?php } ?>
					</div>
					<div class="control-group">
					<pre class="well span10"><?php echo $challenge;?></pre>
					</div>
					
					<div class="control-group">
					  <label class="control-label" for="answer">Token</label>
					  <div class="controls">
						<input type="text" name='answer' size='12'/>
						<span class="help-inline"><?php echo form_error('answer'); ?></span>
					  </div>
					</div>

					<div class="form-actions">
						<input type='submit' class='btn btn-primary' name='disable_pgp' value='Continue' />
						<?php echo anchor('account/two_factor', 'Cancel', 'title="Cancel" class="btn"');?>
					</div>
				  <fieldset>
				</form>
			</div>
		</div>
