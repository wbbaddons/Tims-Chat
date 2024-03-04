{include file='header' pageTitle='chat.acp.suspension.list'}

<script>
require([ 'Bastelstu.be/PromiseWrap/Ajax', 'Bastelstu.be/PromiseWrap/Ui/Confirmation', 'WoltLabSuite/Core/Dom/Traverse' ], function (Ajax, Confirmation, Traverse) {
	elBySelAll('.jsRevokeButton:not(.disabled)', document, function (button) {
		const row = Traverse.parentByClass(button, 'jsSuspensionRow')
		if (row == null) {
			throw new Error('Unreachable')
		}
		const objectID = row.dataset.objectId
		const listener = function (event) {
			Confirmation.show({
				message: button.dataset.confirmMessageHtml,
				messageIsHtml: true
			}).then(function () {
				const payload = { data: { className: 'chat\\data\\suspension\\SuspensionAction'
				                        , actionName: 'revoke'
				                        , objectIDs: [ objectID ]
				                        }
				                }
				return Ajax.apiOnce(payload)
			}).then(function () {
				button.classList.remove('pointer')
				button.classList.add('disabled')
				button.removeEventListener('click', listener)
			})
		}
		button.addEventListener('click', listener)
	})
})
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}chat.acp.suspension.list{/lang}</h1>
	</div>

	{hascontent}
	<nav class="contentHeaderNavigation">
		<ul>
			{content}{event name='contentHeaderNavigation'}{/content}
		</ul>
	</nav>
	{/hascontent}
</header>

<form method="post" action="{link controller='SuspensionList' application='chat'}{/link}">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>

		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<select name="roomID" id="roomID">
						<option value=""{if $roomID === null} selected{/if}>{lang}chat.acp.suspension.room.all{/lang}</option>
						<option value="0"{if $roomID === 0} selected{/if}>{lang}chat.acp.suspension.room.global{/lang}</option>
						{htmlOptions options=$availableRooms selected=$roomID}
					</select>
				</dd>
			</dl>

			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<select name="objectTypeID" id="objectTypeID">
						<option value="">{lang}chat.acp.suspension.objectType.allTypes{/lang}</option>
						{foreach from=$availableObjectTypes item=availableObjectType}
							<option value="{$availableObjectType->objectTypeID}"{if $availableObjectType->objectTypeID == $objectTypeID} selected{/if}>{lang}chat.acp.suspension.type.{$availableObjectType->objectType}{/lang}</option>
						{/foreach}
					</select>
				</dd>
			</dl>

			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="searchUsername" name="searchUsername" value="{$searchUsername}" placeholder="{lang}chat.acp.suspension.username{/lang}" class="long">
				</dd>
			</dl>


			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="searchJudge" name="searchJudge" value="{$searchJudge}" placeholder="{lang}chat.acp.suspension.judge{/lang}" class="long">
				</dd>
			</dl>

			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<label><input name="showExpired" value="1" type="checkbox"{if $showExpired !== false} checked{/if}>{lang}chat.acp.suspension.showExpired{/lang}</label>
				</dd>
			</dl>

			{event name='filterFields'}
		</div>

		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{csrfToken}
		</div>
	</section>
</form>


{capture assign=additionalParameters}{*
	*}{if $userID !== null}&userID={$userID}{/if}{*
	*}{if $judgeID !== null}&judgeID={$judgeID}{/if}{*
	*}{if $roomID !== null}&roomID={$roomID}{/if}{*
	*}{if $objectTypeID !== null}&objectTypeID={$objectTypeID}{/if}{*
	*}{if $showExpired !== null}&showExpired={$showExpired}{/if}{*
*}{/capture}

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="SuspensionList" application="chat" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$additionalParameters"}{/content}
	</div>
{/hascontent}

{hascontent}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnSuspensionID{if $sortField == 'suspensionID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='SuspensionList' application='chat'}pageNo={@$pageNo}&sortField=suspensionID&sortOrder={if $sortField == 'suspensionID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$additionalParameters}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnObjectType">{lang}chat.acp.suspension.type{/lang}</th>
					<th class="columnText columnUsername">{lang}chat.acp.suspension.username{/lang}</th>
					<th class="columnText columnJudge">{lang}chat.acp.suspension.judge{/lang}</th>
					<th class="columnText columnRoom">{lang}chat.acp.suspension.room{/lang}</th>
					<th class="columnText columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='SuspensionList' application='chat'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$additionalParameters}{/link}">{lang}chat.acp.suspension.time{/lang}</a></th>
					<th class="columnText columnExpires{if $sortField == 'expiresSort'} active {@$sortOrder}{/if}"><a href="{link controller='SuspensionList' application='chat'}pageNo={@$pageNo}&sortField=expiresSort&sortOrder={if $sortField == 'expiresSort' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$additionalParameters}{/link}">{lang}chat.acp.suspension.expires{/lang}</a></th>

					{event name='columnHeads'}
				</tr>
			</thead>

			<tbody>
			{content}
				{foreach from=$objects item=suspension}
					<tr class="jsSuspensionRow" data-object-id="{$suspension->suspensionID}">
						<td class="columnIcon">
							<span class="jsRevokeButton{if !$suspension->isActive()} disabled{else} pointer{/if}" title="{lang}chat.acp.suspension.revoke{/lang}" data-confirm-message-html="{lang}chat.acp.suspension.revoke.sure{/lang}">{icon name='arrow-rotate-left'}</span>
							{event name='rowButtons'}
						</td>

						<td class="columnID">{@$suspension->suspensionID}</td>
						<td class="columnTitle columnObjectType"><a href="{link controller="SuspensionList" application="chat"}objectTypeID={$suspension->objectTypeID}{/link}">{lang}chat.acp.suspension.type.{$suspension->getSuspensionType()->objectType}{/lang}</a></td>
						<td class="columnText columnUsername"><a href="{link controller="SuspensionList" application="chat"}userID={$suspension->userID}{/link}">{$suspension->getUser()->username}</a></td>
						<td class="columnText columnJudge"><a href="{link controller="SuspensionList" application="chat"}judgeID={$suspension->judgeID}{/link}">{$suspension->judge}</a></td>
						<td class="columnText columnRoom"><a href="{link controller="SuspensionList" application="chat"}roomID={$suspension->roomID}{/link}">{if $suspension->getRoom() !== null}{$suspension->getRoom()}{else}-{/if}</a></td>
						<td class="columnText columnTime">{@$suspension->time|time}</td>
						<td class="columnText columnExpires">
							{assign var='isActive' value=$suspension->isActive()}
							{if $isActive}<strong>{/if}
								{if $suspension->expires !== null}{@$suspension->expires|time}{else}{lang}chat.acp.suspension.expires.forever{/lang}{/if}
							{if $isActive}</strong>{/if}
							{if $suspension->revoked !== null}
								<br>{lang}chat.acp.suspension.revoked{/lang}
							{/if}
						</td>

						{event name='columns'}
					</tr>
				{/foreach}
			{/content}
			</tbody>
		</table>
	</div>
{hascontentelse}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/hascontent}

<footer class="contentFooter">
	{hascontent}
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}

	{hascontent}
	<nav class="contentFooterNavigation">
		<ul>
			{content}{event name='contentFooterNavigation'}{/content}
		</ul>
	</nav>
	{/hascontent}
</footer>

{include file='footer'}

