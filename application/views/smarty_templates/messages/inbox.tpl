
            <div class="col-md-9" id="inbox">
                <div class="row">
		            <h2>Inbox {if $count_unread_messages > 0}<small>{$count_unread_messages} new messages</small>{/if}</h2>

                    {assign var="defaultMessage" value=""}
                    {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                    {if is_array($messages) == TRUE}
                    <table class="table">
                        <thead>
                            <tr>
                                <th>From</th>
                                <th>Subject</th>
                                <th>Time</th>
                                <th></th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>

                        {foreach from=$messages item=message}
                            {form method="open" action="inbox" class="form-horizontal"}
                            {capture name="t_from_user_url"}user/{$message.from.user_hash}{/capture}
                            {capture name="t_msg_url"}message/{$message.hash}{/capture}
                            {capture name="t_msg_send_url"}message/send/{$message.hash}{/capture}
                            {capture name="t_msg_delete_url"}message/delete/{$message.hash}{/capture}
                            {capture name="t_msg_subject"}{if $message.viewed == FALSE}<strong>{/if}{if strlen($message.subject) > 35}{substr($message.subject|escape:"html":"UTF-8",0,35)}{else}{$message.subject|escape:"html":"UTF-8"}{/if}{if $message.viewed == FALSE}</strong>{/if}{/capture}
                            <tr{if $message.viewed == FALSE} class='info'{/if}>
                                <td>{url type="anchor" url=$smarty.capture.t_from_user_url text=$message.from.user_name|escape:"html":"UTF-8" attr=''}</td>
                                <td>{url type="anchor" url=$smarty.capture.t_msg_url text=$smarty.capture.t_msg_subject attr=''}</td>
                                <td>{$message.time_f}</td>
                                <td>{if $message.encrypted == '1'}<a title="PGP encrypted"><span class="glyphicon glyphicon-lock"></span></a>{/if} {if $message.remove_on_read == '1'}<a title="Will be deleted once read"><span class="glyphicon glyphicon-fire"></span></a>{/if}</td>
                                <td>{url type="anchor" url=$smarty.capture.t_msg_url text="View" attr='class="btn btn-primary"'}
                                {if $message.viewed == '1'}
                                    {url type="anchor" url=$smarty.capture.t_msg_send_url text='Reply' attr='class="btn btn-success"'}
                                    <input type='submit' name='delete_message' value='Delete' class="btn btn-danger"/>
                                    <input type='hidden' name="delete_message_hash" value="{$message.hash}" />
                                {/if}</td>
                            </tr>
                            </form>
                        {/foreach}
                        </tbody>
                    </table>
                    {else}
                        <p>No messages in your inbox.</p>
                    {/if}

                    {form method="open" action="inbox" class="form-horizontal"}

                        <div class="form-group">
                            <label class="control-label col-sm-2 col-lg-2 col-md-2" for="submit"></label>
                            <div class="col-sm-5 col-lg-5 col-md-5">
                                <p align="center">
                                    {url type="anchor" url="message/send" text="Compose" attr='class="btn btn-primary"'}
                                    <input type='submit' name='delete_all_messages' value='Delete All' class="btn btn-danger"/>
                                    <input type='hidden' name="delete_message" value="all" />
                                </p>
                            </div>
                        </div>
                    </form>
		        </div>
            </div>