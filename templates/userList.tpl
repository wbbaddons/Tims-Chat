{literal}
<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="UserList">
	<ul class="sidebarItemList">
		{foreach from=$users item='user'}
			<li data-user-id="{$user.userID}"{if $user.away != null} data-user-away="{$user.away}"{/if} class="box24 jsUserActionDropdown">
				<a href="{$user.link}">{@$user.image24}</a>

				<div class="sidebarItemTitle">
					<h3><a href="{$user.link}" data-user-id="{$user.userID}">{@$user.formattedUsername}</a></h3>
				</div>

				<div class="iconColumn">
				{if $user.away !== null}
					<span class="icon icon24 fa-clock-o jsTooltip awayIcon" title="{lang}chat.room.userList.away{/lang}"></span>
				{/if}
				{if !$user.permissions.canWritePublicly}
					<span class="icon icon24 fa-remove jsTooltip" title="{lang}chat.room.userList.mute{/lang}"></span>
				{/if}
				{/literal}
				{event name='icons'}
				{literal}
				</div>
			</li>
		{/foreach}
	</ul>
</script>
{/literal}
