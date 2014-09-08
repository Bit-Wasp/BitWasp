            <div class="col-md-9 mainContent" id="admin-order-list">

                <h2>{url type="anchor" url="admin" text="Back" attr='class="btn btn-default"'} Order List</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

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
                                    <td>{$coin.symbol} {number_format($order.order_price,8)}</td>
                                    <td>{$order.progress}</td>
                                    <td>{$order.time_f}</td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>

                {else}
                    There are no orders at this time
                {/if}
    		</div>
