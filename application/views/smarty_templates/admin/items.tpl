            <div class="col-md-9" id="admin-items-panel">
                {$nav}

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <div class="col-xs-12">

                    <div class="row">
                        <div class="col-xs-6"><strong>Item Count</strong></div>
                        <div class="col-xs-6">{$item_count}</div>
                    </div>

                    <div class="row">
                        <div class="col-xs-6"><strong>Fees Configuration</strong></div>
                        <div class="col-xs-6">{url type="anchor" url="admin/items/fees" text="Configure" attr=''}</div>
                    </div>

                    <div class="row">
                        <div class="col-xs-6"><strong>Trusted vendor settings</strong></div>
                        <div class="col-xs-6">{url type="anchor" url="admin/trusted_user" text="Configure" attr=''}</div>
                    </div>
                </div>
            </div>
