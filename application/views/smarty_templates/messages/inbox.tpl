
            <div class="col-md-9" id="inbox">
                <div class="row">
		            <h2>Inbox {if $count_unread_messages > 0}<small>{$count_unread_messages} new messages</small>{/if}</h2>

                    {assign var="defaultMessage" value=""}
                    {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" success="$success"}

                    {if is_array($messages) == TRUE}
                    <table class="table table-curved">
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
                            {capture name="t_from_user_url"}user/{$message.from.user_hash}{/capture}
                            {capture name="t_msg_url"}message/{$message.hash}{/capture}
                            {capture name="t_msg_send_url"}message/send/{$message.hash}{/capture}
                            {capture name="t_msg_delete_url"}message/delete/{$message.hash}{/capture}
                            {capture name="t_msg_subject"}{if $message.viewed == FALSE}<strong>{/if}{if strlen($message.subject) > 35}{substr($message.subject|escape:"html":"UTF-8",0,35)}{else}{$message.subject|escape:"html":"UTF-8"}{/if}{if $message.viewed == FALSE}</strong>{/if}{/capture}
                            <tr{if $message.viewed == FALSE} class='info'{/if}>
                                <td>{url type="anchor" url=$smarty.capture.t_from_user_url text=$message.from.user_name|escape:"html":"UTF-8" attr=''}</td>
                                <td>{url type="anchor" url=$smarty.capture.t_msg_url text=$smarty.capture.t_msg_subject attr=''}</td>
                                <td>{$message.time_f}</td>
                                <td>{if $message.encrypted == '1'}[PGP]{/if} {if $message.remove_on_read == '1'}[auto-delete]{/if}</td>
                                <td>{url type="anchor" url=$smarty.capture.t_msg_url text="View" attr='class="btn btn-primary"'}
                                {if $message.viewed == '1'}
                                    {url type="anchor" url=$smarty.capture.t_msg_send_url text='Reply' attr='class="btn btn-success"'}
                                    {url type="anchor" url=$smarty.capture.t_msg_delete_url text='Delete' attr='class="btn btn-danger"'}{/if}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                    {else}
                        <p>No messages in your inbox.</p>
                    {/if}

                    <form class="form-horizontal">
                        <div class="form-actions">
                            {url type="anchor" url="message/send" text="Compose" attr='class="btn btn-primary"'}
                            {url type="anchor" url="message/delete/all" text="Delete All" attr='class="btn btn-danger"'}
                        </div>
                    </form>
		        </div>
            </div>