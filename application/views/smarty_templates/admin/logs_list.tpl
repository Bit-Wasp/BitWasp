            <div class="col-md-9" id="admin-logs-list">

                {$nav}

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {if $logs == TRUE}
                <table class='table'>
                    <thead>
                    <tr>
                        <th>Time</th>
                        <th>Level</th>
                        <th>Title</th>
                        <th>Called By</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$logs item=log}
                    {capture name="t_log_url"}admin/logs/{$log.hash}{/capture}
                        <tr>
                            <td>{$log.time_f}</td>
                            <td>{$log.info_level|escape:"html":"UTF-8"}</td>
                            <td>{url type="anchor" url=$smarty.capture.t_log_url text=$log.title|escape:"html":"UTF-8" attr=''}</td>
                            <td>{$log.caller|escape:"html":"UTF-8"}</td>
                            <td></td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                {else}
                    There are no logs at this time.
                {/if}

            </div>