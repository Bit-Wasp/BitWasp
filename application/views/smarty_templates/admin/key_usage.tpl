            <div class="col-md-9" id="admin-key-usage">

                <h3>Key Usage</h3>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <div class='col-xs-12 col-md-10 col-md-offset1'>
                    {if $count > 0}
                    <div class="panel panel-success">
                        <div class="panel-heading">Created Addresses: {$count}</div>
                        <div class="panel-body">
                            <table class="table table-condensed">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Usage</th>
                                    <th>Address</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach from=$records item=record}
                                    <tr>
                                        <td>{$record.iteration}</td>
                                        <td>{if $record.usage == 'order'}
                                                {capture name="t_order_url"}admin/order/{$record.order_id}{/capture}
                                                {capture name="t_order_name"}Order #{$record.order_id}{/capture}
                                                {url type="anchor" url=$smarty.capture.t_order_url text=$smarty.capture.t_order_name attr=''}
                                            {else}
                                                {capture name="t_user_url"}user/{$record.fees_user_hash}{/capture}
                                                {url type="anchor" url=$smarty.capture.t_user_url text="Registration Fee" attr=''}
                                            {/if}</td>
                                        <td>{$record.address}</td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {else}
                    <div class="panel panel-danger">
                        <div class="panel-heading">No addresses created yet.</div>
                    </div>
                    {/if}
                </div>
            </div>
