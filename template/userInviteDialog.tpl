{literal}
	<div id="userInviteDialogContainer">
		<fieldset>
			<legend>{lang}chat.user.search{/lang}</legend>
			
			<dl class="marginTop">
				<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
				<dd>
					<span>
						<input autocomplete="off" id="userInviteDialogUsernameInput" name="username" class="medium" value="" type="text" />
					</span>
				</dd>
			</dl>
			<div id="userInviteDialogUserList"></div>
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.user.access.following{/lang}</legend>
			
			{if $users.length === 0}
				<p class="info">{lang}chat.global.invite.noFollowing{/lang}</p>
			{else}
				<div id="userInviteDialogFollowingList">
					{foreach from=$users item="user"}
						<dl>
							<dt></dt>
							<dd><label><input type="checkbox" id="userInviteDialogUserID-{$user.userID}" value="{$user.userID}" /> {$user.username}</label></dd>
						</dl>
					{/foreach}
				</div>
			{/if}
		</fieldset>
			
		<div class="formSubmit">
			<input id="userInviteDialogFormSubmit" type="submit" value="{lang}wcf.global.button.submit{/lang}" />
		</div>
	</div>
{/literal}
