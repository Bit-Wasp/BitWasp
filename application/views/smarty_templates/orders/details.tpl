            {capture name='t_vendor_url'}user/{$order.vendor.user_hash}{/capture}
            {capture name='t_buyer_url'}user/{$order.buyer.user_hash}{/capture}

            <div class="col-md-9" id="order-details">

                <h2>Order Details: #{$order.id}</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" success="$success"}

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
                                            <td>{$coin.code} {$fees.shipping_cost|escape:"html":"UTF-8"}</td>
                                        </tr>
                                    {if $current_user.user_role == 'Vendor'}
                                        <tr>
                                            <td></td>
                                            <td>Fees</td>
                                            <td>{$coin.code} {$order.vendor_fees|escape:"html":"UTF-8"}</td>
                                        </tr>
                                    {else}
                                        <tr>
                                            <td></td>
                                            <td>Fees</td>
                                            <td>{$coin.code} {$fees.fee|escape:"html":"UTF-8"}</td>
                                        </tr>
                                    {/if}
                                        <tr>
                                            <td></td>
                                            <td><strong>Total</strong></td>
                                            <td>{$coin.code} {$order.order_price|escape:"html":"UTF-8"}</td>
                                        </tr>
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
                                    <div class="row">
                                        <label class="col-xs-3" for="import_command">Import Command</label>
                                        <div class="col-xs-9">
                                            <textarea id="import_command" class="form-control">addmultisigaddress 2 '["{$order.buyer_public_key|escape:"html":"UTF-8"}","{$order.vendor_public_key|escape:"html":"UTF-8"}","{$order.admin_public_key|escape:"html":"UTF-8"}"]'</textarea>
                                        </div>
                                    </div>
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
                                        Scan to Pay:
                                    <a href='{$payment_url}'><img style="width:100%" src='data:image/png;base64,{$qr}' /></a>
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
                    {form method="open" action=$action_page attr='class="form-horizontal"'}
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
                                                        {if $addrs.{$addr} == 'admin'}
                                                            <div class='col-xs-2'>Fees</div>
                                                            <div class='col-xs-4'>{$coin.symbol} {$arr.value}</div>
                                                            <div class='col-xs-6'>{$addr}</div>
                                                        {elseif in_array}
                                                            {capture name="t_pay_user_url"}user/{$order.{$addrs.{$addr}}.user_hash}{/capture}
                                                            {capture name="t_pay_user_name"}{{$addrs.{$addr}}|escape:"html":"UTF-8"}{/capture}
                                                            <div class='col-xs-2'>{url type="anchor" url=$smarty.capture.t_pay_user_url text=$smarty.capture.t_pay_user_name attr=''}</div>
                                                            <div class='col-xs-4'>{$coin.symbol} {$arr.value}</div>
                                                            <div class='col-xs-6'>{$addr}</div>
                                                        {/if}
                                                        </div>
                                                    {/foreach}
                                                </div>
                                            </div>
                                            <!-- End Tx Info -->

                                            <!-- Display Unsigned/Partially signed transaction -->
                                            <div class='row'>
                                                <label class="col-xs-3" for="display_transaction">{if $order.partially_signed_transaction !== ''}Partially Signed Transaction{else}Unsigned Transaction{/if}</label>
                                                <div class="col-xs-9">
                                                    <textarea id="display_transaction" class="form-control">{if $order.partially_signed_transaction !== ''}{$order.partially_signed_transaction} {$order.json_inputs}{else}{$order.unsigned_transaction}{$order.json_inputs}{/if}</textarea>

                                                    {if $order.partially_signed_transaction !== ''}
                                                        {if $order.partially_signing_user_id !== $current_user.user_id}
                                                        {capture name="t_partially_signed_user_url"}user/{$signer.user_hash}{/capture}
                                                        <div class="col-xs-12">
                                                            Signed by {url type="anchor" url=$smarty.capture.t_partially_signed_user_url text=$signer.user_name|escape:"html":"UTF-8" attr=''} {$order.partially_signed_time_f}. Sign and broadcast to complete payment.
                                                        </div>
                                                        {else}
                                                            You signed this transaction {$order.partially_signed_time_f}.
                                                        {/if}
                                                    {/if}

                                                    {if $display_form == TRUE}
                                                        {if $strange_address == TRUE}
                                                    <div class="col-xs-12">
                                                        <div class="col-xs-8">
                                                            Warning! This transaction has been tampered with, do not sign, message an admin.
                                                        </div>
                                                    </div>
                                                        {/if}
                                                    {/if}
                                                </div>
                                            </div>
                                            <!-- End Display Unsigned/Partially signed transaction -->

                                            <!-- Paste Signed Transaction Row -->
                                            <div class='row'>
                                                {if $display_form == TRUE}
                                                <label class="col-xs-3" for="paste_transaction">Paste Signed Transaction</label>
                                                <div class="col-xs-9">
                                                    <textarea name="partially_signed_transaction" id="paste_transaction" class="form-control"></textarea>
                                                </div>
                                                {else}
                                                {if $order.partially_signed_transaction == null OR $order.partially_signing_user_id == $current_user.user_id}
                                                <label class="col-xs-3" for="message"></label>
                                                <div class="col-xs-9">
                                                    Waiting on the other user to sign.
                                                </div>
                                                {/if}
                                                {/if}
                                            </div>
                                            <!-- End Paste Signed Transaction Row -->

                                            <!-- Buttons -->
                                            <div class="row">
                                                <label class="col-xs-3" for="submit"></label>
                                                <div class="col-xs-9">
                                                    {if $display_form == TRUE}<input type="submit" name="submit_signed_transaction" class="btn btn-primary" value="Submit Transaction" />{/if}
                                                    {if $can_finalize_early == TRUE}
                                                        {capture name='t_finalize_early_url'}orders/finalize_early/{$order.id}{/capture}
                                                        {url type="anchor" url=$smarty.capture.t_finalize_early_url text='Finalize Early' attr='class="btn btn-default"'}
                                                    {/if}
                                                    {if $can_refund == TRUE}
                                                        {capture name='t_refund_url'}orders/refund/{$order.id}{/capture}
                                                        {url type="anchor" url=$smarty.capture.t_refund_url text='Issue Refund' attr='class="btn btn-success"'}
                                                    {/if}
                                                    {url type="anchor" url=$cancel_page text="Back" attr='title="Back" class="btn btn-default"'}
                                                </div>
                                            </div>
                                            <!-- End Buttons -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                {/if}
            </div>