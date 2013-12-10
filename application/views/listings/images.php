        <div class="span9 mainContent" id="add_Listing_Image">
          <h2>Item Images</h2>
          <?php echo form_open_multipart('listings/images/'.$item['hash'], array('class' => 'form-horizontal')); ?>
            <fieldset>
			  <div class="alert<?php if(isset($success)) echo ' alert-success'; ?>">
			  <?php if(isset($returnMessage)) { echo $returnMessage; } else { ?>
			  Select an image to upload to your item.
			  <?php } ?>
			  </div>
			  
              <div class="control-group">
                <label class="control-label" for="name">Item</label>
                <div class="controls">
                  <p><?php echo $item['name'];?></p>
                </div>
              </div>

              <div class="control-group">
                <label class="control-label" for="userfile">Image File</label>
                <div class="controls">
				  <span class="btn-default btn-file">
                  <input type='file' name='userfile' />
                  </span>
                  <span class="help-inline"><?php echo form_error('userfile'); ?></span>
                </div>
              </div>

              <div class="control-group">
                <label class="control-label" for="main_image">Main Photo?</label>
                <div class="controls">
                  <input type='checkbox' name='main_image' value='true' />
                  <span class="help-inline"><?php echo form_error('main_image'); ?></span>
                </div>
              </div>

 	          <div class="form-actions">
                <input type="submit" name="add_image" value="Create" class="btn btn-primary" />
                <?php echo anchor("item/".$item['hash'] ,"Cancel", 'class="btn"'); ?>
              </div>
            </fieldset>
          </form>

          <ul id="image_listing" class="thumbnails">
<?php foreach ($images as $image) { ?>
            <li class="span3 image_box">
              <div class="thumbnail">
		        <img class="productImg" src="data:image/jpeg;base64,<?php echo $image['encoded'];?>" title="<?php echo $item['name']; ?>" width='150' />
                <div class="caption">
				  <center><?php echo anchor('listings/main_image/'.$image['hash'],'Main Image', 'class="btn btn-mini"');?> <?php echo anchor('listings/delete_image/'.$image['hash'], "<i class='icon-trash icon-white'></i> Delete", 'class="btn btn-danger btn-mini"');?></center>
                </div>
              </div>
            </li>
            
<?php } ?>
	      </ul>
        </div>
        
