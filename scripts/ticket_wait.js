$(document).ready(function() {

	$('div#sale > em').html('(This page will automatically refresh)');
	
	var endTime = Date.now() + time_left;
	var refresh = Date.now() + 1800000;
	
	window.setInterval(function() {
		var dnow = Date.now();
		
		if (dnow > refresh || dnow >= endTime) {
			window.location.reload();
			return;
		}
		
		dnow = Math.floor((endTime - dnow) / 1000);
		
		var c = '';
		c = dnow % 60 + ' seconds';
		
		dnow = Math.floor(dnow / 60);
		c = dnow % 60 + ' minutes, ' + c;
		
		dnow = Math.floor(dnow / 60);
		c = dnow % 24 + ' hours, ' + c;
		
		dnow = Math.floor(dnow / 24);
		c = dnow + ' days, ' + c;
		
		$('div#sale > h4').html(c);
		
	}, 1000);
	
});
