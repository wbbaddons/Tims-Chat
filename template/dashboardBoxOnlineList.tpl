{capture assign='roomList'}{include application='chat' file='boxRoomList' showEmptyRooms=false}{/capture}

<div id="chatDashboardBoxOnlineListContainer"{if !$roomList|trim} style="display: none;"{/if}>
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
					actionName: 'getBoxRoomList',
					className: 'chat\\data\\room\\RoomAction',
					parameters: {
						showEmptyRooms: 0
					}
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
