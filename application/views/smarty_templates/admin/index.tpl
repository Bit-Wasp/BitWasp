            <div class="col-xs-9" id="admin-panel">
                {$nav}

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <div class="row">
                    <label class="control-label col-xs-6" for="status">Site Status</label>
                    <div class="col-xs-6">
                        <strong>
                            {if $config.maintenance_mode == TRUE}
                                maintenance mode
                            {else}
                                online
                            {/if}
                        </strong>
                    </div>
                </div>

                <div class='row'>
                    <label class="control-label col-xs-6" for="site_title">Site Title</label>
                    <div class='col-xs-6'>{$config.site_title|escape:"html":"UTF-8"}</div>
                </div>

                <div class="row">
                    <label class="control-label col-xs-6" for="site_description">Site Description</label>
                    <div class="col-xs-6">{$config.site_description|escape:"html":"UTF-8"}</div>
                </div>

                <div class="row">
                    <label class="control-label col-xs-6" for="location_list_source">Location List</label>
                    <div class="col-xs-6">{$config.location_list_source|escape:"html":"UTF-8"}</div>
                </div>

                <div class="row">
                    <label class="control-label col-xs-6" for="terms_of_service_toggle">Terms Of Service</label>
                    <div class="col-xs-6">{if $config.terms_of_service_toggle == TRUE}Enabled{else}Disabled{/if}</div>
                </div>

                <div class='row'>
                    <label class="control-label col-xs-6" for="allow_guests">Allow Guests to Browser?</label>
                    <div class='col-xs-6'>{if $config.allow_guests == TRUE}Enabled{else}Disabled{/if}</div>
                </div>

                {if isset($gpg) == TRUE}
                <div class="row">
                    <label class="control-label col-xs-6" for="gpg">GnuPG Version</label>
                    <div class="col-xs-6">{$gpg}</div>
                </div>
                {/if}

                {if $encrypt_private_messages == TRUE}
                <div class="row">
                    <label class="control-label col-xs-6" for="openssl_version">OpenSSL Version</label>
                    <div class="col-xs-6">{$openssl}</div>
                </div>

                <div class="row">
                    <label class="control-label col-xs-6" for="openssl_keysize">OpenSSL Keysize</label>
                    <div class="col-xs-6">{$config.openssl_keysize|escape:"html"}</div>
                </div>
                {/if}

                <div class="row">
                    <label class="control-label col-xs-6" for="global_proxy">Global Proxy</label>
                    <div class="col-xs-6">
                        {if $config.global_proxy_type == 'Disabled'}
                            Disabled
                        {else}
                            ({$config.global_proxy_type|escape:"html":"UTF-8"}) {$config.global_proxy_url|escape:"html":"UTF-8"}
                        {/if}
                    </div>
                </div>

            </div>
