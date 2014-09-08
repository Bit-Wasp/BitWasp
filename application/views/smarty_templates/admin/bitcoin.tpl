            <div class="col-md-9" id="admin-panel">

                {$nav}

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {if is_array($bitcoin_info) == TRUE}
                <div class="row">
                    <div class="col-xs-6"><strong>{$coin.name} Status</strong></div>
                    <div class="col-xs-6">{$coin.name}d is currently running{if $bitcoin_info.testnet == TRUE} <b>in the testnet</b>{/if}</div>
                </div>

                <div class="row">
                    <div class="col-xs-6"><strong>{$coin.name} Version</strong></div>
                    <div class="col-xs-6">{$bitcoin_info.version}</div>
                </div>

                {else}
                <div class="row">
                    <div class="col-xs-6"><strong>{$coin.name} Status</strong></div>
                    <div class="col-xs-6">Unable to make an outbound connection to the {strtolower($coin.name)} daemon.</div>
                </div>
                {/if}

                <div class="row">
                    <div class="col-xs-6"><strong>Use A {$coin.name} Price Index?</strong></div>
                    <div class="col-xs-6">{if $bitcoin_index == ''}Disabled{else}{$bitcoin_index}{/if}</div>
                </div>

                <div class="row">
                    <div class="col-xs-6"><strong>Key Usage</strong></div>
                    <div class="col-xs-6">Used {$key_usage_count} times. {url type="anchor" url="admin/key_usage" text="View Usage" attr=""}</div>
                </div>

            </div>
