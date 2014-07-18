/**
 * Tables (data, x axis, y axis)
 * single constants with labels
 * 
 * 
 * 
 * 
 */


function view(file, targetElement)
{
	var xhr = new XMLHttpRequest();
	xhr.open('GET', file);
	xhr.onreadystatechange = function() {
		switch (xhr.readyState)
		{
			case 4:
				//xhr.responseXML
				var xml = $($.parseXML(xhr.responseText));
				parseMSQ(xml, targetElement);
				break;
		}
		
	};
	xhr.send();
}

function parseMSQ(xml, el)
{
	el = $(el);
	console.debug("Loading MSQ...");
	
	var fileVersion = parseFloat(xml.find('versionInfo').attr('fileFormat'));
	console.debug("File format: " + fileVersion);
	if (fileVersion == 4.0)
	{
		//load 4.0 json relationships
	}
	
	var author = xml.find("bibliography").attr('author');
	//el.text(author);
	
	var tables = msqFormat4.tables;
	for (var i = 0; i < tables.length; i++)
	{
		var t = tables[i];
		var tbl = createTable(xml, t.data, t.x, t.y);
	}
}

/**
 * create table el and <tr>s and shit
 */
function createTable(xml, data, xaxis, yaxis)
{
	var tbl = document.createElement('table');
	
	
	
	return tbl;
}

msqFormat4 = {constants: [{name: "O2 Sensor Type", id: 'egoType'],
	tables: [{name: "VE Table", data: 'veTable1', x: 'frpm_table1', y: 'fmap_table1'}]
};
