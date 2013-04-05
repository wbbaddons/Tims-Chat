###
# node.js Pushserver for Tims Chat.
# 
# @author	Tim Düsterhus
# @copyright	2010-2013 Tim Düsterhus
# @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
# @package	be.bastelstu.chat
# @subpackage	nodePush
###
process.title = 'nodePush - Tims Chat'

io = require 'socket.io'
net = require 'net'
fs = require 'fs'

config = require '../config.js'

log = (message) ->
	console.log "[be.bastelstu.chat.nodePush] #{message}"

class Server
	constructor: () ->
		log 'Starting Pushserver for Tims Chat'
		log "PID is #{process.pid}"
		log "Using port: #{config.port}"
		
		@initUnixSocket()
		@initSocketIO()
		
		process.on 'exit', @shutdown.bind @
		process.on 'uncaughtException', @shutdown.bind @
		process.on 'SIGINT', @shutdown.bind @
		process.on 'SIGTERM', @shutdown.bind @
		
		setInterval =>
			@socket.sockets.emit 'newMessage'
		, 60e3
	initSocketIO: () ->
		log 'Initializing socket.io'
		@socket = io.listen config.port
		
		@socket.set 'log level', 1
		@socket.set 'browser client etag', true
		@socket.set 'browser client minification', true
		@socket.set 'browser client gzip', true
		
		@socket.configure 'development', =>
			@socket.set 'log level', 3
			@socket.set 'browser client etag', false
			@socket.set 'browser client minification', false
	initUnixSocket: () ->
		log 'Initializing Unix-Socket'
		socket = net.createServer (c) =>
			setTimeout =>
				@socket.sockets.emit 'newMessage'
			, 20
			
			c.end()
		
		socket.listen "#{__dirname}/../data.sock"
		fs.chmod "#{__dirname}/../data.sock", '777'
	shutdown: () ->
		return unless fs.existsSync "#{__dirname}/../data.sock"
		
		log 'Shutting down'
		fs.unlinkSync "#{__dirname}/../data.sock"
		process.exit()

new Server()