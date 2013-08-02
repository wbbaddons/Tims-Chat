{include file='header' pageTitle='chat.acp.log.title'}

<script>
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
	});
	//]]>
</script>


<header class="boxHeadline">
	<h1>{lang}{@$pageTitle}{/lang}</h1>
</header>

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

{if $messages|count == 0}
	{if $errorField === ""}
		<p class="info">{lang}wcf.global.noItems{/lang}</p>
	{/if}
{else}
	<div class="tabMenuContainer marginTop" data-active="timeTab-0" data-store="activeTabMenuItem">
		<nav class="tabMenu">
			<ul>
				{section name=tabLoop loop=24 step=3}
					{if $messages[$tabLoop * 2]|isset || $messages[$tabLoop * 2 + 1]|isset || $messages[$tabLoop * 2 + 2]|isset || $messages[$tabLoop * 2 + 3]|isset || $messages[$tabLoop * 2 + 4]|isset || $messages[$tabLoop * 2 + 5]|isset}
						<li>
							{assign var=anchor value='timeTab-'|concat:$tabLoop}
							<a href="{@$__wcf->getAnchor($anchor)}">{if $tabLoop < 10}0{/if}{$tabLoop}:00 - {if $tabLoop + 2 < 10}0{/if}{$tabLoop + 2}:59</a>
						</li>
					{/if}
				{/section}
			</ul>
		</nav>
		
		{section name=contentLoop loop=24 step=3}
			{if $messages[$contentLoop * 2]|isset || $messages[$contentLoop * 2 + 1]|isset || $messages[$contentLoop * 2 + 2]|isset || $messages[$contentLoop * 2 + 3]|isset || $messages[$contentLoop * 2 + 4]|isset || $messages[$contentLoop * 2 + 5]|isset}
				<div id="timeTab-{$contentLoop}" class="container containerPadding tabMenuContainer tabMenuContent">
					<nav class="menu">
						<ul>
							{section name=subTabLoop loop=6}
								{if $messages[$contentLoop * 2 + $subTabLoop]|isset}
									{assign var=subAnchor value='timeTab-'|concat:$contentLoop|concat:'-subTab-'|concat:$subTabLoop}
									<li>
										<a href="{@$__wcf->getAnchor($subAnchor)}">{if $contentLoop + $subTabLoop / 2 < 10}0{/if}{$contentLoop + $subTabLoop / 2|floor}:{if $subTabLoop % 2 == 0}0{/if}{($subTabLoop % 2) * 30} - {if $contentLoop + $subTabLoop / 2 < 10}0{/if}{$contentLoop + $subTabLoop / 2|floor}:{($subTabLoop % 2) * 30 + 29}</a>
									</li>
								{/if}
							{/section}
						</ul>
					</nav>
					
					{section name=subTabLoop loop=6}
						{if $messages[$contentLoop * 2 + $subTabLoop]|isset}
							{assign var=subAnchor value='timeTab-'|concat:$contentLoop|concat:'-subTab-'|concat:$subTabLoop}
							<div id="{$subAnchor}" class="hidden tabularBox">
								<table class="table">
									<thead>
										<tr>
											<th>{lang}wcf.global.objectID{/lang}</th>
											<th>{lang}chat.general.time{/lang}</th>
											<th colspan="2">{lang}wcf.user.username{/lang}</th>
											<th>{lang}chat.acp.log.message{/lang}</th>
										</tr>
									</thead>
									
									<tbody>
										{foreach from=$messages[$contentLoop * 2 + $subTabLoop] item='message'}
											<tr>
												<td class="columnID">{$message->messageID}</td>
												<td style="width: 1px !important;">{$message->time|date:'H:i:s'}</td>
												<td class="columnIcon"><p class="framed">{@$message->getUserProfile()->getAvatar()->getImageTag(24)}</p></td>
												<td class="columnTitle columnUsername right" style="width: 1px !important;">{$message->username}</td>
												<td>{@$message->getFormattedMessage('text/simplified-html')}</td>
											</tr>
										{/foreach}
									</tbody>
								</table>
							</div>
						{/if}
					{/section}
				</div>
			{/if}
		{/section}
	</div>
{/if}

{include file='footer'}
