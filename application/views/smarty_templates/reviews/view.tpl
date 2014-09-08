            {capture name="t_review_a_url"}reviews/view/{$review_type}/{$subject_hash}{/capture}
            {capture name="t_review_a_str"}[All Reviews: {$review_count.all}]{/capture}
            {capture name="t_review_p_url"}reviews/view/{$review_type}/{$subject_hash}/0{/capture}
            {capture name="t_review_p_str"}[Positive: {$review_count.positive}]{/capture}
            {capture name="t_review_d_url"}reviews/view/{$review_type}/{$subject_hash}/1{/capture}
            {capture name="t_review_d_str"}[Disputed: {$review_count.disputed}]{/capture}

            <div class="col-md-9" id="view-reviews">

                <h2>{if is_string($disputed) == TRUE}
                        {if $disputed == '0'}Positive{else}Negative{/if} reviews
                    {else}Reviews
                    {/if} for {$name|escape:"html":"UTF-8"}</h2>

                {assign var="defaultMessage" value=""}
                {returnMessage defaultMessage="$defaultMessage" returnMessage="$returnMessage" class="$returnMessage_class"}

                <div class='row'>
                    <div class='col-md-12'>Average Rating: {$average}</div>

                    <div class='col-md-12'>
                        {url type="anchor" url=$smarty.capture.t_review_a_url text=$smarty.capture.t_review_a_str attr=''}
                        {url type="anchor" url=$smarty.capture.t_review_p_url text=$smarty.capture.t_review_p_str attr=''}
                        {url type="anchor" url=$smarty.capture.t_review_d_url text=$smarty.capture.t_review_d_str attr=''}
                    </div>
                </div>

                {if $search_reviews == TRUE}

                    <div class="well" style="background:white;">
                        <h4>Recent Reviews</h4>

                        {foreach from=$search_reviews item=review}
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-6">
                                        {foreach from=$review.rating key=quality item=rating}
                                            <div class="col-md-12">
                                                <div class="col-md-7">
                                                    {ucfirst($quality)}
                                                </div>
                                                <div class="col-md-5">
                                                    {for $var1=1 to $rating}<span class="glyphicon glyphicon-star"></span>{/for}{for $var=$var1 to 5}<span class="glyphicon glyphicon-star-empty"></span>{/for}
                                                </div>
                                            </div>
                                        {/foreach}
                                        <div class="col-md-12">
                                            <div class="col-md-7">
                                                Average
                                            </div>
                                            <div class="col-md-5">
                                                {for $var1=1 to $review.average_rating}<span class="glyphicon glyphicon-star"></span>{/for}{for $var=$var1 to 5}<span class="glyphicon glyphicon-star-empty"></span>{/for}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6"></div>

                                    <span class="pull-right">{$review.time_f}</span>
                                    <p>{$review.comments|escape:"html":"UTF-8"}</p>
                                </div>
                            </div>
                        {/foreach}
                    </div>

                {else}
                    {if $disputed == FALSE}
                        No reviews for {$name|escape:"html":"UTF-8"}
                    {else}
                        No {if $disputed == '0'}positive{else}negative{/if} reviews for {$name|escape:"html":"UTF-8"}.
                    {/if}
                {/if}

            </div>