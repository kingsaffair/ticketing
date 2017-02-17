{extends file="main.tpl"}
{block "body"}
<ul class="main-menu">
{foreach $menu as $menuitem}
{if isset($menuitem['TITLE'])}
	<li class="sub-menu"><h4>{$menuitem['TITLE']}</h4>
		<ul>
{foreach $menuitem as $key=>$menusubitem}
{if !($key === 'TITLE')}
			<li class="item">
				<h5><a href="{$menusubitem['URL']}">{$menusubitem['NAME']}</a></h5>
				<span>{$menusubitem['DESCRIPTION']}</span>
			</li>
{/if}
{/foreach}
		</ul>
	</li>
{else}
	<li class="item">
		<h5><a href="{$menuitem['URL']}">{$menuitem['NAME']}</a></h5>
		<span>{$menuitem['DESCRIPTION']}</span>
	</li>
{/if}
{/foreach}
</ul>
{/block}