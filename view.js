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
	el.text("Loading MSQ...");
	
	var author = xml.find("bibliography").attr('author');
	el.text(author);
}

function parseVE(xml, el)
{
	
}

/**
 * create table el and <tr>s and shit
 */
function createTable(xml, data, xaxis, yaxis)
{
	
}
