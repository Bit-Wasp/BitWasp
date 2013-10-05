
          <div class="span9 mainContent">
            <h2>Cashout Funds</h2>	
            <?php if(isset($returnMessage)) echo '<div class="alert">'.$returnMessage.'</div>'; ?>
            <p>Your current balance is BTC <?php echo $current_balance; ?>. Enter the amount you wish to cash out below:</p>

			<br />

            <?php echo form_open('cashout', array('class' => 'form-horizontal')); ?>
			  <fieldset>
				  
				<div class="row-fluid">
				  <div class="span2">Bitcoin Address</div>
				  <div class="span7"><?php echo $cashout_address; ?></div>
				</div> 
			
				<div class="row-fluid">
			      <div class="span2">Amount</div>
			      <div class="span7"><input type='text' name='amount' value="<?php echo set_value('amount'); ?>" /></div>
                  <span class="help-inline"><?php echo form_error('amount'); ?></span>
                </div>

              <div class="form-actions">
                <button type='submit' class="btn btn-primary">Confirm</button>
                <?php echo anchor('bitcoin', 'Cancel', 'title="Cancel" class="btn"');?>
              </div>
              </fieldset>
            </form>
          </div>
