#!/usr/bin/env nodejs
var ubiquiti_info = require ("ubiquiti_info");

function debug (str) {
	//process.stderr.write ("[" + (new Date ().toISOString ()) + "] " + str + "\n");
}

var throttle_limit = 20;
var throttle_queue = [ ];
function throttle_dequeue () {
	var args = throttle_queue.shift ();
	var fn = args.shift ();
	var callback = args.pop ();
	args.push (function () {
		var callback_args = [].slice.apply (arguments);
		if (throttle_queue.length) {
			throttle_dequeue ();
		} else {
			throttle_limit++;
		}
		callback.apply (null, callback_args);
	});
	fn.apply (null, args);
}
function throttle () {
	var args = [].slice.apply (arguments);
	throttle_queue.push (args);
	if (throttle_limit) {
		throttle_limit--;
		throttle_dequeue ();
	}
}

function consultar (id, host, user, password) {
	debug ("consultar: " + JSON.stringify ({ id: id, host: host, user: user, password: password }));
	throttle (ubiquiti_info, host, user, password, function (err, data) {
		if (err) {
			process.stdout.write (JSON.stringify ({ id: id, error: err.message }) + "\n");
			return;
		}
		debug ("obtenido: " + JSON.stringify ({ id: id, data: data }).substr (0, 40));
		process.stdout.write (JSON.stringify ({ id: id, data: data }) + "\n");
	});
}

var input = "";
process.stdin.on ("data", function (chunk) {
	input += chunk;
	if (input.indexOf ("\n") >= 0) {
		var hosts = input.split ("\n");
		input = hosts.pop ();
		hosts.forEach (function (host) {
			debug ("host: " + host);
			host = JSON.parse (host);	// id, ip, puerto, usuario, password
			debug ("parsed host: " + JSON.stringify (host));
			if (host.puerto == 443) {
				consultar (host.id, "https://" + host.ip + ":" + host.puerto, host.usuario, host.password);
			} else {
				consultar (host.id, "http://" + host.ip + ":" + host.puerto, host.usuario, host.password);
			}
		});
	}
});
debug ("test debug");
process.stdin.resume ();

