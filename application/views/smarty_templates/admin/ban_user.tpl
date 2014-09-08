            {capture name="t_ban_user_url"}admin/ban_user/{$user.user_hash}{/capture}
            {capture name="t_user_url"}user/{$user.user_hash}{/capture}

            <div class="col-xs-9" id="admin_ban_user">
                <h2>Ban User</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {form method="open" action=$smarty.capture.t_ban_user_url attr='class="form-horizontal"'}
                    <p>Please confirm you wish to flag {$user.user_name|escape:"html":"UTF-8"} as {if $user.banned == '1'}un{/if}banned.</p>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-3" for="ban_user">Are you sure?</label>
                            <div class="col-xs-7">
                                <label class="radio-inline">
                                    <input type='radio' name='ban_user' value='0' /> No
                                </label>
                                <label class="radio-inline">
                                    <input type='radio' name='ban_user' value='1' /> Yes
                                </label>
                            </div>
                        </div>
                        <span class="col-xs-9 col-xs-offset-3">{form method="form_error" field='ban_user'}</span>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="submit"></label>
                            <div class="col-xs-5">
                                <p align="center">
                                    <input type='submit' name="submit_ban_toggle" value="Submit" class="btn btn-primary">
                                    {url type="anchor" url=$smarty.capture.t_user_url text='Cancel' attr='title="Cancel" class="btn btn-default"'}
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>