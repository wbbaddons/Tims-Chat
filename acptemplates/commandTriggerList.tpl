{include file='header' pageTitle='chat.acp.command.trigger.list'}

<script data-relocate="true">
	$(function() {
		new WCF.Action.Delete('chat\\data\\command\\CommandTriggerAction', '.jsTriggerRow');
	});
</script>


<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}chat.acp.command.trigger.list{/lang}</h1>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='CommandTriggerAdd' application='chat'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}chat.acp.command.trigger.add{/lang}</span></a></li>

			{event name='contentHeaderNavigation'}
		</ul>
</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="CommandTriggerList" application="chat" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{hascontent}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnTriggerID{if $sortField == 'triggerID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='CommandTriggerList' application='chat'}pageNo={@$pageNo}&sortField=triggerID&sortOrder={if $sortField == 'triggerID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnTrigger{if $sortField == 'commandTrigger'} active {@$sortOrder}{/if}"><a href="{link controller='CommandTriggerList' application='chat'}pageNo={@$pageNo}&sortField=commandTrigger&sortOrder={if $sortField == 'commandTrigger' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}chat.acp.command.trigger{/lang}</a></th>
					<th class="columnText columnClassName{if $sortField == 'className'} active {@$sortOrder}{/if}"><a href="{link controller='CommandTriggerList' application='chat'}pageNo={@$pageNo}&sortField=className&sortOrder={if $sortField == 'className' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}chat.acp.command.className{/lang}</a></th>

					{event name='columnHeads'}
				</tr>
			</thead>

			<tbody>
			{content}
				{foreach from=$objects item=trigger}
					<tr class="jsTriggerRow">
						<td class="columnIcon">
							<a href="{link controller='CommandTriggerEdit' object=$trigger application='chat'}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$trigger->triggerID}" data-confirm-message-html="{lang __encode=true}chat.acp.command.trigger.delete.sure{/lang}"></span>

							{event name='rowButtons'}
						</td>

						<td class="columnID">{@$trigger->triggerID}</td>
						<td class="columnTitle columnTrigger"><a href="{link controller='CommandTriggerEdit' object=$trigger application='chat'}{/link}">/{$trigger->commandTrigger}</a></td>
						<td class="columnText columnClassName">{$trigger->className}</td>

						{event name='columns'}
					</tr>
				{/foreach}
			{/content}
			</tbody>
		</table>
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
			<li><a href="{link controller='CommandTriggerAdd' application='chat'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}chat.acp.command.trigger.add{/lang}</span></a></li>

			{event name='contentFooterNavigation'}
		</ul>
	</nav>
</footer>

{include file='footer'}

