            {capture name='t_form_url'}listings/images/{$item.hash}{/capture}

            <div class="col-md-9" id="add_Listing_Image">

                <h2>Item Images</h2>

                {assign var="defaultMessage" value="Select an image to upload for your item: "}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {form method="open-multipart" action=$smarty.capture.t_form_url attr=['class'=>'form-horizontal']}
                    <div class="form-group">
                        <label for="name" class="control-label col-xs-2">Item</label>
                        <div class="col-xs-5">
                            <label class="control-label">{$item.name|escape:"html":"UTF-8"}</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="userfile">Image File</label>
                            <div class="input-group">
                                <span class="btn btn-default btn-file">
                                    <input type="file" name="userfile" />
                                </span>
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="userfile"}</div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-2" for="main_image">Main Photo?</label>
                        <div class="col-xs-5">
                            <input type="checkbox" class="checkbox" name="main_image" value="true" />
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="main_image"}</div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-2" for="submit"></label>
                        <div class="col-xs-5">
                            <p align="center">
                                <input type="submit" name="add_image" value="Upload" class="btn btn-primary" />
                                {url type="anchor" url="listings" text="Cancel" attr='class="btn btn-default"'}
                            </p>
                        </div>
                    </div>
                </form>

                {foreach from=$images item=image}
                    {capture name='t_main_image_url'}listings/main_image/{$image.hash}{/capture}
                    {capture name='t_delete_image_url'}listings/delete_image/{$image.hash}{/capture}
                    <div class="col-md-4">
                        <div class="thumbnail">
                            <img class="productImg" src="data:image/jpeg;base64,{$image.encoded}" title="{$item.name|escape:"html":"UTF-8"}" width='150' />
                            <div class="caption">
                                <center>{url type="anchor" url=$smarty.capture.t_main_image_url text="Main Image" attr='class="btn btn-primary"'}
                                {url type="anchor" url=$smarty.capture.t_delete_image_url text="<i class='icon-trash icon-white'></i>Delete" attr='class="btn btn-danger"'}
                                </center>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
