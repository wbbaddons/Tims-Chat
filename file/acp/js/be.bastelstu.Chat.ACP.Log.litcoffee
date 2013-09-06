Tims Chat 3
===========

This is the javascript file providing functions related to the message log for [##Tims Chat##](https://github.com/wbbaddons/Tims-Chat).

	### Copyright Information
	# @author	Maximilian Mader
	# @copyright	2010-2013 Tim Düsterhus
	# @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
	# @package	be.bastelstu.chat
	###

## Code

	(($, window) ->
		"use strict";
		
		_messageLogContent = null
		_hasContent = {}
		_proxy = null
		
		init = ->
			_messageLogContent = $ '#messageLogContent'
			
			activeMenuItem = _messageLogContent.data 'active'
			enableProxy = false
			
			_messageLogContent.find('div.tabMenuContent > .subTabMenuContent').each (index, container) ->
				containerID = $(container).wcfIdentify()
				
				unless $("##{containerID}").hasClass 'empty'
					_hasContent[containerID] = true
				else
					_hasContent[containerID] = false
					enableProxy = true
					
			if enableProxy
				_proxy = new WCF.Action.Proxy
					success: _success
					
					_messageLogContent.bind 'wcftabsbeforeactivate',
					_loadContent
					
			if not _hasContent[activeMenuItem]
				_loadContent {},
					newPanel: $("##{activeMenuItem}")
					newTab: $("##{activeMenuItem}").parent().find(".menu > ul > li").first()
					
		_loadContent = (event, ui) ->
			containerID = $(ui.newPanel).attr 'id'
			
			if $("##{$(ui.newPanel).attr('id')}").hasClass 'tabMenuContainer'
				containerID = $("##{containerID} > .subTabMenuContent").first().attr 'id'
				tab = $("##{containerID}").parent().find(".menu > ul > li").first()
			else
				tab = $ ui.newTab
			
			unless _hasContent[containerID]
				start = _messageLogContent.data('baseTime') + (tab.data('hour') * 3600) + (tab.data('minutes') * 60)
				
				_proxy.setOption 'data',
					actionName: 'getMessages'
					className: 'chat\\data\\message\\MessageAction'
					parameters:
						containerID: containerID
						start: start
						end: start + 1799
						roomID: _messageLogContent.data 'roomID'
				do _proxy.sendRequest
				
		_success = (data, textStatus, jqWHR) ->
			containerID = data.returnValues.containerID
			_hasContent[containerID] = true
			
			content = _messageLogContent.find "##{containerID}"
			unless data.returnValues.template is ''
				$("<div>#{data.returnValues.template}</div>").hide().appendTo content
				unless data.returnValues.noMessages
					content.addClass 'tabularBox'
				
			do content.children().first().show
		Log =
			TabMenu:
				init: init
				
		window.be ?= {}
		be.bastelstu ?= {}
		be.bastelstu.Chat ?= {}
		be.bastelstu.Chat.ACP ?= {}
		be.bastelstu.Chat.ACP.Log ?= {}
		window.be.bastelstu.Chat.ACP.Log = Log
	)(jQuery, @)
