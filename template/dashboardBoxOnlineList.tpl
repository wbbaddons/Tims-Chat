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
			var proxy = new WCF.Action.Proxy({
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
			
			$(function () {
				be.bastelstu.wcf.push.onMessage('be.bastelstu.chat.join', proxy.sendRequest.bind(proxy));
				be.bastelstu.wcf.push.onMessage('be.bastelstu.chat.leave', proxy.sendRequest.bind(proxy));
			});
		})(jQuery, this);
		//]]>
	</script>
</div>
