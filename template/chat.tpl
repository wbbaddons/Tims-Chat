{include file='documentHeader'}

<head>
	<title>{lang}wcf.chat.title{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude' sandbox=false}
	<script type="text/javascript">
	//<![CDATA[
		new TimWolla.WCF.Chat({$roomID},1);
	//]]>
	</script>
</head>

<body id="tpl{$templateName|ucfirst}">
{include file='header' sandbox=false}

<header class="mainHeading">
	<img src="{icon size='L'}chat{/icon}" alt="" />
	<hgroup>
		<h1>{lang}wcf.chat.title{/lang}</h1>
	</hgroup>
</header>


{include file='footer' sandbox=false}
</body>
</html>