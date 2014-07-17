


function view(file, targetElement)
{
	var xhr = new XMLHttpRequest();
	xhr.open('GET', file);
	xhr.onreadystatechange = function() {
		switch (xhr.readyState)
		{
			case 4:
				//xhr.responseXML
				
				parseMSQ($($.parseXML(xhr.responseText)), targetElement);
				break;
		}
		
	};
	xhr.send();
}

function parseMSQ(xml, el)
{
	var author = xml.find("bibliography").attr('author');
	$(el).text(author);
}
