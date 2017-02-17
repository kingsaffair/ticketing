{extends file="main.tpl"}
{block "body"}
{nocache}
<p>{$description}</p>
<p><strong>Please check your details thoroughly as you may not change your order once you have confirmed your order.</strong></p>
<p><strong>You will not be able to add additional guests tickets after you have confirmed your order.</strong></p>
<h4>Ticket Details</h4>
<table class="simple" caption="List of tickets" summary="A list of your tickets">
	<thead>
		<tr>
			<th scope="col">Ticket</th>
			<th scope="col">First Name</th>
			<th scope="col">Last Name</th>
			<th scope="col">Ticket Type</th>
		</tr>
	</thead>
	<tbody>
{for $i=0 to $guest_count}
		<tr>
			<th scope="col">{if ($i == 0)}Primary{else}Guest {$i}{/if}</th>
			<td>{$data[$i]['fname']}</td>
			<td>{$data[$i]['lname']}</td>
			<td>{if ($i == 0 && $sale->committee_flag == 2)}Committee{else}{if ($data[$i]['ticket_type'] == 0)}Normal{else}QueueJump{/if}{/if}</td>
		</tr>
{/for}
	</tbody>
</table>

<h4>Invoice</h4>
<ul class="invoice">
{foreach $invoice as $i=>$row}
	<li class="{if $i % 2 == 0}even{else}odd{/if}">
		<span>
			<h5>{$row['item']}</h5>
			<span>&pound;{$row['price']}.00</span>
		</span>
		<ul>
{foreach $row['subitems'] as $row2}
			<li>
				<span>
					<h5>{$row2['item']}</h5>
					<span>&pound;{$row2['price']}.00</span>
				</span>
			</li>
{/foreach}
		</ul>
	</li>
{/foreach}
	<li class="summary">
		<span>
			<h5>Total</h5>
			<span>&pound;{$total}</span>
		</span>
	</li>
	{*<li>
		<span>
			<h5>Song Request</h5>
			<span>{$data['song_choice']}</span>
		</span>
	</li>*}
</ul>

<h4>Confirmation</h4>
<form action="{url mode='tickets'}" method="post" class="confirm" id="confirm">
	<input type="hidden" name="sh" id="sh" value="{$session_hash}" />
	<input type="hidden" name="ch" id="ch" value="{if $sale->waiting_flag}w{else}c{/if}" />
{if ($guest_count == 0) && ($sale->guests_allowed > 0)}
	<p class="field-error"><strong>WARNING: It appears that you have not selected any guest tickets. If this was a mistake please click back to purchase guest tickets.</strong></p>
{/if}
	<p{if isset($error['toc'])} class="field-error"{/if}>
		<input type="checkbox" name="toc" id="toc" value="true" class="field" /><label for="toc">I have read and agree to the <a href="{url mode='terms-and-conditions'}">Terms and Conditions</a>.
	</p>
	<p>
		<input type="submit" name="back" id="back" value="Back" class="button" />
		<input type="submit" name="confirm" id="confirm" value="Confirm" class="button" />
	</p>
</form>
{/nocache}
{/block}
