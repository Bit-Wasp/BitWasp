            <div class="col-md-9 mainContent" id="admin-order-list">

                <h2>{url type="anchor" url="admin" text="Back" attr='class="btn btn-default"'} Order List</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" success="$success"}

                {if is_array($orders) == TRUE }
                    <div class="col-md-10 col-md-offset-1 col-xs-12">
                        <table class="table">
                            <thead>
                                <tr>
                                    <td>#</td>
                                    <td>Vendor</td>
                                    <td>Buyer</td>
                                    <td>Price</td>
                                    <td>Step</td>
                                    <td>Confirmed Date</td>
                                </tr>
                            </thead>
                            <tbody>
                            {foreach from=$orders item=order}
                            {capture name="t_order_url"}admin/order/{$order.id}{/capture}
                            {capture name="t_order_str"}#{$order.id}{/capture}
                            {capture name="t_vendor_url"}user/{$order.vendor.user_hash}{/capture}
                            {capture name="t_buyer_url"}user/{$order.buyer.user_hash}{/capture}

                                <tr>
                                    <td>{url type="anchor" url=$smarty.capture.t_order_url text=$smarty.capture.t_order_str attr=''}</td>
                                    <td>{url type="anchor" url=$smarty.capture.t_vendor_url text=$order.vendor.user_name attr=''}</td>
                                    <td>{url type="anchor" url=$smarty.capture.t_buyer_url text=$order.buyer.user_name attr=''}</td>
                                    <td>{$coin.symbol} {$order.order_price}</td>
                                    <td>{$order.progress}</td>
                                    <td>{$order.time_f}</td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>

                    <div class="col-xs-12">
                        <div class="col-xs-1"><strong>#</strong></div>
                        <div class="col-xs-2"><strong>Vendor</strong></div>
                        <div class="col-xs-2"><strong>Buyer</strong></div>
                        <div class="col-xs-2"><strong>Price</strong></div>
                        <dev class="col-xs-1"><strong>Step</strong></dev>
                        <div class="col-xs-2"><strong>Confirmed Date</strong></div>
                    </div>
                    {foreach from=$orders item=order}
                        {capture name="t_order_url"}admin/order/{$order.id}{/capture}
                        {capture name="t_order_str"}#{$order.id}{/capture}
                        {capture name="t_vendor_url"}user/{$order.vendor.user_hash}{/capture}
                        {capture name="t_buyer_url"}user/{$order.buyer.user_hash}{/capture}

                        <div class="col-xs-12">
                            <div class="col-xs-1">{url type="anchor" url=$smarty.capture.t_order_url text=$smarty.capture.t_order_str attr=''}</div>
                            <div class="col-xs-2">{url type="anchor" url=$smarty.capture.t_vendor_url text=$order.vendor.user_name|escape:"html":"UTF-8" attr=''}</div>
                            <div class="col-xs-2">{url type="anchor" url=$smarty.capture.t_buyer_url text=$order.buyer.user_name|escape:"html":"UTF-8" attr=''}</div>
                            <div class="col-xs-2">{$coin.symbol} {$order.order_price}</div>
                            <div class="col-xs-1">{$order.progress}</div>
                            <div class="col-xs-2">{$order.time_f}</div>
                        </div>
                    {/foreach}
                </div>
                {else}
                    There are no orders at this time
                {/if}
    		</div>
