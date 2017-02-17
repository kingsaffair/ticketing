<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<title>{$event_name}{if $page_name != '' } &mdash; {/if}{$page_name}</title>
	<link rel="stylesheet" href="{resource src='css/default.css'}" />
	{block "head"}{/block}
</head>
<body>
<div id="mainContainer">
<h1>{$event_name}</h1>
<h2>{$site_name}</h2>
<div id="contentContainer">
{nocache}
{block "return"}
{if !isset($hidereturn) }
{if isset($user) }
<span class="return-link"><a href="{url mode='tickets'}">&laquo; Return to Tickets</a></span>
{else}
<span class="return-link"><a href="{url}">&laquo; Return to Home Page</a></span>
{/if}
{/if}
{/block}
{/nocache}
{if $page_name != ''}<h3>{$page_name}</h3>{/if}
{block "body"}{/block}
<div class="footer-links">
{nocache}
{if isset($user) }
	<span class="small_header">Logged in as {$user->crsid}{if $user->committee_flag == 2} &mdash; <a href="{url mode='admin'}">Admin</a>{/if} &mdash; <a href="{url mode='user' arg='logout'}">Log out</a>.</span>
{/if}
	<ul>
		<li><a href="{url}">Home</a></li>
		<li><a href="{url mode='faq'}">FAQ</a></li>
		<li><a href="{url mode='terms-and-conditions'}">Terms and Conditions</a></li>
	</ul>
{/nocache}
</div>
</div>
<span class="copyright">Copyright &copy; {$event_name}. All rights reserved.</span>
</div>
</body>
</html>