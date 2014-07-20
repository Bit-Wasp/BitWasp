
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

                                                    {if $display_sign_form == TRUE}
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
                                                {if $display_sign_form == TRUE}
                                                <label class="col-xs-3" for="paste_transaction">Paste Signed Transaction</label>
                                                <div class="col-xs-9">
                                                    <textarea name="partially_signed_transaction" id="paste_transaction" class="form-control"></textarea>
                                                    Sign with private key: m/0'/0/{$my_multisig_key.key_index}
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
                                                    {if $display_sign_form == TRUE}<input type="submit" name="submit_signed_transaction" class="btn btn-primary" value="Submit Transaction" />{/if}

                                                    {if $can_refund == TRUE}
                                                        {capture name='t_refund_url'}orders/refund/{$order.id}{/capture}
                                                        {url type="anchor" url=$smarty.capture.t_refund_url text='Issue Refund' attr='class="btn btn-success"'}
                                                    {/if}
                                                    {url type="anchor" url=$cancel_page text="Back" attr='title="Back" class="btn btn-default"'}
                                                </div>
                                            </div>
                                            <!-- End Buttons -->
