/**
 * Tables (data, x axis, y axis)
 * single constants with labels
 * 
 * 
 * 
 * 
 */

var msqApp = angular.module('msqApp', []);

msqApp.controller('msqParse', function ($scope) {
	
	$scope.loadMsq = function(file) {
		var xhr = new XMLHttpRequest();
		xhr.open('GET', file);
		xhr.onreadystatechange = function() {
			switch (xhr.readyState)
			{
				case 4:
					//xhr.responseXML
					var xml = $($.parseXML(xhr.responseText));
					$scope.msq = parseMSQ(xml);
					break;
			}
		};
		xhr.send();
	};
	
	$scope.msq = {tables: [{name: "VE TAble"}]};
	
});

function parseMSQ(xml)
{
	var msq = {};
	console.debug("Loading MSQ...");
	
	var fileVersion = parseFloat(xml.find('versionInfo').attr('fileFormat'));
	console.debug("File format: " + fileVersion);
	if (fileVersion == 4.0)
	{
		//load 4.0 json relationships
	}
	
	msq.author = xml.find("bibliography").attr('author');
	msq.date = xml.find("bibliography").attr('writeDate');
	msq.format = fileVersion;
	msq.sig = xml.find("versionInfo").attr('signature');
	
	console.debug("Signature: " + msq.sig);
	
	msq.tables = [];
	var tables = msqFormat4.tables;
	for (var i = 0; i < tables.length; i++)
	{
		var t = tables[i];
		var tbl = getTable(xml, t.data, t.x, t.y);
		msq.tables.push({"name": t.name, "dataRows": tbl.dataRows, "xAxis": tbl.xAxis, "yAxis": tbl.yAxis});
	}
	
	//msq.tables = [{name: "VE Table", dataRows: ["3"], xAxis: ["x1"], yAxis: ["y1"]}];
	
	return msq;
}

/**
 * create table el and <tr>s and shit
 */
function getTable(xml, data, xaxis, yaxis)
{
	var tbl = {dataRows: [], xAxis: [], yAxis: []}
	var d = xml.find('constant[name=' + data + ']');
	var dRows = parseInt(d.attr('rows'));
	var dCols = parseInt(d.attr('cols')); //TODO Check these against x,y axisses
	d = d.text().trim().split(/\s+/);
	if (d.length == dRows * dCols)
	{
		//rows seems to be the first indicator
		var x = xml.find('constant[name=' + xaxis + ']');
		var xCount = parseInt(x.attr('rows'));
		x = x.text().trim().split(/\s+/);
		var y = xml.find('constant[name=' + yaxis + ']');
		var yCount = parseInt(y.attr('rows'));
		y = y.text().trim().split(/\s+/);
		if (xCount == dCols && yCount == dRows)
		{//data and axis cell counts match
			for (var r = 0; r < yCount; r++)
			{
				var dr = [];
				for (var c = 0; c < xCount; c++)
				{
					if (r == 0)
					{
						tbl.xAxis.push(x[c]);
						dr.push(d[c]);
					}
					else dr.push(d[r * xCount + c]);
				}
				
				tbl.yAxis.push(y[r]);
				tbl.dataRows.push(dr);
			}
		}
		else console.error("Data/Axis count mismatch");
	}
	else console.error("Error parsing table data");
	
	return tbl;
	
}

msqFormat4 = {constants: [{name: "O2 Sensor Type", id: 'egoType'}],
	tables: [{name: "VE Table", data: 'veTable1', x: 'frpm_table1', y: 'fmap_table1'}]
};
