
            <div class="col-md-9" id="my-orders">
                <div class="row">
			        <h2>Review Order</h2>

                    {assign var="defaultMessage" value=""}
                    {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" success="$success"}
			
                    <p>Review your order details, and enter your address & public key if you are happy to proceed.</p>
                    <p>Once you have confirmed your order, the order price will be updated to reflect the shipping costs and order fee's.</p>
                    <p>Once the vendor is set up correctly, you will be able to see the payment address after completing this step. You will need to keep your private key, and be able to sign a transaction later.</p>

                    {capture name='t_purchase_url'}purchases/confirm/{$order.id}{/capture}
                    {capture name="t_vendor_url"}user/{$order.vendor.user_hash}{/capture}

                    <div class="row">

                        <div class="col-xs-10 col-xs-offset-1">
                            <div class="table-responsive">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Order with {url type="anchor" url=$smarty.capture.t_vendor_url text=$order.vendor.user_name attr=''}:</div>

                                    <table class="table table-striped">
                                        <tbody>
                                        {foreach from=$order.items item=item}
                                            {capture name="t_item_url"}item/{$item.hash}{/capture}
                                            <tr>
                                                <td>{$item.quantity|escape:"html":"UTF-8"} x</td>
                                                <td>{url type="anchor" url=$smarty.capture.t_item_url text=$item.name attr=''}</td>
                                                <td>{$coin.code} {$item.quantity*$item.price_b}</td>
                                            </tr>
                                        {/foreach}
                                        <tr>
                                            <td></td>
                                            <td>Shipping to {$order.buyer.location_f}</td>
                                            <td>{$coin.code} {$fees.shipping_cost}</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>Fees</td>
                                            <td>{$coin.code} {$fees.fee}</td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td><strong>Total</strong></td>
                                            <td>{$coin.code} {$total}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">

                    {form method="open" action=$smarty.capture.t_purchase_url attr=['name'=>'placeOrderForm','id'=>'placeOrderForm','class'=>'form-horizontal']}

                        <div class="row">
                            <div class="row">
                                <div class="col-md-10">Generate a fresh private/public keypair, store them somewhere safe, and paste your public key below:</div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-12">
                                    <label class="control-label col-xs-2" for="bitcoin_public_key">Public Key</label>
                                    <div class="col-xs-8">
                                        <input type='text' class="form-control" id="bitcoin_public_key" name='bitcoin_public_key' value="{form method="set_value" field="user_name"}" />
                                    </div>
                                </div>
                                <div class="col-xs-10 col-xs-offset-2">{form method='form_error' field='bitcoin_public_key'}</div>
                            </div>
                        </div>
                        <br />

                        <div class="row">
                            <div class="row">
                                <div class="col-xs-10">Enter your exact shipping address. {if $order.vendor.pgp == TRUE}It will be encrypted before it leaves your browser if you have javascript enabled.{/if}</div>
                            </div>
                            <div class="form-group">
                                <div class="col-xs-12">
                                    <label class="control-label col-xs-2" for="buyer_address">Address</label>
                                    <div class="col-xs-8">
                                        <textarea name='buyer_address' rows='5' class='form-control'></textarea>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-xs-offset-2">{form method='form_error' field='buyer_address'}</div>
                            </div>
                        </div>

                        {if $order.vendor.pgp == TRUE}
                        <textarea style="display:none;" name="public_key">{$order.vendor.pgp.public_key|escape:"html":"UTF-8"}</textarea>
                        {/if}

                        <div class="form-group">
                            <label class="control-label col-sm-2 col-lg-2 col-md-2" for="submit"></label>
                            <div class="col-sm-5 col-lg-5 col-md-5">
                                <p align="center">
                                    <input type="submit" class="btn btn-primary" value='Place Order' onclick='messageEncrypt()' />
                                    {url type="anchor" url='order/list' text="Cancel" attr='class="btn btn-default"'}
                                </p>
                            </div>
                        </div>

                    </form>
                </div>
    		</div>
