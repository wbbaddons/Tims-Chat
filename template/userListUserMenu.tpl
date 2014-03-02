{literal}
	<ul data-user-id="{$user.userID}">
		<li><a class="jsTimsChatUserMenuWhisper">{lang}chat.global.whisper{/lang}</a></li>
		<li><a class="jsTimsChatUserMenuQuery">{lang}chat.global.query{/lang}</a></li>
		{if $room.permissions.canMute}<li><a class="jsTimsChatUserMenuMute">{lang}chat.global.mute{/lang}</a></li>{/if}
		{if $room.permissions.canBan}<li><a class="jsTimsChatUserMenuBan">{lang}chat.global.ban{/lang}</a></li>{/if}
		<li><a href="{$user.link}" class="userLink" data-user-id="{$user.userID}">{lang}chat.global.profile{/lang}</a></li>
{/literal}
		{event name='menuItems'}
	</ul>
