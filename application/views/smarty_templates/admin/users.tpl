            <div class="col-md-9" id="admin-users-panel">

                {$nav}

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <div class="row">
                    <div class="col-xs-6"><strong>User Count</strong></div>
                    <div class="col-xs-6">{$user_count} ({url type="anchor" url="admin/users/list" text="User list" attr=''})</div>
                </div>

                <div class="row">
                    <div class="col-xs-6"><strong>Request Emails</strong></div>
                    <div class="col-xs-6">{if $config.request_emails == TRUE}Enabled{else}Disabled{/if}</div>
                </div>

                <div class="row">
                    <div class='col-xs-6'><strong>Session Timeout</strong></div>
                    <div class='col-xs-6'>{$config.login_timeout|escape:"html":"UTF-8"} minutes</div>
                </div>

                <div class="row">
                    <div class='col-xs-6'><strong>Captcha Length</strong></div>
                    <div class='col-xs-6'>{$config.captcha_length|escape:"html":"UTF-8"} characters</div>
                </div>

                <div class="row">
                    <div class='col-xs-6'><strong>Registration Allowed?</strong></div>
                    <div class='col-xs-6'>
                        {if $config.registration_allowed == TRUE}
                            Enabled
                        {else}
                            Disabled
                        {/if}
                    </div>
                </div>

                <div class="row">
                    <div class='col-xs-6'><strong>Vendor Registration Allowed?</strong></div>
                    <div class='col-xs-6'>
                        {if $config.vendor_registration_allowed == TRUE}
                            Enabled
                        {else}
                            Disabled
                        {/if}
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-6"><strong>Vendor Registration Fee:</strong></div>
                    <div class="col-xs-6">
                        {if $config.entry_payment_vendor > 0}
                            {$coin.symbol} {$config.entry_payment_vendor|escape:"html":"UTF-8"}
                        {else}
                            Not Required
                        {/if}
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-6"><strong>Buyer Registration Fee:</strong></div>
                    <div class="col-xs-6">
                        {if $config.entry_payment_buyer > 0}
                            {$coin.symbol} {$config.entry_payment_buyer|escape:"html":"UTF-8"}
                        {else}
                            Not Required
                        {/if}
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-6"><strong>Registration Tokens</strong></div>
                    <div class="col-xs-6">{url type="anchor" url="admin/user_tokens" text="Manage" attr=''}</div>
                </div>

                <div class="row">
                    <div class='col-xs-6'><strong>Encrypted PM's</strong></div>
                    <div class='col-xs-6'>
                        {if $config.encrypt_private_messages == TRUE}
                            Enabled
                        {else}
                            Disabled
                        {/if}
                    </div>
                </div>

                <div class="row">
                    <div class='col-xs-6'><strong>Force Vendor PGP?</strong></div>
                    <div class='col-xs-6'>
                        {if $config.force_vendor_pgp == TRUE}
                            Enabled
                        {else}
                            Disabled
                        {/if}
                    </div>
                </div>
            </div>
