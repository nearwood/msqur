"use strict"

$(function() {
	var $makes = $('.browse #makes');
	var $models = $('.browse #models');
	var $firmware = $('.browse #firmware');
	var $versions = $('.browse #versions');
	
	apiGet('engineMakes', $makes);
	apiGet('engineModels', $models);
	apiGet('firmwareList', $firmware);
	apiGet('firmwareVersions', $versions);
	
	function apiGet(method, $target)
	{
		$.get("api.php?method=" + method, function(data) {
			if (method in data) {
				data[method].forEach(function(a) {
					$target.append('<div><a href="">' + a + '</a></div>');
				});
			}
		}, "json");
	}
});
