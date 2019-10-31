/* msqur - MegaSquirt .msq file viewer web application
Copyright 2014-2019 Nicholas Earwood nearwood@gmail.com https://nearwood.dev

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>. */

"use strict";

var msqur = angular.module('msqur', []);

msqur.controller('BrowseController', function ($scope) {
	return true;
});

msqur.controller('SearchController', function ($scope) {
	
});

$(function() {
	$('div#upload').dialog({
		modal: true,
		autoOpen: false,
		title: "Upload Tune Files",
		width: "512px",
		buttons: {
			Upload: uploadClick,
			Cancel: function() { $(this).dialog('close'); }
		}
	});
	
	$('#btnUpload').click(function(e) {
		if (window.File && window.FileReader && window.FileList && window.Blob)
		{
			$('div#upload').dialog('open');
		} else {
			alert('The File APIs are not fully supported in this browser.');
			//TODO no ajax file upload
		}
	});
	
	$('#settingsIcon').click(function(e) {
		$('#settingsPanel').toggle();
	});
	
	$('select#firmware').change(function(){
		getFirmwareVersions($(this).children(":selected").html());
	});
	
	function getFirmwareVersions(fw) {
		$.ajax({
			url: "api.php?req=",
		}).done(function( html ) {
			$( "#results" ).append( html );
		});
	};
	
	var accordionOptions = {
		animate: false,
		active: false, collapsible: true, //to avoid hooking 'create' to make the first graph render
		heightStyle: "content",
		activate: doChart
	};
	
	$('div.group-curves').accordion(accordionOptions);
	$('div.group-tables').accordion(accordionOptions);
	$('div.constant').tooltip();
	
	Chart.defaults.global.animation = false;
	//Chart.defaults.global.responsive = true;
	
	//2D charts
	function doChart(event, ui) {
	//$('div.curve').each(function(i) {
		//var that = $(this); //ick
		var that = ui.newPanel;
		if (that.find('tbody').length == 0 || that.find('div.table').length != 0) return; //do nothing if panel is closing, or if 3d table
		
		//Find data
		var tbl = that.find('tbody').get(0);
		var data = tbl2data($(tbl));
		//var options = {};
		
		var ctx = that.find('canvas').get(0).getContext("2d");
		var chart = new Chart(ctx).Line(data); //, options);
		
	//});
	}
	
	function tbl2data(tbl)
	{
		var rows = tbl.find('tr');
		var lbls = [];
		var cells = [];
		
		rows.each(function(i) {
			var that = $(this); //ick
			
			//.html() gets first element in set, .text() all matched elements
			lbls.push(parseFloat(that.find('th').text()));
			cells.push(parseFloat(that.find('td').text()));
		});
		
		var data = {
			labels: lbls,
			datasets: [{label: "test", data: cells}]
		};
		
		return data;
	}
	
	function normalizeTable(table)
	{
		var min = Number.MAX_SAFE_INTEGER;
		var max = Number.MIN_SAFE_INTEGER;
		var nmin = 5;
		var nmax = 250;
		
		//Find min and max
		table.find('td').each(function(i) {
			var v = parseFloat(this.textContent);
			if (v < min) min = v;
			if (v > max) max = v;
		});
		
		//Precalculate some stuff
		var a = (nmax - nmin) / (max - min);
		var b = nmin - (a * min);
		
		//apply normalization
		table.find('td').each(function(i) {
			var v = parseFloat(this.textContent);
			var r = Math.round(a * v + b);
			this.textContent = "" + r;
		});
	}
	
	function resetTable(table)
	{
		//TODO Need to store old value (and new one if I care about client end)
	}
	
	function clearTableColor(table)
	{
		table.find('td').each(function(i) {
			this.style.backgroundColor = '';
		});
	}
	
	function colorTable(table, reverseColor)
	{//reverseColor could be an override, value passed in is currently ignored
		var min = Number.MAX_SAFE_INTEGER;
		var max = Number.MIN_SAFE_INTEGER;
		
		//Find min and max
		table.find('td').each(function(i) {
			var v = parseFloat(this.textContent);
			if (v < min) min = v;
			if (v > max) max = v;
		});
		
		if (table.attr('hot') == 'ascending')
			reverseColor = true;
		if (table.attr('hot') == 'descending')
			reverseColor = false;
		
		var range = (max - min);
		//console.debug("Range: " + range);
		var r = 0, g = 0, b = 0, percent = 0, intensity = 0.6;
		
		//MegaTune coloring scheme
		table.find('td').each(function(i) {
			var v = parseFloat(this.textContent);
			percent = (v - min) / range;
			
			if (reverseColor)
				percent = 1.0 - percent;
			
			if (percent < 0.33)
			{
				r = 1.0;
				g = Math.min(1.0, (percent * 3));
				b = 0.0;
			}
			else if (percent < 0.66)
			{
				r = Math.min(1.0, ((0.66 - percent) * 3));
				g = 1.0;
				b = 0.0;
			}
			else
			{
				r = 0.0;
				g = Math.min(1.0, ((1.0 - percent) * 3));
				b = 1.0 - g;
			}
			
			r = Math.round((r * intensity + (1.0 - intensity)) * 255);
			g = Math.round((g * intensity + (1.0 - intensity)) * 255);
			b = Math.round((b * intensity + (1.0 - intensity)) * 255);
			
			this.style.backgroundColor = 'rgb(' + r + ',' + g + ',' + b + ')';
		});
	}
	
	//FIXME Hack for tablesorter bug
	var hdrObj = {};
	for (var i = 1; i < 32; ++i)
		hdrObj[i] = {sorter: false};
		
	$('table').tablesorter({
		headers: hdrObj,
		sortList: [[0, 1]]
	});
	
	$('input#colorizeData').change(function () {
		if (this.checked)
			$('table.msq tbody').each(function(i) { colorTable($(this)); });
		else
			$('table.msq tbody').each(function(i) { clearTableColor($(this)); });
	});
	
	//default
	$('input#colorizeData').prop('checked', true);
	$('table.msq tbody').each(function(i) { colorTable($(this)); });
	
	$('input#normalizeData').change(function () {
		if (this.checked)
			$('table.msq.ve').each(function(i) { normalizeTable($(this)); });
		else
			$('table.msq.ve').each(function(i) { resetTable($(this)); });
	});
	
	function uploadAdd(e)
	{
		e.stopPropagation();
		e.preventDefault();
		
		var files = e.target.files || e.dataTransfer.files
		//TODO type check
		var output = [];
		for (var i = 0, f; f = files[i]; ++i)
		{
			output.push('<li><strong>', escape(f.name), '</strong> (', f.type || 'n/a', ') - ',
			f.size, ' bytes, last modified: ',
			f.lastModifiedDate ? f.lastModifiedDate.toLocaleDateString() : 'n/a',
			'</li>');
		}
		$('output#fileList').html('<ul>' + output.join('') + '</ul>');
	}
	
	function uploadDragOver(e)
	{
		e.stopPropagation();
		e.preventDefault();
		e.dataTransfer.dropEffect = 'copy';
	}
	
	function simpleValidation(s)
	{
		if (typeof s === 'string' && s.length > 0)
			return true;
		else
			return false;
	}
	
	function uploadClick()
	{
		//var files = $('input#fileSelect').val();
		var make = $('input#make').val();
		var model = $('input#code').val();
		var disp = $('input#displacement').val();
		var comp = $('input#compression').val();
		
		//put in array and map/reduce?
		if (simpleValidation(make) && simpleValidation(model) && simpleValidation(disp) && simpleValidation(comp))
		{
			$('div#upload form').submit();
		}
		else
		{
			//TODO some error msg
		}
	}
	
	function searchClick()
	{
		$('form#search').submit();
	}
	
	$('input#fileSelect').change(uploadAdd);
	var dropZone = document.getElementById('fileDropZone');
	dropZone.addEventListener('dragover', uploadDragOver);
	dropZone.addEventListener('drop', uploadAdd);
});
