
            <div class="col-md-9" id="send-message">
                <h2>Send Message</h2>

                {assign var="defaultMessage" value="Enter your message below: "}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                {form method="open" action=$action_uri attr=['class'=>'form-horizontal', 'name' => 'sendMessageForm']}
                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="recipient">Recipient</label>
                            <div class="col-xs-5">
                                <input type='text' class="form-control" id='recipient' name='recipient' value="{$to_name|escape:"html":"UTF-8"}" />
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="recipient"}</div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="subject">Subject</label>
                            <div class="col-xs-5">
                                <input type='text' class="form-control" id='subject' name='subject' value="{if isset($subject) == TRUE}{$subject|escape:"html":"UTF-8"}{/if}" />
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="subject"}</div>
                    </div>

                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="message">Message</label>
                            <div class="col-xs-5">
                                <textarea name="message" class="form-control" id='message' rows='6'>{form method="set_value" field="message"}</textarea>
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="message"}</div>
                    </div>


                    <div class="form-group">
                        <div class="col-xs-12">
                            <label class="control-label col-xs-2" for="delete_on_read">Delete After Reading?</label>
                            <div class="col-xs-5">
                                <div class="checkbox-inline">
                                    <label class="checkbox inline">
                                        <input type='checkbox' id="delete_on_read" name='delete_on_read' value='1' />
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-10 col-xs-offset-2">{form method="form_error" field="delete_on_read"}</div>
                    </div>

                    <input type='hidden' name='public_key' style='display:none;' value='{$public_key|escape:"html":"UTF-8"}'/>

                    <div class="form-group">
                        <label class="control-label col-sm-2 col-lg-2 col-md-2" for="submit"></label>
                        <div class="col-sm-5 col-lg-5 col-md-5">
                            <p align="center">
                                <input type='submit' class="btn btn-primary" value="Send" onclick='messageEncrypt()' />
                                {url type="anchor" url="inbox" text="Cancel" attr='class="btn btn-default"'}
                            </p>
                        </div>
                    </div>
                </form>
            </div>