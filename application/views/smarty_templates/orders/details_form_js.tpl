
                                            <div class="col-xs-12">&nbsp;</div>
                                            <!-- Display Unsigned/Partially signed transaction -->
                                            {if $display_sign_form == TRUE}
                                            <div class="row">
                                                <label class="col-xs-3"></label>
                                                <div class="col-xs-9">
                                                    {$display_sign_msg}
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-xs-3" for="wallet_passphrase">Wallet Passphrase</label>
                                                <div class="col-xs-9">
                                                    <input type="text" name="wallet_passphrase" id="wallet_passphrase" class="form-control" value="" />
                                                </div>
                                            </div>
                                            {/if}
                                            {if $order.partially_signed_transaction == null OR $order.partially_signing_user_id == $current_user.user_id}
                                                <label class="col-xs-3" for="message"></label>
                                                <div class="col-xs-9">
                                                    Waiting on the other user to sign.
                                                </div>
                                            {/if}
                                            <!-- End Display Unsigned/Partially signed transaction -->
                                            <input type="hidden" name="extended_public_key" id="extended_public_key" value="{$signing_info.parent_extended_public_key}" />
                                            <input type="hidden" name="unsigned_transaction" id="unsigned_transaction" value="{$order.unsigned_transaction}" />
                                            <input type="hidden" name="partially_signed_transaction" id="partially_signed_transaction" value="{$order.partially_signed_transaction}" />
                                            <input type="hidden" name="key_index" id="key_index" value="{$signing_info.key_index}" />
                                            <input type="hidden" name="wallet_salt" id="wallet_salt" value="{$wallet_salt}" />

                                            <!-- Buttons -->
                                            <div class="row">
                                                <label class="col-xs-3" for="submit"></label>
                                                <div class="col-xs-9">
                                                    {if $display_sign_form == TRUE}
                                                        <button type="button" onclick="sign_raw_transaction()">click</button>
                                                        <input type="submit" name="js_signed_transaction" class="btn btn-primary" value="Submit Transaction" onclick="sign_raw_transaction()" />{/if}
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
