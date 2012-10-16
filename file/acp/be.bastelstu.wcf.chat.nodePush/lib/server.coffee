###
# node.js Pushserver for Tims Chat.
# 
# @author	Tim Düsterhus
# @copyright	2010-2012 Tim Düsterhus
# @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
# @package	be.bastelstu.wcf.chat
# @subpackage	nodePush
###
process.title = 'nodePush - Tims Chat'

io = require 'socket.io'
net = require 'net'
fs = require 'fs'

config = require('../config.js')

log = (message) ->
	console.log '[be.bastelstu.wcf.chat.nodePush] '+message

class Server
	constructor: () ->
		if process.cwd().substring(process.cwd().length - 3) isnt 'lib'
			console.error 'Please run me via bin/run.sh'
			process.exit 1
		log 'Starting Pushserver for Tims Chat'
		log 'PID is ' + process.pid
		log 'Using port: ' + config.port
		
		@initUnixSocket()
		@initSocketIO()
		
		process.on 'exit', @shutdown.bind @
		process.on 'uncaughtException', @shutdown.bind @
		process.on 'SIGINT', @shutdown.bind @
		process.on 'SIGTERM', @shutdown.bind @
		
		setInterval (() ->
			@socket.sockets.emit 'newMessage'
		).bind(@), 60e3
	initSocketIO: () ->
		log 'Initializing socket.io'
		@socket = io.listen config.port
		
		@socket.set 'log level', 1
		@socket.set 'browser client etag', true
		@socket.set 'browser client minification', true
		@socket.set 'browser client gzig', true
		
		@socket.configure 'development', (() ->
			@socket.set 'log level', 3
			@socket.set 'browser client etag', false
			@socket.set 'browser client minification', false
		).bind(@)
	initUnixSocket: () ->
		log 'Initializing Unix-Socket'
		socket = net.createServer ((c) ->
			setTimeout (() ->
				@socket.sockets.emit 'newMessage'
			).bind(@), 20
			c.end()
		).bind(@)
		
		socket.listen process.cwd() + '/../data.sock'
		fs.chmod process.cwd() + '/../data.sock', '777'
	shutdown: () ->
		return unless fs.existsSync process.cwd() + '/../data.sock'
		
		log 'Shutting down'
		fs.unlinkSync process.cwd() + '/../data.sock'
		process.exit()

new Server()