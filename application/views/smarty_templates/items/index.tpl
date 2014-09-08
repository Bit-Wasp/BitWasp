            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-12 btn-group">
                        <div class='pull-right'>
                            {$links}
                        </div>
                        <h2>{if isset($custom_title) == TRUE}{$custom_title|escape:'html':'UTF-8'}
                            {else}Items{/if}</h2>
                    </div>
                </div>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {if is_array($items) AND count($items) > 0}
                    {$c = 0}
                    <div class="row">
                    {foreach from=$items item=item}

                        {capture name="t_item_img"}<img src='data:image/jpeg;base64,{$item.main_image.encoded}' title='{$item.name|escape:"html":"UTF-8"}'>{/capture}
                        {capture name="t_item_url"}item/{$item.hash}{/capture}
                        {capture name="t_item_vendor_url"}user/{$item.vendor.user_hash}{/capture}
                        {capture name="t_reviews_url"}reviews/view/item/{$item.hash}{/capture}
                        {capture name="t_reviews_str"}{$item.review_count} reviews{/capture}
                            <div class="col-sm-4 col-lg-4 col-md-4">
                                <div class="thumbnail">
                                    {url type="anchor" url=$smarty.capture.t_item_url text=$smarty.capture.t_item_img attr="title='{$item.name|escape:"html":"UTF-8"}'"}
                                    <div class="caption">
                                        <h4>{url type="anchor" url=$smarty.capture.t_item_url text=$item.name|escape:"html":"UTF-8" attr="title='{$item.name|escape:"html":"UTF-8"}'"}
                                        </h4>
                                        <p>{$item.price_f} - {url type="anchor" url=$smarty.capture.t_item_vendor_url text=$item.vendor.user_name|escape:"html":"UTF-8" attr=""}</p>
                                    </div>
                                    <div class="ratings">
                                        <p class="pull-right">{url type="anchor" url=$smarty.capture.t_reviews_url text=$smarty.capture.t_reviews_str attr=''}</p>
                                        <p>{rating rating=$item.average_rating}</p>
                                    </div>
                                </div>
                            </div>
                    {/foreach}
                    </div>

                {else}
                    There are no items at present, please try again later!
                {/if}
            </div>
