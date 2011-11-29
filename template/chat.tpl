{include file='documentHeader'}

<head>
	<title>{lang}wcf.chat.title{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude' sandbox=false}
</head>

<body id="tpl{$templateName|ucfirst}">
{include file='header' sandbox=false}

<header class="mainHeading">
	<img src="{icon size='L'}chat1{/icon}" alt="" />
	<hgroup>
		<h1>{lang}wcf.chat.title{/lang}</h1>
	</hgroup>
</header>

{foreach from=$smilies item='smiley'}
<img src="{$smiley->getURL()}" alt="{$smiley->smileyCode}" class="smiley" /><br />
{/foreach}

<script type="text/javascript">
	//<![CDATA[
		TimWolla.WCF.Chat.init({$room->roomID}, 1);
	//]]>
</script>

{include file='footer' sandbox=false}
</body>
</html>