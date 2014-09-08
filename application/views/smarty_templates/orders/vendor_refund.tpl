            {capture name="t_refund_url"}orders/vendor_refund/{$order.id}{/capture}

            <div class="span9" id="issue_refund">
                <div class="row-fluid">

			        <h2>Issue Refund: Order {$order.id}</h2>

                    {assign var="defaultMessage" value=""}
                    {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                    {form method="open" action=$smarty.capture.t_refund_url attr='class="form-horizontal"'}
            			<div class='col-xs-9'>
				            Once you issue a refund, the Order Details page will display a new unsigned transaction which refunds the buyer the full amount.<Br /><br />
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
                                            <div class='col-xs-4'>Order Amount</div>
                                            <div class='col-xs-8'>
                                            {if $current_user.currency.id !== '0'}
                                                {$current_user.currency.symbol}{number_format(($order.total_paid*$current_user.currency.rate),2)} /
                                            {/if}
                                            {$order.currency.symbol} {number_format($order.total_paid, 8)}
                                            </div>
                                        </div>
                                        <div class='row'>
                                            <div class='col-xs-4'>Refund</div>
                                            <div class='col-xs-8'>{$coin.symbol} {$order.total_paid-0.0001}</div>
                                        </div>
                                    </div>
                                </div>

					        </div>
                            <br />

                            <div class='row'>
                                <div class='col-xs-4'>Are you sure?</div>
                                <div class='col-xs-6'>
                                    <label class="checkbox-inline">
                                        <input type='radio' name='refund' value='0'>  No<br />
                                    </label>

                                    <label class="checkbox-inline">
                                        <input type='radio' name='refund' value='1'>  Yes
                                    </label>

                                </div>
                                {form method="form_error" fieldset="refund"}
                            </div>
                            <br />
                            <div class="form-group">
                                <label class="control-label col-sm-2 col-lg-2 col-md-2" for="submit"></label>
                                <div class="col-sm-5 col-lg-5 col-md-5">
                                    <p align="center">
                                        <input type='submit' class="btn btn-primary" name='issue_refund' value='Issue Refund' />
                                        {url type="anchor" url="orders" text="Cancel" attr='title="Cancel" class="btn btn-default"'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
