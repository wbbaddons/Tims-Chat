{include file='header' pageTitle='chat.acp.suspension.list'}

<script type="text/javascript">
	//<![CDATA[
		$(function() {
			new WCF.Search.User('#username', null, false, [ ], false);
			new WCF.Search.User('#issuerUsername', null, false, [ ], false);
		});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}chat.acp.suspension.list{/lang}</h1>
</header>

<form method="post" action="{link controller='ChatSuspensionList' application="chat"}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.global.filter{/lang}</legend>
		
			<dl>
				<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
				<dd>
					<input type="text" id="username" name="username" class="long" value="{$filterUsername}" />
				</dd>
			</dl>
			
			<dl>
				<dt><label for="issuerUsername">{lang}wcf.acp.chat.issuer{/lang}</label></dt>
				<dd>
					<input type="text" id="issuerUsername" name="issuerUsername" class="long" value="{$filterIssuerUsername}" />
				</dd>
			</dl>
			
			<dl>
				<dt><label for="roomID">{lang}chat.general.room{/lang}</label></dt>
				<dd>
					<select name="roomID">
						<option value="-1" {if $filterRoomID == -1}selected="selected"{/if}></option>
						<option value="0" {if $filterRoomID == 0}selected="selected"{/if}>{lang}chat.room.global{/lang}</option>
						<option value="" disabled="disabled">&mdash;&mdash;&mdash;&mdash;</option>
						{foreach from=$availableRooms key=id item=room}
							<option value="{$id}" {if $filterRoomID == $id}selected="selected"{/if}>{$room}</option>
						{/foreach}
					</select>
				</dd>
			</dl>
			
			<dl>
				<dt><label for="searchTypeMute">{lang}wcf.acp.chat.suspensionType{/lang}</label></dt>
				<dd>
					<select name="suspensionType" id="suspensionType">
						<option value=""{if $filterSuspensionType == null}selected="selected"{/if}></option>
						<option value="{'\chat\data\suspension\Suspension::TYPE_MUTE'|constant}"{if $filterSuspensionType == '\chat\data\suspension\Suspension::TYPE_MUTE'|constant} selected="selected"{/if}>{lang}chat.suspension.{'\chat\data\suspension\Suspension::TYPE_MUTE'|constant}{/lang}</option>
						<option value="{'\chat\data\suspension\Suspension::TYPE_BAN'|constant}"{if $filterSuspensionType == '\chat\data\suspension\Suspension::TYPE_BAN'|constant} selected="selected"{/if}>{lang}chat.suspension.{'\chat\data\suspension\Suspension::TYPE_BAN'|constant}{/lang}</option>
					</select>
				</dd>
			</dl>
		</fieldset>
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
	</div>
</form>
	
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
					<th class="columnSuspensionType{if $sortField == 'suspensionType'} active {@$sortOrder}{/if}"><a href="{link application='chat' controller='ChatSuspensionList'}pageNo={@$pageNo}&sortField=suspensionType&sortOrder={if $sortField == 'suspensionType' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.chat.suspensionType{/lang}</a></th>
					<th class="columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link application='chat' controller='ChatSuspensionList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.chat.time{/lang}</a></th>
					<th class="columnExpires{if $sortField == 'expires'} active {@$sortOrder}{/if}"><a href="{link application='chat' controller='ChatSuspensionList'}pageNo={@$pageNo}&sortField=expires&sortOrder={if $sortField == 'expires' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.chat.expires{/lang}</a></th>
					<th class="columnIssuer{if $sortField == 'issuer'} active {@$sortOrder}{/if}"><a href="{link application='chat' controller='ChatSuspensionList'}pageNo={@$pageNo}&sortField=issuer&sortOrder={if $sortField == 'issuer' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.chat.issuer{/lang}</a></th>
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=$suspension}
					<tr>
						<td id="columnID">{@$suspension->suspensionID}</td>
						<td id="columnUsername"><a href="{link application='chat' controller='ChatSuspensionList'}userID={$suspension->userID}{/link}">{$suspension->username}</a></td>
						<td id="columnRoomID"><a href="{link application='chat' controller='ChatSuspensionList'}roomID={if $suspension->roomID}{$suspension->roomID}{else}0{/if}{/link}">{if $suspension->roomID}{@$suspension->roomTitle|language}{else}{lang}chat.room.global{/lang}{/if}</a></td>
						<td id="columnSuspensionType"><a href="{link application='chat' controller='ChatSuspensionList'}suspensionType={$suspension->type}{/link}">{lang}chat.suspension.{@$suspension->type}{/lang}</a></td>
						<td id="columnTime">{@$suspension->time|plainTime}</td>
						<td id="columnExpires">{@$suspension->expires|plainTime} ({$suspension->expires|dateDiff})</td>
						<td id="columnIssuer"><a href="{link application='chat' controller='ChatSuspensionList'}issuerUserID={$suspension->issuer}{/link}">{$suspension->issuerUsername}</a></td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}


{include file='footer'}
