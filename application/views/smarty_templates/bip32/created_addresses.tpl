
                <p align="justify">Each time a new order is created, a new bitcoin address will show up here, as well as the identifier for this address. Note that funds are never sent to these addresses, but their public keys are used to make the  address.</p>

                <div class="panel panel-default">
                    <div class="panel-heading">Created Addresses: {count($usage)}</div>
                    {if count($usage) > 0}
                    <ul class="list-group">
                        {foreach from=$usage item=record}
                            {capture name="t_order_url"}{if $current_user.user_role == 'Buyer'}purchases{else}orders{/if}/details/{$record.order_id}{/capture}
                        <li class="list-group-item">M/0'/0/{$record.key_index} - {$record.address} for {url type="anchor" url=$smarty.capture.t_order_url text="order {$record.order_id}" attr=""} </li>
                        {/foreach}
                    </ul>
                    {/if}
                </div>
