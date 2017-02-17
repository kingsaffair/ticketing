{extends file="main.tpl"}
{block "body"}
<p>Welcome to The {$event_name} {$site_name}.</p>
<p>If you would like to buy tickets for the {$event_name} or review an existing order, please <a href="{url mode='user' arg='login'}">sign in with Raven</a>.</p>
{/block}