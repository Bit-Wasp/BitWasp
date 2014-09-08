            <div class="col-md-9" id="registration-tokens">

                <h2>{url type="anchor" url='admin/users' text='Back' attr='class="btn btn-default"'} Registration Invites</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <legend>Create Registration Invites</legend>
                {form method="open" action='admin/tokens' attr='class="form-horizontal"'}
                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="col-xs-2 control-label" for="user_role">Details:</label>
                            <div class="col-xs-4">
                                <div class="input-group">
                                    <span class="input-group-addon"><i>Role</i></span>
                                    <select class="form-control" id="user_role" name="user_role">
                                        <option value=""></option>
                                        <option value="1">Buyer</option>
                                        <option value="2">Vendor</option>
                                        <option value="3">Administrator</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xs-4">
                                <div class="input-group">
                                    <span class="input-group-addon"><i>Cost</i></span>
                                    <input type='text' class='form-control' name='entry_payment' value='default' />
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-12 col-md-10 col-md-offset-2">{form method="form_error" field='user_role'}{form method="form_error" field='entry_payment'}</div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="col-xs-2 control-label">Comment:</label>
                            <div class="col-xs-8">
                                <input type="text" name="token_comment" value="" class="form-control" placeholder="Optional. This will not be shown to the user." />
                            </div>
                        </div>
                        <div class="col-xs-12"></div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-sm-2 col-lg-2 col-md-2" for="submit"></label>
                        <div class="col-sm-5 col-lg-5 col-md-5">
                            <p align="center">
                                <input type="submit" name="create_token" value="Create" class="btn btn-primary" />
                            </p>
                        </div>
                    </div>
                </form>

                <div class="col-xs-12">&nbsp;</div>

                {if $tokens == TRUE}
                <table class='table' cellspacing='0'>
                    <thead>
                    <tr>
                        <th>Link</th>
                        <th>Delete</th>
                        <th>Role</th>
                        <th>Fee</th>
                        <th>Comment</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$tokens item=token}
                    {capture name="t_registration_url"}register/{$token.token_content}{/capture}
                        {form method="open" action="admin/user_tokens" attr=''}
                        <tr>
                            <td>{url type="anchor" url=$smarty.capture.t_registration_url text="Right click to copy" attr='class="btn btn-default btn-sm"'}</td>
                            <td><input type="submit" name="delete_token" value="Delete Token" class="btn btn-danger btn-sm" />
                                <input type="hidden" name="delete_token_content" value="{$token.token_content}" />
                            <td>{$token.role}</td>
                            <td>{$coin.symbol} {$token.entry_payment|escape:"html":"UTF-8"}</td>
                            <td>{$token.comment|escape:"html":"UTF-8"}</td>
                        </tr>
                        </form>
                    {/foreach}
                    </tbody>
                </table>
                {/if}

            </div>