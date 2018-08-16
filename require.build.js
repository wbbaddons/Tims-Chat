(function () {
	var config = {
		name: "_Meta",
		out: "Bastelstu.be.Chat.js",
		useStrict: true,
		preserveLicenseComments: false,
		optimize: 'none',
		excludeShallow: [
			'_Meta'
		],
		rawText: {
			'_Meta': 'define([], function() {});'
		},
		paths: {
			'Bastelstu.be': 'files_wcf/js/Bastelstu.be'
		},
		onBuildRead: function(moduleName, path, contents) {
			if (!process.versions.node) {
				throw new Error('You need to run node.js');
			}
			
			if (moduleName === '_Meta') {
				if (global.allModules === undefined) {
					var fs   = module.require('fs'),
					    path = module.require('path');
					global.allModules = [];
					
					var queue = ['Bastelstu.be'];
					var folder;
					while (folder = queue.shift()) {
						var files = fs.readdirSync('files_wcf/js/' + folder);
						for (var i = 0; i < files.length; i++) {
							var filename = path.join(folder, files[i]).replace(/\\/g, '/');
							
							if (path.extname(filename) === '.js') {
								global.allModules.push(filename);
							}
							else if (fs.statSync('files_wcf/js/' + filename).isDirectory()) {
								queue.push(filename);
							}
						}
					}
				}
				
				return 'define([' + global.allModules.map(function (item) { return "'" + item.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/\.js$/, '') + "'"; }).join(', ') + '], function() { });';
			}
			
			return contents;
		}
	};
	
	var _isSupportedBuildUrl = require._isSupportedBuildUrl;
	require._isSupportedBuildUrl = function (url) {
		var result = _isSupportedBuildUrl(url);
		if (!result) return result;
		if (Object.keys(config.rawText).some(module => url.endsWith(`${module}.js`))) return result;

		var fs = module.require('fs');
		try {
			fs.statSync(url);
		}
		catch (e) {
			console.log('Unable to find module:', url, 'ignoring.');

			return false;
		}
		return true;
	};
	
	if (module) module.exports = config;
	
	return config;
})();
