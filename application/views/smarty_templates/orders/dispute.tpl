            {capture name="t_other_user_url"}user/{{$current_order.{$other_role}.user_hash}}{/capture}
            {capture name="t_other_user_name"}{{$current_order.{$other_role}.user_name|escape:"html":"UTF-8"}}{/capture}

            <div class="col-md-9" id="dispute-transaction">
                <h2>Dispute: Order #{$current_order.id}</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {if $form == TRUE}
                {form method="open" action=$dispute_page attr=['class'=>"form-horizontal"]}
                    <div class='well'>
                        <div class='col-xs-12'>
                            <div class='col-xs-6'>
                                <div class="col-xs-12">
                                    <div class="col-xs-4"><strong>Order Date</strong></div>
                                    <div class="col-xs-8">{$current_order.created_time_f}</div>
                                </div>

                                <div class="col-xs-12">
                                    <div class="col-xs-4"><strong>Price</strong></div>
                                    <div class="col-xs-8">{$current_order.currency.symbol} {$current_order.price}</div>
                                </div>

                                <div class="col-xs-12">
                                    <div class="col-xs-4"><strong>{ucfirst($other_role)}</strong></div>
                                    <div class="col-xs-8">{url type="anchor" url=$smarty.capture.t_other_user_url text=$smarty.capture.t_other_user_name|escape:"html":"UTF-8" attr=''}</div>
                                </div>
                            </div>

                            <div class='col-xs-6'>
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="col-xs-3"><strong>Items</strong></div>
                                        <div class="col-xs-9">
                                            <ul>
                                            {foreach from=$current_order.items item=item}
                                                <li>{$item.quantity|escape:"html":"UTF-8"} x {$item.name|escape:"html":"UTF-8"}</li>
                                            {/foreach}
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <label for="dispute_reason" class="control-label col-xs-3">Dispute Reason</label>
                                <div class="col-xs-7">
                                    <textarea id="dispute_message" name="dispute_message" rows="5" class='form-control'></textarea>
                                </div>
                            </div>
                            <div class="col-xs-9 col-xs-offset-3">{form method="form_error" field="dispute_message"}</div>
                        </div>


                        <div class="form-group">
                            <label class="control-label col-sm-2 col-lg-2 col-md-2" for="submit"></label>
                            <div class="col-sm-5 col-lg-5 col-md-5">
                                <p align="center">
                                    <input type='submit' class="btn btn-primary" value='Submit' />
                                    {url type="anchor" url=$cancel_page text="Cancel" attr='class="btn btn-default"'}
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
                {else}
                {capture name="t_disputing_user_url"}user/{$disputing_user.user_hash}{/capture}
                {capture name="t_disputing_user_name"}{$disputing_user.user_name|escape:"html":"UTF-8"}{/capture}

                    {form method="open" action=$dispute_page attr='class="form-horizontal"'}
                <div class='well'>
                    <div class='row'>
                        <div class='col-xs-6'>

                            <div class="col-xs-6"><strong>Order Date</strong></div>
                            <div class="col-xs-6">{$current_order.created_time_f}</div>


                            <div class="col-xs-6"><strong>Amount Paid</strong></div>
                            <div class="col-xs-6">{$coin.symbol} {number_format($current_order.order_price, 8)}
                                {if $current_order.currency.id !== '0'}
                                    / {$current_order.currency.symbol} {$current_order.price_l}<br />
                                {/if}
                                {if $current_order.vendor_selected_upfront == 1}
                                    upfront
                                {elseif $current_order.vendor_selected_escrow == '1'}
                                    escrow
                                {/if}
                            </div>

                            <div class="col-xs-6"><strong>Disputing User</strong></div>
                            <div class="col-xs-6">{url type="anchor" url=$smarty.capture.t_disputing_user_url text=$smarty.capture.t_disputing_user_name|escape:"html":"UTF-8" attr=''}</div>

                        </div>

                        <div class='col-xs-6'>

                            <div class="col-xs-1"><strong>Items:</strong></div>
                            <div class="col-xs-9">
                                <ul>
                                {foreach from=$current_order.items item=item}
                                    <li>{$item.quantity|escape:"html":"UTF-8"} x {$item.name|escape:"html":"UTF-8"}</li>
                                {/foreach}
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>

                <div class='well'>
                    <h4>Messages</h4>

                        <div class='col-xs-12'>
                            <div class="col-xs-2"><strong>Initial Dispute</strong></div>
                            <div class="col-xs-5">{$dispute.dispute_message|escape:"html":"UTF-8"}</div>
                        </div>

                        {if count($dispute.updates) > 0}
                        <hr>
                        <div class="row">
                        {foreach from=$dispute.updates item=update}
                            <div class='col-xs-12'>
                                <div class='col-xs-2'>
                                    {if $update.posting_user_id == '0'}
                                        <b>Notification</b>
                                    {else}
                                        {capture name="t_posting_user_url"}user/{$update.posting_user_hash}{/capture}
                                        Posted {$update.time_f} by {url type="anchor" url=$smarty.capture.t_posting_user_url text=$update.posting_user_name|escape:"html":"UTF-8" attr=''}.
                                    {/if}
                                </div>
                                <div class='col-xs-10'>{$update.message|escape:"html":"UTF-8"}</div>
                            </div>
                            <br />
                        {/foreach}
                        </div>
                        {/if}

                        {if $dispute.final_response == '0'}
                        <hr />
                        <div class='form-group'>
                            <div class="col-xs-12">
                                <label class="control-label col-xs-2">Response</label>
                                <div class='col-xs-9'>
                                    <textarea name='update_message' class='form-control'></textarea>
                                </div>
                            </div>
                            <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="update_message"}</div>
                        </div>

                        <div class="form-group">
                            <label class="control-label col-sm-2 col-lg-2 col-md-2" for="submit"></label>
                            <div class="col-sm-5 col-lg-5 col-md-5">
                                <p align="center">
                                    <input type='submit' name='post_dispute_message' value='Post Message' class='btn btn-primary' />
                                </p>
                            </div>
                        </div>
                        {/if}
                    </form>
                </div>
                {/if}
            </div>
