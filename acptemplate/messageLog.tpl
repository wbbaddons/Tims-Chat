{include file='header' pageTitle='chat.acp.log.title'}
	
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
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}chat.acp.log.title{/lang} <span class="badge badgeInverse">{#$messages|count}</span></h2>
		</header>
		
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
				{foreach from=$messages item='message'}
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

{include file='footer'}
