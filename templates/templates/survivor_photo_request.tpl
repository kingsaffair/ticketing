{extends file="main.tpl"}
{block "head"}
{/block}
{block "body"}
{nocache}
<p>Please select the tickets you wish you add a survivors photo to.</p>
<form action="{url mode='survivor'}" method="post" class="tickets" id="tickets">
	<input type="hidden" name="sh" id="sh" value="{$session_hash}" />
{for $i=0 to $guest_count}
    {if ($i == 0)}
        {assign var="identifier" value="primary"}
    {else}
        {assign var="identifier" value="g{$i}"}
    {/if}
	<fieldset id="{$identifier}_ticket" class="ticket {if ($i % 2 == 0)}even{else}odd{/if}">
		<legend><strong>{if ($i == 0)}Primary{else}Guest {$i}{/if}</strong> Ticket</legend>
		<div>
			<input type="hidden" name="{$identifier}_tid" id="{$identifier}_tid" value="{$data[$i]['tid']}" />
			<p>
				<label for="{$identifier}_name">Name</label>
				<input type="text" name="{$identifier}_name" id="{$identifier}_name" value="{if isset($data[$i]['fname'])}{$data[$i]['fname']}{/if} {if isset($data[$i]['lname'])}{$data[$i]['lname']}{/if}" class="field" disabled />
			</p>
            <p>
				<label for="{$identifier}_photo">Mark for photo<span>Check if you want a photo for this ticket</span></label>
				<input type="checkbox" name="{$identifier}_photo" id="{$identifier}_photo" value="true" class="option-field" {if (isset($data[$i]['photo']) && $data[$i]['photo'])}checked="checked" {/if}/>
			</p>
		</div>
	</fieldset>
{/for}
	<p class="nofield"><strong>Survivors photos must be paid for by Cheque.</strong></p>
	<p class="nofield"><input type="submit" name="submit" id="submit" value="Submit" class="button" /></p>
</form>
{/nocache}
{/block}