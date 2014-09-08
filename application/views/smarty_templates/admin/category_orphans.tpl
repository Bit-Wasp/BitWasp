            {capture name="t_action_url"}admin/category/orphans/{$category.hash}{/capture}
            <div class="col-xs-9" id="admin_category_orphans">
                <h2>Fix Orphans</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <p>Deleting this category will orphan <?php echo $list; ?>, please select another category to contain them.</p>

                {form method="open" action="admin/items/fees" attr='class="form-horizontal"'}
                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="col-xs-2 col-xs-offset-1">New Category</label>
                            <div class="col-xs-4">
                                <select name='category_id' class="form-control" autocomplete='off'>
                                    <option value=''></option>
                                    {if $allow_root == TRUE}<option value='0'>Root Category</option>{/if}
                                    {foreach from=$categories item=cat}
                                        <option value='{$cat.id}'>{$cat.name|escape:"html":"UTF-8"}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="col-xs-9 col-xs-offset-3"}{form method="form_error" field='category_id'}</div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="submit"></label>
                            <div class="col-xs-5">
                                <p align="center">
                                    <input type="submit" value="Update" class="btn btn-primary" />
                                    {url type="anchor" url="admin/edit/items" text="Cancel" attr='class="btn"'}
                                </p>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
