            {capture name='t_profile_url'}user/{$user.user_hash}{/capture}

            <div class="col-md-9" id="own-account">

                <div class="row">
                    <div class="col-md-9 btn-group">
                        <h2>{url type="anchor" url="account/edit" text="Edit" attr='class="btn btn-default"'} {$user.user_name}</h2>
                    </div>
                </div>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" success="$success"}
                <div class="col-xs-12">&nbsp;</div>

                <div class="row">
                    <div class="col-md-3"><strong>Profile URL</strong></div>
                    <div class="col-md-8">{url type="anchor" url=$smarty.capture.t_profile_url text="" attr=''}</div>
                </div>

                {if $user.user_role == 'Vendor'}
                <div class="row">
                    <div class="col-md-3"><strong>Bitcoin Public Keys</strong></div>
                    <div class="col-md-7">{$public_key_count} available. {url type="anchor" url="accounts/public_keys" text="Click here to add more" attr=''}</div>
                </div>
                {/if}

                <div class="row">
                    <div class="col-md-3"><strong>Location</strong></div>
                    <div class="col-md-7">{$user.location_f}</div>
                </div>

                <div class="row">
                    <div class="col-md-3"><strong>Local Currency</strong></div>
                    <div class="col-md-7">{$user.currency.name} ({$user.currency.symbol})</div>
                </div>

                <div class="row">
                    <div class="col-md-3"><strong>Date Registered</strong></div>
                    <div class="col-md-7">{$user.register_time_f}</div>
                </div>

                <div class="row">
                    <div class="col-md-3"><strong>Display activity?</strong></div>
                    <div class="col-md-7">{if $user.display_login_time == '1'}Enabled{else}Disabled{/if}</div>
                </div>

                <div class="row">
                    <div class="col-md-3"><strong>Last Login</strong></div>
                    <div class="col-md-7">{$user.login_time_f}</div>
                </div>

                <div class="row">
                    <div class="col-md-3"><strong>Two Factor Authentication</strong></div>
                    <div class="col-md-7">
                        {if $two_factor_setting == TRUE}
                            {if $two_factor.totp == TRUE}
                                Enabled (Mobile App)
                            {else}
                                Enabled (PGP)
                            {/if}
                        {else}
                            Disabled
                        {/if}

                        {url type="anchor" url="account/two_factor" text="Configure" attr=""}
                    </div>
                </div>

                {if isset($user.pgp.public_key) == TRUE}
                <div class="row">
                    <div class="col-md-3"><strong>PGP Fingerprint</strong></div>
                    <div class="col-md-7">{substr($user.pgp.fingerprint, 0, -8)}<b>{substr($user.pgp.fingerprint,-8)}</b></div>
                </div>

                <div class="row">
                    <div class="col-md-3"><strong>Force PGP Messages?</strong></div>
                    <div class="col-md-7">{if $user.force_pgp_messages == '1'}Enabled{else}Disabled{/if}</div>
                </div>

                <div class="row">
                    <div class="col-md-3"><strong>Block non-PGP messages?</strong></div>
                    <div class="col-md-7">{if $user.block_non_pgp == '1'}Enabled{else}Disabled{/if}</div>
                </div>
                {else}
                <div class="row">
                    <div class="col-md-3"><strong>PGP Features</strong></div>
                    <div class="col-md-6">{url type="anchor" url="pgp/add" text="Add a PGP key" attr=""} to enable features such as two-factor authentication, or automatic encryption of messages.</div>
                </div>
                {/if}

            </div>
