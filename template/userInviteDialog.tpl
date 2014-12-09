<div id="userInviteDialogContainer">
	<fieldset>
		<legend>{lang}wcf.user.access.following{/lang}</legend>
		
		{if $users|count === 0}
			<p class="noFollowing">{lang}chat.global.invite.noFollowing{/lang}</p>
		{/if}
		
		<div id="userInviteDialogUserlist">
			<dl>
				{foreach from=$users item=$user}
					<dt></dt>
					<dd><label><input type="checkbox" id="userInviteDialogUserID-{$user->userID}" data-user-id="{$user->userID}" /> {$user->username}</label></dd>
				{/foreach}
			</dl>
		</div>
		
		<div class="formSubmit">
			<input id="userInviteDialogFormSubmit" type="submit" value="{lang}wcf.global.button.submit{/lang}" />
		</div>
	</fieldset>
	
	<fieldset>
		<legend>{lang}chat.user.search{/lang}</legend>
		
		<dl>
			<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
			<dd>
				<span>
					<input autocomplete="off" id="userInviteDialogUsernameInput" name="username" class="medium" value="" type="text" />
				</span>
			</dd>
		</dl>
	</fieldset>
</div>

<script>
	(function() {
		new WCF.Search.User('#userInviteDialogUsernameInput', function(user) {
			if (!$.wcfIsset('userInviteDialogUserID-' + user.objectID)) {
				$('.noFollowing').hide();
				
				$('#userInviteDialogUserlist').append('<dl>\
					<dt></dt>\
					<dd>\
						<label>\
							<input type="checkbox" id="userInviteDialogUserID-' + user.objectID + '" data-user-id="' + user.objectID + '" checked="checked" /> ' + user.label + '\
						</label>\
					</dd>\
				</dl>');
			}
			else {
				$('#userInviteDialogUserID-' + user.objectID).prop('checked', true);
			}
			
			$('#userInviteDialogUsernameInput').val('');
		}, false, ['{$__wcf->getUser()->username|encodeJS}'], false);
		
		$('#userInviteDialogFormSubmit').on('click', function(event) {
			var checked = $('#userInviteDialogUserlist input[type=checkbox]:checked');
			var userList = [];
			
			checked.each(function(k, v) {
				userList.push($(v).data('userID'));
			});
			
			if (userList.length) {
				new WCF.Action.Proxy({
					autoSend: true,
					data: {
						actionName: 'invite',
						className: 'chat\\data\\user\\UserAction',
						parameters: {
							recipients: userList
						}
					},
					success: function (data) {
						new WCF.System.Notification('{lang}wcf.global.success{/lang}').show();
					}
				});
			}
			
			$('#timsChatInviteDialog').wcfDialog('close');
		});
	})();
</script>