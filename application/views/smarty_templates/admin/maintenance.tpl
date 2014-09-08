            <div class="col-md-9" id="admin_maintenance">

                <h2>Maintenance Settings</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <p>You can use this panel to put the website into maintenance mode. Guests will not be able to view the site, and non-administrative users will be logged out. Once re-enabled, your previous settings will be restored.</p>

                <p>If maintenance mode has been triggered by the bitcoin daemon, or due to an alert reported on github, a message will be disabled informing you of the reason and giving advice.</p>

                {form method="open" action="admin/maintenance" attr='class="form-horizontal"'}
                    <div class="panel panel-{if $config.maintenance_mode == TRUE}danger{else}success{/if}">
                        <div class="panel-heading">Current Status: {if $config.maintenance_mode == TRUE}site offline{else}site online{/if}</div>
                        <div class="panel-body">

                            <div class="form-group">
                                <div class="col-xs-12">
                                    <div class='col-xs-1'></div>
                                    <div class='col-xs-8'>
                                        <div class="input-group">
                                            <span class="input-group-addon"><i>Action:</i></span>
                                            <select name='maintenance_mode' class="form-control" autocomplete='off'>
                                                <option value=''></option>
                                                <option value='0'{if $config.maintenance_mode == '1'} selected="selected"{/if}>Deactivate maintenance mode</option>
                                                <option value='1'{if $config.maintenance_mode == '0'} selected="selected"{/if}>Activate maintenance mode</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class='col-xs-3'><input type='submit' name='set_maintenance_mode' value='Update' class="btn btn-primary" /></div>
                                </div>
                                <div class="col-xs-12 col-md-11 col-md-offset-1">{form method="form_error" field="maintenance_mode"}</div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>