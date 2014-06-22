            {capture name="t_FE_url"}orders/finalize_early/{$order.id}{/capture}
            {capture name="t_buyer_url"}user/{$order.buyer.user_hash}{/capture}
            {capture name="t_refund_url"}orders/vendor_refund/{$order.id}{/capture}

            <div class="col-xs-9" id="request_FE">

                <h2>Finalize Early: Order {$order.id}</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" success="$success"}

                {form method="open" action=$smarty.capture.t_FE_url attr='class="form-horizontal"'}
                <div class='col-xs-9'>
                    If you need to, you can request early finalization for this order. {url type="anchor" url=$smarty.capture.t_buyer_url text=$order.buyer.user_name|escape:"html":"UTF-8" attr=''} will be asked to sign the transaction immediately. Once this is done, you can sign and broadcast to claim the funds. <br /><br />
                    <div class='row'>
                        <div class='row'>
                            <div class='col-xs-6'>
                                <ul>
                                    {foreach from=$order.items item=item}
                                        {capture name="t_item_url"}item/{$item.hash}{/capture}
                                        <li>{$item.quantity|escape:"html":"UTF-8"} x {if $item.hash == 'removed'}{$item.name|escape:"html":"UTF-8"}{else}{url type="anchor" url=$smarty.capture.t_item_url text=$item.name|escape:"html":"UTF-8" attr=''}{/if}</li>
                                    {/foreach}
                                </ul>
                            </div>
                            <div class='col-xs-6'>
                                <div class='row'>
                                    <div class='col-xs-4'>Earnings</div>
                                    <div class='col-xs-8'>
                                        {$order.currency.symbol} {number_format($order.total_paid-$order.extra_fees-$order.fees,8)}
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <br />

                    <div class='form-group'>
                        <div class="col-xs-12">
                            <div class='col-xs-4'>Are you sure?</div>
                            <div class='col-xs-6'>
                                <label class="radio-inline">
                                    <input type="radio" name="upfront" value='0'>  No<br />
                                </label>

                                <label class="radio-inline">
                                    <input type="radio" name="upfront" value='1'>  Yes
                                </label>
                            </div>
                        </div>
                        <div class="col-xs-8 col-xs-offset-4">
                            {form method="form_error" fieldset="upfront"}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2 col-lg-2 col-md-2" for="submit"></label>
                        <div class="col-sm-5 col-lg-5 col-md-5">
                            <p align="center">
                                <input type='submit' class="btn btn-primary" name='request_FE' value='Continue' />
                                {url type="anchor" url="orders" text="Cancel" attr='title="Cancel" class="btn btn-default"'}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
