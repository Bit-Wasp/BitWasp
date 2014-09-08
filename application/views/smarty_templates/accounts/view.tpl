            {capture name='t_message_user_url'}message/send/{$user.user_hash}{/capture}
            {capture name='t_ban_user_txt'}{if $user.banned == '0'}Ban User{else}Unban User{/if}{/capture}
            {capture name='t_ban_user_page'}admin/ban_user/{$user.user_hash}{/capture}

            <div class="col-md-9" id="view-account">
                <div class="row">

                    {assign var="defaultMessage" value=""}
                    {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                    <div class="row">
                        <div class="col-md-9 btn-group">
                            <h2>
                            {if $current_user.logged_in == TRUE AND $current_user.user_id !== $user.id}
                            {url type="anchor" url=$smarty.capture.t_message_user_url text="Message" attr='class="btn btn-default"'}
                            {/if}
                            {if $current_user.user_role == 'Admin' && $user.user_role !== 'Admin'}
                            {url type="anchor" url=$smarty.capture.t_ban_user_page text=$smarty.capture.t_ban_user_txt attr='class="btn btn-default"'}
                            {/if}
                            {$user.user_name|escape:"html":"UTF-8"} {if $user.banned == TRUE}<small>(banned)</small>{/if}</h2>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3"><strong>Location</strong></div>
                        <div class="col-md-7">{$user.location_f}</div>
                    </div>

                    <div class="row">
                        <div class="col-md-3"><strong>Registered</strong></div>
                        <div class="col-md-7">{$user.register_time_f}</div>
                    </div>

                    {if $user.display_login_time == '1'}
                    <div class="row">
                        <div class="col-md-3"><strong>Last Activity</strong></div>
                        <div class="col-md-7">{$user.login_time_f}</div>
                    </div>
                    {/if}

                    <div class="row">
                        <div class="col-md-3"><strong>Average Rating</strong></div>
                        <div class="col-md-7">{$average_rating}</div>
                    </div>

                    <div class="row">
                        <div class='col-md-3'><strong>Completed Orders</strong></div>
                        <div class='col-md-7'>{$user.completed_order_count}</div>
                    </div>

                    {if $reviews == TRUE}
                        <div class="well" style="background:white;">
                            <legend>Recent Reviews</legend>
                            {capture name='t_user_all_reviews_url'}reviews/view/user/{$user.user_hash}{/capture}
                            {capture name='t_all_reviews_str'}[All Reviews: {$review_count.all}]{/capture}

                            {capture name='t_user_p_reviews_url'}reviews/view/user/{$user.user_hash}/0{/capture}
                            {capture name='t_p_reviews_str'}[Positive: {$review_count.positive}]{/capture}

                            {capture name='t_user_d_reviews_url'}reviews/view/user/{$user.user_hash}/1{/capture}
                            {capture name='t_d_reviews_str'}[Disputed: {$review_count.disputed}]{/capture}

                            {url type="anchor" url=$smarty.capture.t_user_all_reviews_url text=$smarty.capture.t_all_reviews_str attr=""}
                            {url type="anchor" url=$smarty.capture.t_user_p_reviews_url text=$smarty.capture.t_p_reviews_str attr=""}
                            {url type="anchor" url=$smarty.capture.t_user_d_reviews_url text=$smarty.capture.t_d_reviews_str attr=""}

                            {foreach from=$reviews item=review}
                                <hr>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-md-6">
                                            {foreach from=$review.rating key=quality item=rating}
                                                <div class="col-md-12">
                                                    <div class="col-md-7">{ucfirst($quality)}</div>
                                                    <div class="col-md-5">{rating rating=$rating}</div>
                                                </div>
                                            {/foreach}
                                            <div class="col-md-12">
                                                <div class="col-md-7">Average</div>
                                                <div class="col-md-5">{rating rating=$review.average_rating}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6"></div>

                                        <span class="pull-right">{$review.time_f}</span>
                                        <p>{$review.comments|escape:"html":"UTF-8"}</p>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    {/if}

                    {if isset($items) && count($items) > 0}
                    <div class='well' style="background:white">
                        <legend>Latest Listings</legend>
                        {$c = 0}
                        {$last = count($items)-1}
                        {foreach from=$items item=item}
                        {capture name="t_item_url"}item/{$item.hash}{/capture}
                            {$cal = $c%4}
                        {if $cal == 0}<div class="row">{/if}
                        <div class='col-xs-3'>{url type="anchor" url=$smarty.capture.t_item_url text=$item.name|escape:"html":"UTF-8" attr=""}</div>
                        {if $cal == 3 OR $c == $last }</div>{/if}
                            {$c = $c+1}
                        {/foreach}
                    </div>
                    {/if}

                    {if isset($user.pgp) == TRUE}
                    <div class="row">
                        <div class="col-xs-3"><strong>PGP Fingerprint</strong></div>
                        <div class="col-xs-7">{substr($user.pgp.fingerprint, 0, -8)}<b>{substr($user.pgp.fingerprint,-8)}</b></div>
                    </div>

                    <div class="row">
                        <div class="col-md-3"><strong>PGP Public Key</strong></div>
                        <div class="col-xs-9">
                            <pre id="publicKeyBox" class="well">{$user.pgp.public_key|escape:"html":"UTF-8"}</pre>
                        </div>
                    </div>
                    {/if}
                </div>
            </div>
