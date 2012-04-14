{include file='header'}
	
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('\\wcf\\data\\chat\\room\\ChatRoomAction', $('.chatRoomRow'));
		new WCF.Sortable.List('chatRoomList', '\\wcf\\data\\chat\\room\\ChatRoomAction', {@$startIndex-1});
	});
	//]]>
</script>
	
<header class="box48 boxHeadline">
	<img src="{@$__wcf->getPath('wcf')}icon/chat1.svg" alt="" class="icon48" />
	<hgroup>
		<h1>{lang}wcf.acp.chat.room.list{/lang}</h1>
	</hgroup>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="ChatRoomList" link="pageNo=%d"}
	
	{if $__wcf->session->getPermission('admin.content.chat.canAddRoom')}
		<nav>
			<ul>
				<li><a href="{link controller='ChatRoomAdd'}{/link}" title="{lang}wcf.acp.chat.room.add{/lang}" class="button"><img src="{@$__wcf->getPath('wcf')}icon/add1.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.chat.room.add{/lang}</span></a></li>
			</ul>
		</nav>
	{/if}
</div>
{hascontent}
	<section id="chatRoomList" class="container containerPadding sortableListContainer marginTop shadow">
		<ol class="sortableList" data-object-id="0" start="{$startIndex}">
			{content}
				{foreach from=$objects item=chatRoom}
					<li class="sortableNode sortableNoNesting chatRoomRow" data-object-id="{@$chatRoom->roomID}">
						<span class="sortableNodeLabel">
							{if $__wcf->session->getPermission('admin.content.chat.canEditRoom')}
								<a href="{link controller='ChatRoomEdit' id=$chatRoom->roomID}{/link}">{$chatRoom->title|language}</a>
							{else}
								{$chatRoom->title|language}
							{/if}
							
							<span class="statusDisplay sortableButtonContainer">
								{if $__wcf->session->getPermission('admin.content.chat.canEditRoom')}
									<a href="{link controller='ChatRoomEdit' id=$chatRoom->roomID}{/link}"><img src="{@$__wcf->getPath('wcf')}icon/edit1.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip icon16" /></a>
								{/if}
								{if $__wcf->session->getPermission('admin.content.chat.canDeleteRoom')}
									<img src="{@$__wcf->getPath('wcf')}icon/delete1.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="jsDeleteButton jsTooltip icon16" data-object-id="{@$chatRoom->roomID}" data-confirm-message="{lang}wcf.acp.chat.delete.sure{/lang}" />
								{/if}
							</span>
						</span>
						<ol class="sortableList" data-object-id="{@$chatRoom->roomID}"></ol></li>
					</li>
				{/foreach}
			{/content}
		</ol>
		<div class="formSubmit">
			<button class="button" data-type="submit">{lang}wcf.global.button.submit{/lang}</button>
		</div>
	</section>
{hascontentelse}
	<p class="warning">{lang}wcf.acp.chat.room.noneAvailable{/lang}</p>
{/hascontent}


{include file='footer'}
