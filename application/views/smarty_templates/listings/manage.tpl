        <div class="col-md-9" id="manage_items">
            <h2>Listings</h2>

            {assign var="defaultMessage" value=""}
            {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

            {if $items == TRUE}
                {foreach from=$items item=item}
                    {form method="open" action="listings"}
                    {capture name='t_item_url'}item/{$item.hash}{/capture}
                    {capture name='t_listing_edit_url'}listings/edit/{$item.hash}{/capture}
                    {capture name='t_listing_images_url'}listings/images/{$item.hash}{/capture}
                    {capture name='t_listing_delete_url'}listings/delete/{$item.hash}{/capture}
                    {capture name='t_item_img'}<img src='data:image/jpeg;base64,{$item.main_image.encoded}' title='{$item.name|escape:"html":"UTF-8"}'>{/capture}
                    <div class='well'>
                        <div class="row">
                            <div class='col-md-3'>{url type="anchor" url=$smarty.capture.t_item_url text=$smarty.capture.t_item_img attr=""}</div>
                            <div class='col-md-6'>{url type="anchor" url=$smarty.capture.t_item_url text=$item.name|escape:"html":"UTF-8" attr=""}<br/>{$item.price_f} <br /> {$item.description_s|escape:"html":"UTF-8"}</div>
                            <div class='col-md-2 col-md-offset-1'>
                                {if $item.hidden == '1'}[hidden]{/if}
                                {url type="anchor" url=$smarty.capture.t_listing_edit_url text='Edit' attr='class="btn btn-default btn-block"'}
                                {url type="anchor" url=$smarty.capture.t_listing_images_url text='Images' attr='class="btn btn-default btn-block"'}
                                <input type="submit" name="delete_listing" value="Delete" class="btn btn-danger btn-block">
                                <input type="hidden" name="delete_listing_hash" value="{$item.hash}" />
                            </div>
                        </div>
                    </div>
                    </form>
                {/foreach}
            {else}
                You have no listings!
            {/if}

            <br />

            <div class="form-group">
                <label class="control-label col-xs-2" for="submit"></label>
                <div class="col-xs-5">
                    <p align="center">
                        {url type="anchor" url="listings/add" text="Add a listing" attr='class="btn btn-primary"'}
                        {url type="anchor" url="" text="Cancel" attr='title="Cancel" class="btn btn-default"'}
                    </p>
                </div>
            </div>
        </div>
