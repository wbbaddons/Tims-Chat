{capture assign='roomList'}
	{foreach from=$rooms item='room'}
		{assign var='users' value=$room->getUsers()}
		
		{if $users|count > 0}
			<li>
				<div>
					<div>
						<hgroup class="containerHeadline">
							<h3><a href="{link application='chat' controller='Chat' object=$room}{/link}">{$room}</a> <span class="badge">{#$users|count}</span></h3>
							<p>{$room->topic|language}</p>
						</hgroup>
						
						<ul class="dataList">
							{foreach from=$users item='user'}
								<li><a href="{link controller='User' object=$user}{/link}" class="userLink" data-user-id="{$user->userID}">{$user}</a></li>
							{/foreach}
						</ul>
					</div>
				</div>
			</li>
		{/if}
	{/foreach}
{/capture}
{if $onlyList|isset}
	{@$roomList}
{else}
	<div id="chatDashboardBoxOnlineListContainer" {if !$roomList|trim} style="display: none;"{/if}>
		<header class="boxHeadline boxSubHeadline">
			<h2>{lang}chat.header.menu.chat{/lang}</h2>
		</header>

		<div class="container marginTop">
			<ul class="containerList">
				{@$roomList}
			</ul>
		</div>
		<script data-relocate="true">
			//<![CDATA[
			(function($, window, undefined) {
				proxy = new WCF.Action.Proxy({
					data: {
						actionName: 'getDashboardRoomList',
						className: 'chat\\data\\room\\RoomAction'
					},
					showLoadingOverlay: false,
					suppressErrors: true,
					success: function(data) {
						if (data.returnValues.template) $('#chatDashboardBoxOnlineListContainer').show();
						else $('#chatDashboardBoxOnlineListContainer').hide();
						
						$('#chatDashboardBoxOnlineListContainer ul').html(data.returnValues.template);
					}
				});
				
				be.bastelstu.wcf.nodePush.onMessage('be.bastelstu.chat.join', $.proxy(proxy.sendRequest, proxy));
				be.bastelstu.wcf.nodePush.onMessage('be.bastelstu.chat.leave', $.proxy(proxy.sendRequest, proxy));
			})(jQuery, this);
			//]]>
		</script>
	</div>
{/if}