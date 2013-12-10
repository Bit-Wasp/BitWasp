	    <div class="span9 mainContent" id="registration-tokens">
		  <h2><?php echo anchor('admin/users', 'Back', 'class="btn"'); ?> Registration Invites</h2>
			
		  <?php if(isset($returnMessage)) { ?>
		  <div class='alert<?php if($success == TRUE) echo ' alert-success'; ?>'><?php echo $returnMessage; ?></div>
		  <?php } ?>
		  <div class="container-fluid">
			  
			<?php echo form_open("admin/tokens", array("class" => "form-horizontal")); ?>  
			
			  <div class="row-fluid">
				<div class="offset2"><b>Create a Registration Invite</b></div>
			  </div>
			
			  <div class="row-fluid">
			    <div class="span2">User Role</div>
			    <div class="span4">
				  <select name="user_role">
				    <option value=""></option>
				    <option value="1">Buyer</option>
				    <option value="2">Vendor</option>
				    <option value="3">Administrator</option>
				  </select>
			    </div>
			  </div>
			  <span class="help-inline"><?php echo form_error('user_role'); ?></span>
			
			  <div class="row-fluid">
			    <span class="span2">Comment (optional):</span>
			    <div class="span7"><input type="text" name="token_comment" value="" class="span15" /></div>
			  </div>
			  
			  <div class="row-fluid">
				<div class="span5 offset2">This note will not be shown to the user.</div>
			  </div>
			
			  <div class="row-fluid">
			    <div class="span2">Registration Fee</div>
			    <div class="span4">
				  <div class="input-prepend">
				    <span class="add-on"><i>BTC</i></span>
					<input type='text' class='span10' name='entry_payment' value='default' /> 
				  </div>
			    </div>
			  </div>
			  <span class="help-inline"><?php echo form_error('entry_payment'); ?></span>
						
			  <div class="row-fluid">
			    <div class="span2"></div>
			    <span class="span4"><input type="submit" name="create_token" value="Create" class="btn" /></span>			  
			  </div>
		    </div>
		  </form>
		  
<?php if($tokens !== FALSE) { ?>
		  <table class='table' cellspacing='0'>
			<thead>
			  <tr>
			    <th>Link</th>
			    <th>Delete</th>
			    <th>User Role</th>
			    <th>Registration Fee</th>
			    <th>Comment</th>
			  </tr>
			</thead>
			<tbody>
<?php foreach($tokens as $token) { ?>
			  <tr>
				<td class='span2'><?php echo anchor('register/'.$token['token_content'], 'Right click to copy', 'class="btn btn-mini"'); ?></td>
				<td class='span2'><?php echo anchor('admin/tokens/delete/'.$token['token_content'], 'Delete', 'class="btn btn-danger btn-mini"'); ?></td>
				<td class='span1'><?php echo $token['role']; ?></td>
				<td class='span2'>BTC <?php echo $token['entry_payment']; ?></td>
				<td class='span6'><?php echo $token['comment']; ?></td>
			  </tr>
<?php } ?>
			</tbody>
		  </table>
<?php } ?>
		</div>
