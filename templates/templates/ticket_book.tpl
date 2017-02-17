{extends file="main.tpl"}
{block "head"}
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
{nocache}
	<script type="text/javascript">
		var guests_allowed = {$sale->guests_allowed};
		var guests_enabled = {$guests_enabled};
	</script>
{/nocache}
	<script type="text/javascript" src="{resource src='scripts/ticket_book.js'}"></script>
{/block}
{block "body"}
{nocache}
<p>{$description}</p>
<form action="{url mode='tickets'}" method="post" class="tickets" id="tickets">
	<input type="hidden" name="js" id="js" value="false" />
	<input type="hidden" name="sh" id="sh" value="{$session_hash}" />
	<fieldset id="primary_ticket" class="ticket even">
		<legend><strong>Primary</strong> Ticket</legend>
		<div>		
			<input type="hidden" name="p_crsid" id="p_crsid" value="{$user->crsid}" />
			<p>
				<label for="p_crsid">crsID<span>Your Cambridge <a href="http://www.ucs.cam.ac.uk/accounts/crsid">crsID</a>.</span></label>
				<span class="form-data">{$user->crsid} &mdash; <a href="{url mode='user' arg='logout'}">not you?</a></span>
			</p>
			<p{if isset($error[0]['fname'])} class="field-error"{/if}>
				<label for="p_fname">First Name<span>Your first name&mdash;not just initials</span></label>
				<input type="text" name="p_fname" id="p_fname" maxlength="32" value="{if isset($data[0]['fname'])}{$data[0]['fname']}{/if}" class="field" />
				<span class="error-message">First name cannot be blank!</span>
			</p>
			<p{if isset($error[0]['lname'])} class="field-error"{/if}>
				<label for="p_lname">Last Name<span>Your surname</span></label>
{if $sale->lname_lock}
				<input type="text" name="p_lname" id="p_lname" maxlength="32" disabled="disabled" value="{$sale->user['lname']}" class="field" />
{else}
				<input type="text" name="p_lname" id="p_lname" maxlength="32" value="{if isset($data[0]['lname'])}{$data[0]['lname']}{/if}" class="field" />
{/if}
				<span class="error-message">Last name cannot be blank!</span>
			</p>
			<p{if isset($error[0]['ticket_type'])} class="field-error"{/if}>
				<label>Ticket type<span>Click <a href="{url mode='faq'}#queuejump">here</a> for more details.</span></label>
				<span class="form-data">
{if $sale->committee_flag >= 1}
					<input type="radio" name="p_ticket_type" id="p_ticket_type_committee" value=0 checked="checked" /><label for="p_ticket_type_normal">Committee</label>
{else}
					<input type="radio" name="p_ticket_type" id="p_ticket_type_normal" value=0{if $sale->premium_flag} checked="checked"{else}{if isset($data[0]['ticket_type']) && $data[0]['ticket_type'] === 0} checked="checked"{/if}{/if} /><label for="p_ticket_type_normal">Normal</label>
					<input type="radio" name="p_ticket_type" id="p_ticket_type_queuejump" value=1{if $sale->premium_flag} disabled="disabled"{else}{if isset($data[0]['ticket_type']) && $data[0]['ticket_type'] === 1} checked="checked"{/if}{/if} /><label for="p_ticket_type_queuejump">QueueJump{if $sale->premium_flag} <em>(Sold out)</em>{/if}</label>
{/if}
				</span>
				<span class="error-message">Must select a ticket type!</span>
			</p>
			<p{if isset($error[0]['age_check'])} class="field-error"{/if}>
				<label>Will you be over 18?<span>&hellip;on the {$event_date}.</span></label>
				<span class="form-data">
					<input type="radio" name="p_age_check" id="p_age_check_true" value="true"{if isset($data[0]['age_check']) && $data[0]['age_check'] === true} checked="checked"{/if} /><label for="p_age_check_true">Yes</label>
					<input type="radio" name="p_age_check" id="p_age_check_false" value="false"{if isset($data[0]['age_check']) && $data[0]['age_check'] === false} checked="checked"{/if} /><label for="p_age_check_false">No</label>
				</span>
				<span class="error-message">Must be over 18 on the event!</span>
			</p>
		</div>
	</fieldset>
{if $sale->guests_allowed > 0}
	<fieldset id="g{$i}_ticket" class="ticket odd">
		<legend><strong>Guest</strong> Tickets</legend>
		<div>
			<p id="guest_explanation" class="note">Leave guest tickets blank if you do not want a guest.</p>
			<p class="note"><strong>Please note that you will not be able to add additional guests after you have confirmed your booking.</strong></p>
		</div>
	</fieldset>
{/if}
{if $sale->guests_allowed > 0}
{for $i=1 to $sale->guests_allowed}
	<fieldset id="g{$i}_ticket" class="ticket {if ($i % 2 == 0)}odd{else}even{/if}">
		<legend><strong>Guest {$i}</strong> Ticket</legend>
		<div>
			<p{if isset($error[$i]['fname'])} class="field-error"{/if}>
				<label for="g{$i}_fname">First Name<span>Their first name&mdash;not just initials</span></label>
				<input type="text" name="g{$i}_fname" id="g{$i}_fname" maxlength="32" value="{if isset($data[$i]['fname'])}{$data[$i]['fname']}{/if}" class="field" />
				<span class="error-message">First name cannot be blank!</span>
			</p>
			<p{if isset($error[$i]['lname'])} class="field-error"{/if}>
				<label for="g{$i}_lname">Last Name<span>Their surname</span></label>
				<input type="text" name="g{$i}_lname" id="g{$i}_lname" maxlength="32" value="{if isset($data[$i]['lname'])}{$data[$i]['lname']}{/if}" class="field" />
				<span class="error-message">Last name cannot be blank!</span>
			</p>
			<p{if isset($error[$i]['ticket_type'])} class="field-error"{/if}>
				<label>Ticket type<span>Click <a href="{url mode='faq'}#queuejump">here</a> for more details.</span></label>
				<span class="form-data">
					<input type="radio" name="g{$i}_ticket_type" id="g{$i}_ticket_type_normal" value=0{if $sale->premium_flag} checked="checked"{else}{if isset($data[$i]['ticket_type']) && $data[$i]['ticket_type'] === 0} checked="checked"{/if}{/if} /><label for="g{$i}_ticket_type_normal">Normal</label>
					<input type="radio" name="g{$i}_ticket_type" id="g{$i}_ticket_type_queuejump" value=1{if $sale->premium_flag} disabled="disabled"{else}{if isset($data[$i]['ticket_type']) && $data[$i]['ticket_type'] === 1} checked="checked"{/if}{/if} /><label for="g{$i}_ticket_type_queuejump">QueueJump{if $sale->premium_flag} <em>(Sold out)</em>{/if}</label>
				</span>
				<span class="error-message">Must select a ticket type!</span>
			</p>
			<p{if isset($error[$i]['age_check'])} class="field-error"{/if}>
				<label>Will they be over 18?<span>&hellip;on the {$event_date}.</span></label>
				<span class="form-data">
					<input type="radio" name="g{$i}_age_check" id="g{$i}_age_check_true" value="true"{if isset($data[$i]['age_check']) && $data[$i]['age_check'] === true} checked="checked"{/if} /><label for="g{$i}_age_check_true">Yes</label>
					<input type="radio" name="g{$i}_age_check" id="g{$i}_age_check_false" value="false"{if isset($data[$i]['age_check']) && $data[$i]['age_check'] === false} checked="checked"{/if} /><label for="g{$i}_age_check_false">No</label>
				</span>
				<span class="error-message">Must be over 18 on the event!</span>
			</p>
		</div>
	</fieldset>
{/for}
{/if}
	{*<fieldset id="song_choice">
		<legend><strong>Song</strong> Choice</legend>
		<div>		
			<p{if isset($error[0]['song_choice'])} class="field-error"{/if}>
				<label for="song_choice">Song Request<span>Submit your requested track for the silent disco.</span></label>
				<input type="text" name="song_choice" id="song_choice" maxlength="80" value="{if isset($data[0]['song_choice'])}{$data[0]['song_choice']}{/if}" class="field" />
				<span class="error-message">Song choice cannot be blank!</span>
			</p>
		</div>
	</fieldset>*}
	<fieldset id="payment">
		<legend><strong>Payment</strong> Details</legend>
		<div>
			<p>
				<label for="charity">&pound;3 Charity donation per ticket<span>to <a href="http://cambridgerapecrisis.org.uk/">Cambridge Rape Crisis Centre</a>.</span></label>
				<input type="checkbox" name="charity" id="charity" value="true" class="option-field" {if !(isset($data['charity']) && !$data['charity'])}checked="checked" {/if}/>
			</p>
			<p{if isset($error['payment_method'])} class="field-error"{/if}>
				<label for="payment_method">Payment Method<span>How you would like to pay.</span></label>
				<select name="payment_method" id="payment_method" class="field">
					<option value="0"{if !isset($data['payment_method']) || $data['payment_method'] == 0} selected="selected"{/if}></option>
{foreach $sale->payment_options as $key=>$val}
					<option value="{$key}"{if isset($data['payment_method']) && $data['payment_method'] == $key} selected="selected"{/if}>{$val}</option>
{/foreach}
				</select>
				<span class="error-message">Please select a payment option!</span>
			</p>
		</div>
	</fieldset>
	<p class="nofield"><input type="submit" name="submit" id="submit" value="Submit" class="button" /></p>
</form>
{/nocache}
{/block}
