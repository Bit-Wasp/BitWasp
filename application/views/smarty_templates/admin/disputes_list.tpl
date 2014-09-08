            <div class="col-md-9" id="admin-disputes-list">
                <h2>{url type="anchor" url="admin" text="Back" attr='class="btn btn-default"'} Disputes</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {if $disputes !== TRUE }
                <table class="table">
                    <thead>
                    <tr>
                        <th>Order</th>
                        <th>Disputing User</th>
                        <th>Issue</th>
                        <th>Other User</th>
                        <th>Last Update</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$disputes item=dispute}
{capture name="t_order_url"}admin/order/{$dispute.order_id}{/capture}
{capture name="t_order_str"}#{$dispute.order_id}{/capture}
{capture name="t_dispute_url"}admin/dispute/{$dispute.order_id}{/capture}
{capture name="t_dispute_str"}{substr($dispute.dispute_message|escape:"html":"UTF-8", 0, 100)}{/capture}
{capture name="t_disputing_user_url"}user/{$dispute.disputing_user.user_hash}{/capture}
{capture name="t_other_user_url"}user/{$dispute.other_user.user_hash}{/capture}
                        <tr>
                            <td>{url type="anchor" url=$smarty.capture.t_order_url text=$smarty.capture.t_order_str attr=""}</td>
                            <td>{url type="anchor" url=$smarty.capture.t_disputing_user_url text=$dispute.disputing_user.user_name|escape:"html":"UTF-8" attr=""}</td>
                            <td>{url type="anchor" url=$smarty.capture.t_dispute_url text=$smarty.capture.t_dispute_str attr=""}</td>
                            <td>{url type="anchor" url=$smarty.capture.t_other_user_url text=$dispute.other_user.user_name|escape:"html":"UTF-8" attr=""}</td>
                            <td>{$dispute.last_update_f}</td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                {else}
                    There are no disputes at this time.
                {/if}
            
            </div>
