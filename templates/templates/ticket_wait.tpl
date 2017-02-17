{extends file="main.tpl"}
{block "head"}
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
{nocache}
	<script type="text/javascript">
		var time_left = {$time_left} * 1000;
	</script>
{/nocache}
	<script type="text/javascript" src="{resource src='scripts/ticket_wait.js'}"></script>
{/block}
{block "body"}
{nocache}
<p>Unfortunately, you may not purchase tickets at the moment.</p>
{if $aim_zone == 1}
<p>As you are a King&rsquo;s Member, you may purchase tickets at <strong>{$college_start|formatdate:'g:ia l j\<\s\u\p\>S\<\/\s\u\p\> F'}</strong> till <strong>{$college_end|formatdate:'g:ia l j\<\s\u\p\>S\<\/\s\u\p\> F'}</strong>.</p>
<p>Or you may purchase tickets when they go on sale to the whole University at <strong>{$general_start|formatdate:'g:ia l j\<\s\u\p\>S\<\/\s\u\p\> F'}</strong>.</p>
{else}
{if $sale->college_flag}
<p>Sale for King&rsquo;s Member&rsquo;s , you may purchase tickets when they go on sale to the whole University at <strong>{$general_start|formatdate:'g:ia l j\<\s\u\p\>S\<\/\s\u\p\> F'}</strong>.</p>
{else}
<p>As you are not a King&rsquo;s Member, you may purchase tickets when they go on sale to the whole University at <strong>{$general_start|formatdate:'g:ia l j\<\s\u\p\>S\<\/\s\u\p\> F'}</strong>.</p>
{/if}
{/if}
<div id="sale">
	<span>Tickets will be available in:</span>
	<h4 id="sale">{$days} days, {$hours} hours, {$minutes} minutes, {$seconds} seconds</h4>
	<em>(Enable JavaScript to automatically refresh this page)</em>
</div>
{/nocache}
{/block}