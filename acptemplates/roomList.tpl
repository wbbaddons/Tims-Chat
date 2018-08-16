{include file='header' pageTitle='chat.acp.room.list'}

<script data-relocate="true">
	$(function() {
		require([ 'WoltLabSuite/Core/Ui/Sortable/List' ], function (UiSortableList) {
			new UiSortableList({ containerId: 'roomNodeList'
			                   , className:   'chat\\data\\room\\RoomAction'
			                   })
		})

		new WCF.Action.Delete('chat\\data\\room\\RoomAction', '#roomNodeList')
	})
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}chat.acp.room.list{/lang}</h1>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='RoomAdd' application='chat'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}chat.acp.room.add{/lang}</span></a></li>

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="RoomList" application="chat" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{hascontent}
	<div id="roomNodeList" class="section sortableListContainer">
		<ol id="roomContainer0" class="sortableList" data-object-id="0">
			{content}
				{foreach from=$objects item=room}
					<li class="sortableNode sortableNoNesting" data-object-id="{@$room->roomID}">
						<span class="sortableNodeLabel">
							<a href="{link controller='RoomEdit' application='chat' object=$room}{/link}">{$room}</a>

							<span class="statusDisplay sortableButtonContainer">
								<span class="icon icon16 fa-arrows sortableNodeHandle"></span>
								<a href="{link controller='RoomEdit' application='chat' object=$room}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
								<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$room->roomID}" data-confirm-message-html="{lang __encode=true}chat.acp.room.delete.sure{/lang}"></span>
								{event name='itemButtons'}
							</span>
						</span>
					</li>
				{/foreach}
			{/content}
		</ol>
	</div>

	<div class="formSubmit">
		<button class="button buttonPrimary" data-type="submit">{lang}wcf.global.button.saveSorting{/lang}</button>
	</div>
{hascontentelse}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/hascontent}

<footer class="contentFooter">
	{hascontent}
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}

	<nav class="contentFooterNavigation">
		<ul>
			<li><a href="{link controller='RoomAdd' application='chat'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}chat.acp.room.add{/lang}</span></a></li>

			{event name='contentFooterNavigation'}
		</ul>
	</nav>
</footer>

{include file='footer'}

