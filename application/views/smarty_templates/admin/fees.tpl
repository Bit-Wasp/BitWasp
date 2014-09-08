            <div class="col-md-9" id="admin_fees_panel">

                <h2>{url type="anchor" url="admin/items" text="Back" attr='class="btn btn-default"'} Fees Configuration</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}
                <div class="col-xs-12">&nbsp;</div>

                {form method="open" action="admin/items/fees" attr='class="form-horizontal"'}
                    <h3>Basic Settings</h3>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-3" for="minimum_fee">Minimum Fee:</label>
                            <div class="col-xs-7">
                                <div class="input-group">
                                    <span class="input-group-addon"><i>{$coin.symbol}</i></span>
                                    <input type="text" name="minimum_fee" id="minimum_fee" class="form-control" value="{$config.minimum_fee}" />
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-9 col-xs-offset-3">{form method="form_error" field="minimum_fee"}</div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-3" for="default_rate">Default Rate</label>
                            <div class="col-xs-7">
                                <div class="input-group">
                                    <span class="input-group-addon"><i>%</i></span>
                                    <input type="text" class="form-control" name="default_rate" id="default_rate" value='{$config.default_rate|escape:"html":"UTF-8"}' />
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-9 col-xs-offset-3">{form method="form_error" field="default_rate"}</div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-3" for="escrow_rate">Escrow Rate</label>
                            <div class="col-xs-7">
                                <div class="input-group">
                                    <span class="input-group-addon"><i>%</i></span>
                                    <input type='text' class="form-control" name='escrow_rate' id="escrow_rate" value='{$config.escrow_rate|escape:"html":"UTF-8"}' />
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-9 col-xs-offset-3">{form method="form_error" field="escrow_rate"}</div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-2" for="submit"></label>
                        <div class="col-xs-5">
                            <p align="center">
                                <input type="submit" name="update_config" value="Update" class="btn btn-primary" />
                                {url type="anchor" url="admin/items" text="Cancel" attr='title="Cancel" class="btn btn-default"'}
                            </p>
                        </div>
                    </div>
                </form>

                <h3>Fee Ranges</h3>
                {if $fees == TRUE}
                    <div class="col-xs-12">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Lower Limit</th>
                                <th>Upper Limit</th>
                                <th>% Rate</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach from=$fees item=fee}
                                {form method="open" action='admin/items/fees'}
                                <tr>
                                    <td>{$fee.low|escape:"html":"UTF-8"}</td>
                                    <td>{$fee.high|escape:"html":"UTF-8"}</td>
                                    <td>{$fee.rate|escape:"html":"UTF-8"}</td>
                                    <td><input type="submit" class="form-control btn btn-default" name="delete_rate[{$fee.id}]" value="Delete"  /></td>
                                </tr>
                                </form>
                            {/foreach}
                            </tbody>
                        </table>
                    </div>
                {/if}

                {form method="open" action="admin/items/fees" attr='class="form-horizontal"'}
                    <h4>Create a Fee Range:</h4>

                    <div class="form-group">
                        <label class="control-label col-xs-3" for="lower_limit">Lower Limit:</label>
                        <div class="col-xs-7">
                            <div class="input-group">
                                <span class="input-group-addon"><i>{$coin.symbol}</i></span>
                                <input type="text" name="lower_limit" id="lower_limit" class="form-control" value="" />
                            </div>
                        </div>
                        <span class="help-inline">{form method="form_error" field="lower_limit"}</span>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-3" for="upper_limit">Upper Limit</label>
                        <div class="col-xs-7">
                            <div class="input-group">
                                <span class="input-group-addon"><i>BTC</i></span>
                                <input type="text" class="form-control" name="upper_limit" id="upper_limit" value='' />
                            </div>
                        </div>
                        <span class="help-inline">{form method="form_error" field="upper_limit"}</span>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-3" for="percentage_fee">% Fee</label>
                        <div class="col-xs-7">
                            <div class="input-group">
                                <span class="input-group-addon"><i>%</i></span>
                                <input type='text' class="form-control" name='percentage_fee' id="percentage_fee" value='' />
                            </div>
                        </div>
                        <span class="help-inline">{form method="form_error" field="percentage_fee"}</span>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-2" for="submit"></label>
                        <div class="col-xs-5">
                            <p align="center">
                                <input type='submit' name='create_fee' value='Add' class='btn btn-primary' />
                            </p>
                        </div>
                    </div>
                </form>

            </div>
