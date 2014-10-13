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