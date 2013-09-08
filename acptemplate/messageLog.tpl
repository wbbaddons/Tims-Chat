{include file='header' pageTitle='chat.acp.log.title'}

{if CHAT_LOG_ARCHIVETIME !== 0}
	<script data-relocate="true" src="{$__wcf->getPath('chat')}acp/js/be.bastelstu.Chat.ACP.Log{if !ENABLE_DEBUG_MODE}.min{/if}.js?version={PACKAGE_VERSION|rawurlencode}"></script>

	{if $errorField === ''}
		<script data-relocate="true">
			//<![CDATA[
			$(function() {
				be.bastelstu.Chat.ACP.Log.TabMenu.init();
				WCF.TabMenu.init();
			});
			//]]>
		</script>
	{/if}
{/if}


<header class="boxHeadline">
	<h1>{lang}{@$pageTitle}{/lang}</h1>
</header>

{if CHAT_LOG_ARCHIVETIME !== 0}
	<form method="post" action="{link controller='MessageLog' application='chat'}{/link}">
		<div class="container containerPadding marginTop">
			<fieldset>
				<legend>{lang}wcf.global.filter{/lang}</legend>
				
				<dl>
					<dt><label for="id">{lang}chat.general.room{/lang}</label></dt>
					<dd>
						<select id="id" name="id">
							{foreach from=$rooms item='roomBit'}
							<option value="{$roomBit->roomID}"{if $roomBit->roomID == $room->roomID} selected="selected"{/if}>{$roomBit}</option>
							{/foreach}
						</select>
					</dd>
				</dl>
				
				<dl{if $errorField == 'date'} class="formError"{/if}>
					<dt><label for="date">{lang}chat.general.time{/lang}</label></dt>
					<dd>
						<input id="date" type="date" name="date" value="{$date|date:'Y-m-d'}" />
						{if $errorField == 'date'}
							<small class="innerError">
								{lang}chat.acp.log.date.error.{$errorType}{/lang}
							</small>
						{/if}
					</dd>
				</dl>
			</fieldset>
		</div>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		</div>
	</form>

	<div class="contentNavigation">
		<nav>
			<ul>
				<li>
					<a class="button" href="{link controller='MessageLogDownload' application='chat' object=$room date=$date|date:'Y-m-d'}{/link}">
						<span class="icon icon16 icon-download-alt"></span>
						<span>{lang}chat.acp.log.download{/lang}</span>
					</a>
				</li>
			</ul>
		</nav>
	</div>

	{if $errorField === ''}
		<div id="messageLogContent" class="tabMenuContainer marginTop" data-active="timeTab-0" data-store="activeTabMenuItem" data-base-time="{$date}" data-room-id="{$room->roomID}">
			<nav class="tabMenu">
				<ul>
					{section name=tabLoop loop=24 step=3}
						<li>
							{assign var=anchor value='timeTab-'|concat:$tabLoop}
							<a href="{@$__wcf->getAnchor($anchor)}">{if $tabLoop < 10}0{/if}{$tabLoop}:00 - {if $tabLoop + 2 < 10}0{/if}{$tabLoop + 2}:59</a>
						</li>
					{/section}
				</ul>
			</nav>
			
			{section name=contentLoop loop=24 step=3}
				<div id="timeTab-{$contentLoop}" class="container containerPadding tabMenuContainer tabMenuContent" data-menu-item="timeTab-{$contentLoop}">
					<nav class="menu">
						<ul>
							{section name=subTabLoop loop=6}
								{assign var=subAnchor value='timeTab-'|concat:$contentLoop|concat:'-subTab-'|concat:$subTabLoop}
									<li data-hour="{$contentLoop + $subTabLoop / 2|floor}" data-minutes="{($subTabLoop % 2) * 30}">
										<a href="{@$__wcf->getAnchor($subAnchor)}">{if $contentLoop + $subTabLoop / 2 < 10}0{/if}{$contentLoop + $subTabLoop / 2|floor}:{if $subTabLoop % 2 == 0}0{/if}{($subTabLoop % 2) * 30} - {if $contentLoop + $subTabLoop / 2 < 10}0{/if}{$contentLoop + $subTabLoop / 2|floor}:{($subTabLoop % 2) * 30 + 29}</a>
									</li>
							{/section}
						</ul>
					</nav>
					
					{section name=subTabLoop loop=6}
						{assign var=subAnchor value='timeTab-'|concat:$contentLoop|concat:'-subTab-'|concat:$subTabLoop}
						<div id="{$subAnchor}" class="hidden subTabMenuContent empty"></div>
					{/section}
				</div>
			{/section}
		</div>
	{/if}
{else}
	<p class="error">{lang}chat.acp.log.error.disabled{/lang}</p>
{/if}

{include file='footer'}
