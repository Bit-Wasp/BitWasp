            <div class="col-md-9" id="account_email_address">

                <h2>{if $action_type == 'new'}Set{else}Update{/if} Email Address</h2>

                <p align="justify">{if $action_type == 'new'}
                    If you wish you can register an email address with your account, to receive updates about your orders, and notifications from the site.
                {else}
                    Use this form if you wish to change your email to something else.
                {/if}
                An email will be sent to the address, and will need to follow the activation link, or manually enter the given details to confirm this change.
                </p>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {if count($pending_verification) > 0}
                {form method="open" action="accounts/email" attr=""}
                    {form method="validation_errors"}
                <div class='col-xs-10 col-xs-offset-1'>
                    <div class="panel panel-default">
                        <div class="panel-heading">Pending Verification: </div>

                        <table class="table">
                            <tbody>
                            {foreach from=$pending_verification item=verification_record}

                            <tr>
                                <td>{$verification_record.email_address|escape:"html":"UTF-8"}</td>
                                <td></td>
                                <td>expires {format_time ts=$verification_record.expire_time}</td>
                                <td><button type="submit" class="btn btn-default btn-xs" name="delete_request[{$verification_record.id}][]" value='1' title="delete"><i class="glyphicon glyphicon-trash"></i></button>

                            </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                </div>
                </form>
                {/if}

                {form method="open" action="accounts/email" attr='class="form-horizontal"'}
                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-3" for="email_address">Email Address</label>
                            <div class="col-xs-6">
                                <input type="text" class="form-control" name="email_address" id="email_address" value="" />
                            </div>
                        </div>
                        <div class="col-xs-8 col-xs-offset-3">{form method='form_error' field='email_address'}</div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-3" for="password">Password:</label>
                            <div class="col-xs-6">
                                <input type="password" name="password" id="password" class="form-control" value="">
                            </div>
                        </div>
                        <div class="col-xs-9 col-xs-offset-3">{form method='form_error' field='password'}</div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-2" for="submit"></label>
                        <div class="col-xs-5">
                            <p align="center">
                                <input type="submit" name="submit_new_email_address" value="Submit" class="btn btn-primary" />
                                {url type="anchor" url="account" text="Cancel" attr='title="Cancel" class="btn btn-default"'}
                            </p>
                        </div>
                    </div>

                </form>
            </div>
