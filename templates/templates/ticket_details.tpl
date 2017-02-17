{extends file="main.tpl"}
{block "body"}
{nocache}
<ul class="status">
{if $data['waiting'] == 0}
	<li class="complete">
		<h5>1</h5>
		<span>
			Booking
			<em>{$data['created']|formatdate:"j/m/Y"}</em>
		</span>
	</li>
	<li class="{if $data['paid'] == 0}current{else}complete{/if}">
		<h5>2</h5>
		<span>
			Payment
{if $data['paid'] != 0}<em>{$data['paid']|formatdate:"j/m/Y"}</em>{/if}
		</span>
	</li>
	<li class="{if $data['collected'] == 0}{if $data['paid'] == 0}future{else}current{/if}{else}complete{/if}">
		<h5>3</h5>
		<span>
			Collection
{if $data['collected'] != 0}<em>{$data['collected']|formatdate:"j/m/Y"}</em>{/if}
		</span>
	<li>
	<li class="{if $data['entered'] == 0}{if $data['collected'] == 0}future{else}current{/if}{else}complete{/if}">
		<h5>4</h5>
		<span>
			Entry
{if $data['entered'] != 0}<em>{$data['entered']|formatdate:"j/m/Y"}</em>{/if}
		</span>
	<li>
{else}
	<li class="current">
		<h5>1</h5>
		<span>
			Waiting List
			<em>{$data['created']|formatdate:"j/m/Y"}</em>
		</span>
	</li>
	<li class="future">
		<h5>2</h5>
		<span>
			Payment
		</span>
	</li>
	<li class="future">
		<h5>3</h5>
		<span>
			Collection
		</span>
	<li>
	<li class="future">
		<h5>4</h5>
		<span>
			Entry
		</span>
	<li>
{/if}
</ul>

<h4>Ticket Details</h4>
<table class="simple" caption="List of tickets" summary="A list of your tickets">
	<thead>
		<tr>
			<th scope="col">Ticket</th>
			<th scope="col">First Name</th>
			<th scope="col">Last Name</th>
			<th scope="col">Ticket Type</th>
{if $data['waiting'] == 0}
			<th scope="col">Name Changes</th>
           {* <th scope="col">Survivors Photos</th>*}
{/if}
		</tr>
	</thead>
	<tbody>
{for $i=0 to $guest_count}
		<tr>
			<th scope="row">{if ($i == 0)}Primary{else}Guest {$i}{/if}</th>
			<td>{$data[$i]['fname']}</td>
			<td>{$data[$i]['lname']}</td>
			<td>{if ($i == 0 && $committee_flag >= 1)}Committee{else}{if ($data[$i]['ticket_type'] == 0)}Normal{else}QueueJump{/if}{/if}</td>
{if $data['waiting'] == 0}
{if ($i == 0)}
			<td>Not permitted</td>
{else}
			<td>{if $data['name_change']}Pending&hellip;{else}{if $nc_enabled}<a href="{url mode='namechange'}">Request a change</a>{else}<em>Disabled</em>{/if}{/if}</td>
{/if}
           {* <td>{if ($data['survivor'] == 'pending')}Pending&hellip;
                {elseif ($data['survivor'] == 'none')}
                    {if $sp_enabled}<a href="{url mode='survivor'}">Request a photo</a>
                    {else}<em>Disabled</em>
                    {/if}
                {elseif ($data['survivor'] == 'paid')}
                    {if ($data[$i]['survivor'])}Pre-Ordered
                    {else}None
                    {/if}
                {/if}</td>*}
{/if}
		</tr>
{/for}
	</tbody>
</table>

{if $data['waiting'] == 1}

<h4>Waiting List</h4>

<p>You are currently on the waiting list. If a place becomes available, you will be offered a spot. Please do not send any payment at this time. Payment is only required if you are offered a ticket.</p>

{elseif $data['paid'] == 0}

<h4>Payment Details</h4>

{if $data['payment_method'] == 1}
<p>As you have chosen payment via College Bills, you do not have to do anything at this stage.</p>
{else}
<p>As you have chosen payment via Bank Transfer, you will need to send a Bank Transfer payable to <strong>King&rsquo;s Affair</strong> for the amount of <strong>&pound;{$data['amount']}</strong>.</p>
<p>This needs to be done <strong>within 10 days</strong> of the day you booked your ticket.</p>
<p>Please use the following Reference Code when sending your Bank Transfer: <strong>t{$data['amount']}-{$user->crsid}</strong>.</p>
<p>Bank Transfers should be sent to:</p>
<p>Sort Code: 60-04-23<br />
Account Number: 24175439</p>
<p>PLEASE DOUBLE CHECK THE SORT CODE, ACCOUNT NUMBER AND REFERENCE CODE, AS IF ANY OF THESE ARE INCORRECT YOUR PAYMENT MAY BE LOST.</p>

{/if}
<p>Once your payment has been processed you will receive notifcation by email.</p>

{elseif $data['collected'] == 0}

<h4>Collection Details</h4>

<p>Information regarding collection of King's Affair tickets will be released shortly through our Facebook page.</p>

{elseif $data['entered'] == 0}

<h4>Entry Details</h4>

<p>In order to enter the King's Affair you will need to bring one of the following as Valid Photo ID:</p>

<p>&ndash; A current (non-expired) University Card;</p>
<p>&ndash; A photocopy of or actual current passport or driving license with a Photograph and Date of Birth.</p>

{/if}

{if $data['name_change']}

<h4>Name Change Details</h4>

<p>In order to complete your name change, you will need to send a Bank Transfer to <strong>King&rsquo;s Affair</strong> for the amount of <strong>&pound;{$data['name_change_amount']}</strong>.</p>
<p>Please use the following Reference Code when sending your Bank Transfer: <strong>n{$data['name_change_amount']}-{$user->crsid}</strong>.</p>
<p>Bank Transfers should be sent to:</p>
<p>Sort Code: 60-04-23<br />
Account Number: 24175439</p>
<p>PLEASE DOUBLE CHECK THE SORT CODE, ACCOUNT NUMBER AND REFERENCE CODE, AS IF ANY OF THESE ARE INCORRECT YOUR PAYMENT MAY BE LOST.</p>
<p>If you would like to cancel your name change, please click <a href="{url mode='namechange' arg='cancel'}">here</a>.</p>

{/if}

{if ($data['survivor'] == 'pending')}

<h4>Survivors Photo Details</h4>

<p>In order to complete your photo request, you will need to send a Cheque payable to <strong>King&rsquo;s Affair</strong> with the amount <strong>&pound;{$data['survivor_photo_amount']}</strong>.</p>
<p>Please write the following at the back of the Cheque: <strong>s{$data['survivor_photo_amount']}-{$user->crsid}</strong>.</p>
<p>Cheques should be sent to:</p>
<p>King&rsquo;s Affair<br />
King&rsquo;s College<br />
CB2 1ST Cambridge<br />
United Kingdom</p>
<p>If you would like to cancel your name change, please click <a href="{url mode='survivor' arg='cancel'}">here</a>.</p>

{/if}

{/nocache}
{/block}
