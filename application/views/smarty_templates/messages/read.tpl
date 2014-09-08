            {capture name='t_from_user_url'}user/{$message.from.user_hash}{/capture}
            {capture name='t_msg_reply_url'}message/send/{$message.hash}{/capture}
            {capture name='t_msg_delete_url'}message/delete/{$message.hash}{/capture}

            {if $message.encrypted == TRUE}
                {capture name='t_message'}<pre>{$message.message|escape:"html":"UTF-8"}</pre>{/capture}
            {else}
                {capture name='t_message'}{$message.message|escape:"html":"UTF-8"}{/capture}
            {/if}

            <div class="col-md-9" id="read-message">
                <h2>View Message</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <div class="row-fluid">
                    <div class="col-xs-12">
                        <div class="col-xs-2"><strong>From</strong></div>
                        <div class="col-xs-7">{url type="anchor" url=$smarty.capture.t_from_user_url text=$message.from.user_name|escape:"html":"UTF-8" attr=''}</div>
                    </div>
            
                    <div class="col-xs-12">
                        <div class="col-xs-2"><strong>Subject</strong></div>
                        <div class="col-xs-7">{$message.subject|escape:"html":"UTF-8"}</div>
                    </div>
            
                    <div class="col-xs-12">
                        <div class="col-xs-2"><strong>Time</strong></div>
                        <div class="col-xs-7">{$message.time_f}</div>
                    </div>

                    {if $message.remove_on_read == TRUE}
                    <div class="col-xs-12">
                        <div class="col-xs-4">This message will now self-destruct..</div>
                    </div>
                    {/if}

                    <div class="col-xs-12">
                        <div class="col-xs-2"></div>
                        <div class="col-xs-9"><br />{$smarty.capture.t_message}</div>
                    </div>
                </div>

                <div class="">&nbsp;</div>

                <div class="form-group">
                    <label class="control-label col-sm-2 col-lg-2 col-md-2" for="submit"></label>
                    <div class="col-sm-5 col-lg-5 col-md-5">
                        <p align="center">
                            {url type="anchor" url="inbox" text="Back" attr='class="btn btn-default"'}
                            {url type="anchor" url=$smarty.capture.t_msg_reply_url text="Reply" attr='class="btn btn-primary"'}
                            {url type="anchor" url=$smarty.capture.t_msg_delete_url text="Delete" attr='class="btn btn-danger"'}
                        </p>
                    </div>
                </div>
            </div>
