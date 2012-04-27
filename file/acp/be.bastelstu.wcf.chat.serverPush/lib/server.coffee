###
# node.js Pushserver for Tims Chat.
# 
# @author	Tim Düsterhus
# @copyright	2010-2012 Tim Düsterhus
# @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
# @package	be.bastelstu.wcf.chat
###

io = require 'socket.io'

config = require('../config.js').config

log = (message) ->
	console.log '[be.bastelstu.wcf.chat.serverPush] '+message

class Server
	constructor: () ->
		log 'Starting Pushserver for Tims Chat'
		log 'Using port: ' + config.port
		
		@socket = io.listen config.port
		
		setInterval((() ->
			@socket.sockets.emit 'newMessage'
		).bind(@), 5000)

new Server()