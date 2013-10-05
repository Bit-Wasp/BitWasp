	    <div class="span9 mainContent" id="registration-tokens">
		  <h2><?php echo anchor('admin/users', 'Back', 'class="btn"'); ?> Registration Invites</h2>
			
		  <?php if(isset($returnMessage)) { ?>
		  <div class='alert<?php if($success == TRUE) echo ' alert-success'; ?>'><?php echo $returnMessage; ?></div>
		  <?php } ?>
		  <div class="container-fluid">
			  
			<?php echo form_open("admin/tokens", array("class" => "form-horizontal")); ?>  
			
			  <div class="row-fluid">
				<div class="span2"></div>
				<div class="span3"><b>Create a Registration Invite</b></div>
			  </div>
			
			  <div class="row-fluid">
			    <div class="span1"></div>
			    <div class="span2">User Role</div>
			    <div class="span4">
				  <select name="user_role">
				    <option value=""></option>
				    <option value="1">Administrator</option>
				    <option value="2">Buyer</option>
				    <option value="3">Vendor</option>
				  </select>
			    </div>
			    <span class="help-inline"><?php echo form_error('user_role'); ?></span>
			  </div>
			
			  <div class="row-fluid">
			    <span class="span3">Comment (optional):</span>
			    <div class="span7"><input type="text" name="token_comment" value="" class="span15" /></div>
			  </div>
			  
			  <div class="row-fluid">
				<div class="span3"></div>
				<div class="span4">This note will not be shown to the user.</div>
			  </div>
			
			  <div class="row-fluid">
			    <div class="span3"></div>
			    <span class="span4"><input type="submit" name="create_token" value="Create" class="btn" /></span>			  
			  </div>
		    </div>
		  </form>
		  
<?php if($tokens !== FALSE) { ?>
		  <table class='table'>
			<thead>
			  <tr>
			    <th>Link</th>
			    <th>Delete</th>
			    <th>Comment</th>
			  </tr>
			</thead>
			<tbody>
<?php foreach($tokens as $token) { ?>
			  <tr>
				<td class='span2'><?php echo anchor('register/'.$token['token_content'], 'Right click to copy', 'class="btn btn-mini"'); ?></td>
				<td><?php echo anchor('admin/tokens/delete/'.$token['token_content'], 'Delete', 'class="btn btn-danger btn-mini"'); ?></td>
				<td><?php echo $token['comment']; ?></td>
			  </tr>
<?php } ?>
			</tbody>
		  </table>
<?php } ?>
		</div>
