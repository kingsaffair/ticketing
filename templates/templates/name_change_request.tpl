{extends file="main.tpl"}
{block "head"}
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
	<script type="text/javascript" src="{resource src='scripts/namechange.js'}"></script>
{/block}
{block "body"}
{nocache}
<p>Please select and fill in the tickets you wish you change.</p>
<form action="{url mode='namechange'}" method="post" class="tickets" id="tickets">
	<input type="hidden" name="sh" id="sh" value="{$session_hash}" />
{for $i=1 to $guest_count}
	<fieldset id="g{$i}_ticket" class="ticket {if ($i % 2 == 0)}even{else}odd{/if}">
		<legend><strong>Guest {$i}</strong> Ticket</legend>
		<div>
			<input type="hidden" name="g{$i}_tid" id="g{$i}_tid" value="{$data[$i]['tid']}" />
			<p>
				<label for="g{$i}_change">Mark for change<span>Check if you want this ticket to change</span></label>
				<input type="checkbox" name="g{$i}_change" id="g{$i}_change" value="true" class="option-field" {if (isset($data[$i]['change']) && $data[$i]['change'])}checked="checked" {/if}/>
			</p>
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
		</div>
	</fieldset>
{/for}
	<p class="nofield"><strong>Name change requests must be paid by Bank Transfer.</strong></p>
	<p class="nofield"><input type="submit" name="submit" id="submit" value="Submit" class="button" /></p>
</form>
{/nocache}
{/block}
