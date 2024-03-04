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
					<span class="jsTooltip awayIcon" title="{lang}chat.room.userList.away{/lang}">{icon size=24 name='clock'}</span>
				{/if}
				{if !$user.permissions.canWritePublicly}
					<span class="jsTooltip" title="{lang}chat.room.userList.mute{/lang}">{icon size=24 name='xmark'}</span>
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
