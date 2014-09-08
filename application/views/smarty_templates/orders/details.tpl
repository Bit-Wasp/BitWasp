            {capture name='t_vendor_url'}user/{$order.vendor.user_hash}{/capture}
            {capture name='t_buyer_url'}user/{$order.buyer.user_hash}{/capture}

            <div class="col-md-9" id="order-details">

                <h2>Order Details: #{$order.id}</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <div class="row">
                    <!-- Display Order Items, Shipping Info, Fees -->
                    <div class="col-xs-12 col-md-10 col-md-offset-1">
                        <div class="table-responsive">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                {if $current_user.user_role == 'Admin'}
                                    {url type="anchor" url=$smarty.capture.t_buyer_url text=$order.buyer.user_name attr=''}'s order with {url type="anchor" url=$smarty.capture.t_vendor_url text=$order.vendor.user_name|escape:"html":"UTF-8" attr=''}
                                {elseif $current_user.user_role == 'Buyer'}
                                    Order with {url type="anchor" url=$smarty.capture.t_vendor_url text=$order.vendor.user_name|escape:"html":"UTF-8" attr=''}:
                                {elseif $current_user.user_role == 'Vendor'}
                                    Order with {url type="anchor" url=$smarty.capture.t_buyer_url text=$order.buyer.user_name|escape:"html":"UTF-8" attr=''}:
                                {/if}
                                </div>

                                <table class="table table-striped">
                                    <tbody>
                                    {foreach from=$order.items item=item}
                                    {capture name="t_item_url"}item/{$item.hash}{/capture}
                                        <tr>
                                            <td>{$item.quantity|escape:"html":"UTF-8"}</td>
                                            <td>{url type="anchor" url=$smarty.capture.t_item_url text=$item.name|escape:"html":"UTF-8" attr=''}</td>
                                            <td>{$coin.code} {number_format(($item.quantity*$item.price_b),8)}</td>
                                        </tr>
                                    {/foreach}
                                        <tr>
                                            <td></td>
                                            <td>Shipping to {$order.buyer.location_f}</td>
                                            <td>{$coin.code} {number_format($fees.shipping_cost|escape:"html":"UTF-8",8)}</td>
                                        </tr>
                                    {if $current_user.user_role == 'Vendor'}
                                        <tr>
                                            <td></td>
                                            <td>Fees</td>
                                            <td>{$coin.code} {number_format($order.vendor_fees|escape:"html":"UTF-8",8)}</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td><strong>Earnings</strong></td>
                                            <td>{$coin.code} {number_format($order.order_price|escape:"html":"UTF-8",8)}</td>
                                        </tr>
                                    {else}
                                        <tr>
                                            <td></td>
                                            <td>Fees</td>
                                            <td>{$coin.code} {number_format($fees.fee|escape:"html":"UTF-8",8)}</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td><strong>Total</strong></td>
                                            <td>{$coin.code} {number_format($order.order_price|escape:"html":"UTF-8",8)}</td>
                                        </tr>
                                    {/if}

                                    </tbody>
                                </table>
                            </div>
                            <!-- End Display Order Items, Shipping Info, Fees -->
                        </div>
                    </div>
                </div>

                {if $order.address == TRUE}
                <div class="row">
                    <div class="col-xs-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">Payment Details</div>
                            <div class="panel-body">
                                <div class="col-xs-12 col-md-10">
                                    <div class="row">
                                        <label class="col-xs-3" for="address">Address</label>
                                        <div class="col-xs-9">{$order.address}</div>
                                    </div>
                                    <div class="row">
                                        <label class="col-xs-3" for="redeem_script">Redeem Script</label>
                                        <div class="col-xs-9"><textarea id="redeem_script" class="form-control">{$order.redeemScript|escape:"html":"UTF-8"}</textarea></div>
                                    </div>
                                    {if $my_multisig_key.provider == 'Manual'}
                                    <div class="row">
                                        <label class="col-xs-3" for="import_command">Import Command</label>
                                        <div class="col-xs-9">
                                            <textarea id="import_command" class="form-control">addmultisigaddress 2 '["{$redeem_script.keys.0|escape:"html":"UTF-8"}","{$redeem_script.keys.1|escape:"html":"UTF-8"}","{$redeem_script.keys.2|escape:"html":"UTF-8"}"]'</textarea>
                                        </div>
                                    </div>
                                    {elseif $my_multisig_key.provider == 'JS'}
                                    <noscript>
                                        <div class="row">
                                            <label class="col-xs-3" for="import_command">Import Command</label>
                                            <div class="col-xs-9">
                                                <textarea id="import_command" class="form-control">addmultisigaddress 2 '["{$redeem_script.keys.0|escape:"html":"UTF-8"}","{$redeem_script.keys.1|escape:"html":"UTF-8"}","{$redeem_script.keys.2|escape:"html":"UTF-8"}"]'</textarea>
                                            </div>
                                        </div>
                                    </noscript>
                                    {/if}
                                    {if $order.final_transaction_id !== ''}
                                    <div class="row">
                                        <label class="col-xs-3" for="import_command">Final Transaction</label>
                                        <div class="col-xs-9">
                                            {$order.final_transaction_id}
                                        </div>
                                    </div>
                                    {/if}
                                </div>
                                <div class="col-xs-12 col-md-2">
                                    {if isset($qr) == TRUE}
                                        Scan to Pay: <a href='{$payment_url}'><img style="width:100%" src='data:image/png;base64,{$qr}' /></a>
                                        {$coin.code}{$order.order_price|escape:"html":"UTF-8"}
                                    {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {/if}

                {if $order.paid_time !== '' && $order.final_transaction_id == null}
                <div class="row">
                    {form method="open" action=$action_page attr='class="form-horizontal" name="sign_transaction" id="sign_transaction"'}
                        <div class="col-xs-12">
                            <div class="table-responsive">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Add Signature</div>
                                    <div class="panel-body">
                                        <div class="col-xs-10">
                                            <!-- Display Tx Info -->
                                            <div class='row'>
                                                <label class="col-xs-3" for="paying">Paying:</label>
                                                <div class='col-xs-9'>
                                                    {foreach from=$raw_tx.vout item=arr}
                                                        <div class='row'>

                                                        {$addr = $arr.scriptPubKey.addresses.0}
                                                        {if isset($addrs.{$addr}) == FALSE }
                                                            <div class='col-xs-2'>Unknown!</div>
                                                            <div class='col-xs-4'>{$coin.symbol} {$arr.value}</div>
                                                            <div class='col-xs-6'>{$addr}</div>
                                                        {else}
                                                            {if $addrs.{$addr} == 'admin'}
                                                                <div class='col-xs-2'>Fees</div>
                                                                <div class='col-xs-4'>{$coin.symbol} {$arr.value}</div>
                                                                <div class='col-xs-6'>{$addr}</div>
                                                            {elseif in_array($addrs.{$addr}, ['buyer','vendor']) }
                                                                {capture name="t_pay_user_url"}user/{$order.{$addrs.{$addr}}.user_hash}{/capture}
                                                                {capture name="t_pay_user_name"}{$order.{$addrs.{$addr}}.user_name|escape:"html":"UTF-8"}{/capture}
                                                                <div class='col-xs-2'>{url type="anchor" url=$smarty.capture.t_pay_user_url text=$smarty.capture.t_pay_user_name attr=''}</div>
                                                                <div class='col-xs-4'>{$coin.symbol} {$arr.value}</div>
                                                                <div class='col-xs-6'>{$addr}</div>
                                                            {else}
                                                                <div class='col-xs-2'>Unknown</div>
                                                            {/if}
                                                        {/if}


                                                        </div>
                                                    {/foreach}
                                                </div>
                                            </div>
                                            <!-- End Tx Info -->
                                            {if isset($invalid_transaction_error) == TRUE}
                                            <div class='row'>
                                                <div class="col-xs-7 col-xs-offset-3">
                                                    {$invalid_transaction_error}
                                                </div>
                                            </div>
                                            {/if}

                                            {$sign_form_output}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                {/if}
            </div>