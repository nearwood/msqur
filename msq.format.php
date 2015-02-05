<?php
//MSQ <=> INI <=> FE links/lookups

function getSchema()
{
	return array(// XML/INI Name => pretty name, [xAxisXmlName, yAxisXmlName]
		'veTable' => array('name' => 'VE Table', 'x' => 'frpm_table', 'y' => 'fmap_table', 'units' => '%', 'hot' => 'descending'),
		'veTable1' => array('name' => 'VE Table 1', 'x' => 'frpm_table1', 'y' => 'fmap_table1', 'units' => '%', 'hot' => 'descending'),
		'veTable2' => array('name' => 'VE Table 2', 'x' => 'frpm_table2', 'y' => 'fmap_table2', 'units' => '%', 'hot' => 'descending'),
		'advanceTable1' => array('name' => 'Timing Advance 1', 'x' => 'srpm_table1', 'y' => 'smap_table1', 'units' => 'degrees', 'hot' => 'ascending'),
		'advanceTable2' => array('name' => 'Timing Advance 2', 'x' => 'srpm_table2', 'y' => 'smap_table2', 'units' => 'degrees', 'hot' => 'ascending'),
		'afrTable1' => array('name' => 'AFR Targets 1', 'x' => 'arpm_table1', 'y' => 'amap_table1', 'hot' => 'ascending'),
		'afrTable2' => array('name' => 'AFR Targets 2', 'x' => 'arpm_table2', 'y' => 'amap_table2', 'hot' => 'ascending'),
//		'nCylinders' => array('name' => 'Cylinders'),
//		'engineType' => array('name' => 'Engine Type'),
		'reqFuel' => array('name' => 'Base Fuel (reqFuel)'),
//		'twoStroke' => array('name' => '# Strokes'),
//		'injType' => array('name' => 'Injection'),
//		'nInjectors' => array('name' => 'Injectors'),
		'egoType' => array('name' => 'O2 Sensor Type'),
		'crankingRPM' => array('name' => 'Cranking RPM Threshold'),
		'dwellcorr' => array('name' => 'Dwell Voltage Correction', 'y' => 'dwellvolts', 'units' => '%', 'hot' => 'ascending')
	);
}

function getEngineSchema()
{
	return array(
		'nCylinders' => null,
		'engineType' => null,
		'reqFuel' => null,
		'twoStroke' => null,
		'injType' => null,
		'nInjectors' => null,
		'egoType' => null
	);
}

?>
