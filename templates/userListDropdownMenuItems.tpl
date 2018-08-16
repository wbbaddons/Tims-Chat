<script type="x-text/template" data-application="be.bastelstu.chat" data-template-name="UserListDropdownMenuItems">
	<ul class="dropdownMenu">
		{literal}<li><a href="{$user.link}" class="userLink" data-user-id="{$user.userID}">{lang}chat.user.action.profile{/lang}</a></li>{/literal}
		<li><span data-module="Bastelstu.be/Chat/Ui/UserActions/WhisperAction" data-trigger="whisper">{lang}chat.user.action.whisper{/lang}</span></li>
		{capture append='extraModules'}
		'Bastelstu.be/Chat/Ui/UserActions/WhisperAction',
		{/capture}
{if $__wcf->session->getPermission('mod.chat.canMute')}
		<li><span data-module="Bastelstu.be/Chat/Ui/UserActions/MuteAction" data-trigger="mute">{lang}chat.user.action.mute{/lang}</span></li>
{/if}
{if $__wcf->session->getPermission('mod.chat.canBan')}
		<li><span data-module="Bastelstu.be/Chat/Ui/UserActions/BanAction" data-trigger="ban">{lang}chat.user.action.ban{/lang}</span></li>
{/if}
		{capture append='extraModules'}
		'Bastelstu.be/Chat/Ui/UserActions/MuteAction',
		'Bastelstu.be/Chat/Ui/UserActions/BanAction',
		{/capture}
		{event name='dropdownMenuEntries'}
	</ul>
</script>
