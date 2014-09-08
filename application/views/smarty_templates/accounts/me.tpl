            {capture name='t_profile_url'}user/{$user.user_hash}{/capture}

            <div class="col-md-9" id="own-account">

                <div class="row">
                    <div class="col-md-12 btn-group">
                       <h2>{$user.user_name|escape}
                           <div class='pull-right'>
                               {url type="anchor" url="account/edit" text="Edit" attr='class="btn btn-default"'}
                               {if $request_emails == TRUE}
                                   {if $user.email_address == NULL}
                                       {url type="anchor" url="accounts/email" text="Set Email" attr='class="btn btn-default"'}
                                   {else}
                                       {url type="anchor" url="accounts/email" text="Update Email" attr='class="btn btn-default"'}
                                   {/if}
                               {/if}
                               {url type="anchor" url="accounts/password" text="Change Password" attr='class="btn btn-default"'}
                           </div>
                       </h2>
                    </div>
                </div>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}
                <div class="col-xs-12">&nbsp;</div>

                <div class="row">
                    <div class="col-md-4"><strong>Profile URL</strong></div>
                    <div class="col-md-8">{url type="anchor" url=$smarty.capture.t_profile_url text="" attr=''}</div>
                </div>

                <div class="row">
                    <div class="col-md-4"><strong>Location</strong></div>
                    <div class="col-md-8">{$user.location_f}</div>
                </div>

                <div class="row">
                    <div class="col-md-4"><strong>Local Currency</strong></div>
                    <div class="col-md-8">{$user.currency.name} ({$user.currency.symbol})</div>
                </div>

                <div class="row">
                    <div class="col-md-4"><strong>Display Login Activity?</strong></div>
                    <div class="col-md-8">{if $user.display_login_time == '1'}Enabled{else}Disabled{/if}</div>
                </div>


                <div class="row">&nbsp;</div>
                <legend>Private Settings </legend>

                {if $request_emails == TRUE}
                <div class="row">
                    <div class="col-md-4"><strong>Email Address</strong></div>
                    <div class="col-md-8">{if $user.email_address == ''}none{else}{$user.email_address|escape:"html":"UTF-8"}{/if}</div>
                </div>

                <div class="row">
                    <div class="col-md-4"><strong>Forward messages to email?</strong></div>
                    <div class="col-md-8">{if $user.email_updates == TRUE AND (($user.email_address == '')==FALSE) }Enabled{else}Disabled{/if}</div>
                </div>
                {/if}

                {if $user.user_role !== 'Admin'}
                <div class="row">
                    <div class="col-md-4"><strong>Bitcoin Public Keys</strong></div>
                    <div class="col-md-8">
                        {if $bip32 == FALSE}Not set up - {url type="anchor" url="bip32" text="do so now!" attr=""}
                        {else}Provider: {$bip32.provider} - {url type="anchor" url="bip32" text="Settings" attr=""}
                        {/if}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4"><strong>{if $user.user_role == 'Vendor'}Payout{else}Refund{/if} Address</strong></div>
                    <div class="col-md-8">
                        {if is_array($payout)}
                            {$payout.address|escape:"html":"UTF-8"} - {url type="anchor" url="accounts/payout" text="Settings" attr=''}
                        {else}
                            Not set up - {url type="anchor" url="accounts/payout" text="do so now!" attr=''}
                        {/if}
                    </div>
                </div>
                {/if}


                <div class="row">&nbsp;</div>
                <legend>Security</legend>

                <div class="row">
                    <div class="col-md-4"><strong>Two Factor Authentication</strong></div>
                    <div class="col-md-8">
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
                    <div class="col-md-4"><strong>PGP Fingerprint</strong></div>
                    <div class="col-md-8">{substr($user.pgp.fingerprint, 0, -8)}<b>{substr($user.pgp.fingerprint,-8)}</b></div>
                </div>

                <div class="row">
                    <div class="col-md-4"><strong>Force PGP Messages?</strong></div>
                    <div class="col-md-8">{if $user.force_pgp_messages == '1'}Enabled{else}Disabled{/if}</div>
                </div>

                <div class="row">
                    <div class="col-md-4"><strong>Block non-PGP messages?</strong></div>
                    <div class="col-md-8">{if $user.block_non_pgp == '1'}Enabled{else}Disabled{/if}</div>
                </div>
                {else}
                <div class="row">
                    <div class="col-md-4"><strong>PGP Features</strong></div>
                    <div class="col-md-8">{url type="anchor" url="pgp/add" text="Add a PGP key" attr=""} to enable features such as two-factor authentication, or automatic encryption of messages.</div>
                </div>
                {/if}

            </div>
