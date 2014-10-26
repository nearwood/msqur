$(function() {
	
	$('div#upload').dialog({
		modal: true,
		autoOpen: false,
		title: "Upload Tune Files",
		width: "450px",
		buttons: {
			Upload: upload,
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
	
	function colorTable(table, reverseColor)
	{//reverseColor could be an override, value passed in is currently ignored
		var colors = new Array();
		
		var min = Number.MAX_SAFE_INTEGER;
		var max = Number.MIN_SAFE_INTEGER;
		
		//Find min and max
		table.find('td').each(function(i) {
			var v = parseFloat(this.textContent);
			if (v < min) min = v;
			else if (v > max) max = v;
		});
		
		if (table.attr('hot') == 'ascending')
			reverseColor = true;
		if (table.attr('hot') == 'descending')
			reverseColor = false;
		
		var range = (max - min);
		console.debug("Range: " + range);
		var r = 0, g = 0, b = 0, percent = 0, intensity = 0.6;
		
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
			
			//this.css('background-color', 'rgb(' + r + ',' + g + ',' + b + ')');
			this.style.backgroundColor = 'rgb(' + r + ',' + g + ',' + b + ')';
		});
		
		return colors;
	}
	
	//FIXME Hack for tablesorter bug
	var hdrObj = {};
	for (var i = 1; i < 32; ++i)
		hdrObj[i] = {sorter: false};
		
	$('table').tablesorter({
		headers: hdrObj,
		sortList: [[0, 1]]
	});
	
	$('table').each(function(i) { colorTable($(this)); });
	
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
	
	function upload()
	{
		//TODO Check files
		$('div#upload form').submit();
	}
	
	$('input#fileSelect').change(uploadAdd);
	var dropZone = document.getElementById('fileDropZone');
	dropZone.addEventListener('dragover', uploadDragOver);
	dropZone.addEventListener('drop', uploadAdd);
});
