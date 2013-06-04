{include file='header' pageTitle='chat.acp.suspension.list'}

<header class="boxHeadline">
	<h1>{lang}chat.acp.suspension.list{/lang}</h1>
</header>

{if $objects|count}
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.acp.suspension.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>
		
		<!--
			'suspensionID', 'userID', 'username', 'roomID', 'type', 'expires'
		-->
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID{if $sortField == 'suspensionID'} active {@$sortOrder}{/if}"><a href="{link application='chat' controller='SuspensionList'}pageNo={@$pageNo}&sortField=suspensionID&sortOrder={if $sortField == 'suspensionID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnUserID{if $sortField == 'userID'} active {@$sortOrder}{/if}"><a href="{link application='chat' controller='SuspensionList'}pageNo={@$pageNo}&sortField=userID&sortOrder={if $sortField == 'userID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.chat.userID{/lang}</a></th>
					<th class="columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}"><a href="{link application='chat' controller='SuspensionList'}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.chat.username{/lang}</a></th>
					<th class="columnRoomID{if $sortField == 'roomID'} active {@$sortOrder}{/if}"><a href="{link application='chat' controller='SuspensionList'}pageNo={@$pageNo}&sortField=roomID&sortOrder={if $sortField == 'roomID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.chat.roomID{/lang}</a></th>
					<th class="columnType{if $sortField == 'type'} active {@$sortOrder}{/if}"><a href="{link application='chat' controller='SuspensionList'}pageNo={@$pageNo}&sortField=type&sortOrder={if $sortField == 'type' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.chat.type{/lang}</a></th>
					<th class="columnExpires{if $sortField == 'expires'} active {@$sortOrder}{/if}"><a href="{link application='chat' controller='SuspensionList'}pageNo={@$pageNo}&sortField=expires&sortOrder={if $sortField == 'expires' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.chat.expires{/lang}</a></th>
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=$suspension}
					<tr>
						<td id="columnID">{@$suspension->suspensionID}</td>
						<td id="columnUserID">{@$suspension->userID}</td>
						<td id="columnUsername">{@$suspension->username}</td>
						<td id="columnRoomID">{@$suspension->roomID}</td>
						<td id="columnType">{@$suspension->type}</td>
						<td id="columnExpires">{@$suspension->expires|date}</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
		
	</div>
{/if}


{include file='footer'}
