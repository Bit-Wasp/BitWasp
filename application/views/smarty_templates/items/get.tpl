        {capture name='t_message_vendor_url'}message/send/{$item.vendor.user_hash}{/capture}
        {capture name='t_item_delete_url'}admin/delete_item/{$item.hash}{/capture}
        {capture name='t_item_purchase_url'}purchase/{$item.hash}{/capture}
        {capture name="t_item_url"}item/{$item.hash}{/capture}
        {capture name='t_vendor_url'}user/{$item.vendor.user_hash}{/capture}
        {capture name='t_vendor_reviews_url'}reviews/view/user/{$item.vendor.user_hash}{/capture}
        {capture name="t_reviews_url"}reviews/view/item/{$item.hash}{/capture}
        {capture name="t_reviews_str"}{$item.review_count} reviews{/capture}

        <div class="col-md-9" id="item_detail">

            {assign var="defaultMessage" value=""}
            {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

            {if $current_user.logged_in == TRUE AND $current_user.user_role == 'Buyer'}
            {form method="open" action="purchases" attr=''}
            {/if}

            <div class="thumbnail">
                {if $item.main_image.hash !== 'default'}
                    {capture name="t_img_uri"}image/{$item.main_image.hash}{/capture}
                    {capture name="t_img"}{url type="site" url=$smarty.capture.t_img_uri}{/capture}
                    {capture name="t_item_img"}<img class="img-responsive" src='{$smarty.capture.t_img}' height='300' title='{$item.name|escape:"html":"UTF-8"}'>{/capture}
                    {capture name="t_item_url"}item/{$item.hash}{/capture}
                    {url type="anchor" url=$smarty.capture.t_item_url text=$smarty.capture.t_item_img attr='title="{$item.name|escape:"html":"UTF-8"}"'}
                {/if}
                <div class="caption-full">
                    <h4 class="pull-right">{$item.price_f|escape:"none"}</h4>
                    <h4>{url type="anchor" url=$smarty.capture.t_item_url text=$item.name|escape:"html":"UTF-8" attr="title='{$item.name|escape:"html":"UTF-8"}'"}</h4>
                    <p class="pull-right">
                        {if $current_user.logged_in == TRUE}
                            {if $current_user.user_hash != $item.vendor.user_hash}{url type="anchor" url=$smarty.capture.t_message_vendor_url text="Message" attr='class="btn btn-default"'}<br />{/if}
                            {if $current_user.user_role == 'Buyer'}
                                <input type="hidden" name="item_hash" value="{$item.hash}" style="display:none" />
                                <input type="submit" name="submit_purchase" value="Purchase" class="btn btn-primary">
                            {/if}
                        {/if}
                    </p>
                    <p>{nl2br($item.description|escape:"html":"UTF-8")}</p>
                    <p>By {url type="anchor" url=$smarty.capture.t_vendor_url text=$item.vendor.user_name|escape:"html":"UTF-8" attr=""} <span class="rating">({url type="anchor" url=$smarty.capture.t_vendor_reviews_url text=$vendor_rating attr=''})</p>
                    <p>Added: {$item.add_time_f}</p>
                    {if $item.update_time == TRUE}
                        <p>Updated: {$item.add_time_f}</p>
                    {/if}
                </div>

                <div class="ratings">
                    <p class="pull-right">
                        {url type="anchor" url=$smarty.capture.t_reviews_url text=$smarty.capture.t_reviews_str attr=''}
                    </p>
                    <p>{rating rating=$item.average_rating}</p>
                </div>

                {if $shipping_costs == TRUE}
                <div class="caption">
                    <div class="col-xs-12">
                        <div class='col-xs-6'><strong>Available Locations</strong></div>
                        <div class='col-xs-6'><strong>Cost</strong></div>
                    </div>

                    {foreach from=$shipping_costs item=shipping_charge}
                    <div class="col-xs-12">
                        <div class='col-xs-6'>{$shipping_charge.destination_f}</div>
                        <div class='col-xs-6'>
                            {if $current_user.currency.id !== '0'}
                                {$current_user.currency.symbol} {number_format($shipping_charge.cost*$current_user.currency.rate,2)} /
                            {/if}
                            {$coin.symbol} {number_format($shipping_charge.cost|escape:"html":"UTF-8",8)}
                        </div>
                    </div>
                    {/foreach}
                </div>
                {/if}
            </div>

            {foreach from=$item.images item=image}
                <div class="col-md-4">
                    <div class="thumbnail">
                        <img class="productImg" src="data:image/jpeg;base64,{$image.encoded}" title="{$item.name|escape:"html":"UTF-8"}" width='150' />
                    </div>
                </div>
            {/foreach}

            {if $reviews == TRUE}
            <div class="well" style="background:white;">
                <h4>Recent Reviews</h4>
                {capture name='t_item_all_reviews_url'}reviews/view/item/{$item.hash}{/capture}
                {capture name='t_all_reviews_str'}[All Reviews: {$review_count.all}]{/capture}

                {capture name='t_item_p_reviews_url'}reviews/view/item/{$item.hash}/0{/capture}
                {capture name='t_p_reviews_str'}[Positive: {$review_count.positive}]{/capture}

                {capture name='t_item_d_reviews_url'}reviews/view/item/{$item.hash}/1{/capture}
                {capture name='t_d_reviews_str'}[Disputed: {$review_count.disputed}]{/capture}

                {url type="anchor" url=$smarty.capture.t_item_all_reviews_url text=$smarty.capture.t_all_reviews_str attr=""}
                {url type="anchor" url=$smarty.capture.t_item_p_reviews_url text=$smarty.capture.t_p_reviews_str attr=""}
                {url type="anchor" url=$smarty.capture.t_item_d_reviews_url text=$smarty.capture.t_d_reviews_str attr=""}

                {foreach from=$reviews item=review}
                    <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-6">
                            {foreach from=$review.rating key=quality item=rating}
                            <div class="col-md-12">
                                <div class="col-md-7">
                                    {ucfirst($quality)}
                                </div>
                                <div class="col-md-5">
                                    {rating rating=$rating}
                                </div>
                            </div>
                            {/foreach}
                            <div class="col-md-12">
                                <div class="col-md-7">
                                    Average
                                </div>
                                <div class="col-md-5">
                                    {for $var1=1 to $review.average_rating}<span class="glyphicon glyphicon-star"></span>{/for}{for $var=$var1 to 5}<span class="glyphicon glyphicon-star-empty"></span>{/for}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6"></div>

                        <span class="pull-right">{$review.time_f}</span>
                        <p>{$review.comments|escape:"html":"UTF-8"}</p>
                    </div>
                </div>
                {/foreach}
            </div>
            {/if}
            {if $current_user.logged_in == TRUE AND $current_user.user_role == 'Buyer'}
            </form>
            {/if}
        </div>
