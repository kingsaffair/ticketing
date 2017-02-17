{extends file="main.tpl"}
{block "body"}
{if is_array($description)}
{foreach $description as $error_line}
<p>{$error_line}</p>
{/foreach}
{else}
<p>{$description}</p>
{/if}
{/block}