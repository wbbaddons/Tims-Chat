{include file='header' pageTitle='chat.acp.suspension.list'}

<header class="boxHeadline">
	<h1>{lang}chat.acp.suspension.list{/lang}</h1>
</header>

{if $objects|count}
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.acp.suspension.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID{if $sortField == 'suspensionID'} active {@$sortOrder}{/if}"><a href="{link application='chat' controller='ChatSuspensionList'}pageNo={@$pageNo}&sortField=suspensionID&sortOrder={if $sortField == 'suspensionID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}"><a href="{link application='chat' controller='ChatSuspensionList'}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.username{/lang}</a></th>
					<th class="columnRoomID{if $sortField == 'roomID'} active {@$sortOrder}{/if}"><a href="{link application='chat' controller='ChatSuspensionList'}pageNo={@$pageNo}&sortField=roomID&sortOrder={if $sortField == 'roomID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}chat.general.room{/lang}</a></th>
					<th class="columnType{if $sortField == 'type'} active {@$sortOrder}{/if}"><a href="{link application='chat' controller='ChatSuspensionList'}pageNo={@$pageNo}&sortField=type&sortOrder={if $sortField == 'type' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.chat.type{/lang}</a></th>
					<th class="columnExpires{if $sortField == 'expires'} active {@$sortOrder}{/if}"><a href="{link application='chat' controller='ChatSuspensionList'}pageNo={@$pageNo}&sortField=expires&sortOrder={if $sortField == 'expires' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.chat.expires{/lang}</a></th>
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=$suspension}
					<tr>
						<td id="columnID">{@$suspension->suspensionID}</td>
						<td id="columnUsername"><a href="{link application='chat' controller='ChatSuspensionList'}userID={$suspension->userID}{/link}">{$suspension->username}</a></td>
						<td id="columnRoomID"><a href="{link application='chat' controller='ChatSuspensionList'}roomID={if $suspension->roomID}{$suspension->roomID}{else}0{/if}{/link}">{if $suspension->roomID}{@$suspension->roomTitle|language}{else}{lang}chat.room.global{/lang}{/if}</a></td>
						<td id="columnType"><a href="{link application='chat' controller='ChatSuspensionList'}type={$suspension->type}{/link}">{lang}chat.suspension.{@$suspension->type}{/lang}</a></td>
						<td id="columnExpires">{@$suspension->expires|time} ({@$suspension->expires|dateDiff})</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}


{include file='footer'}
