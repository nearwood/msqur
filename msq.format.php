<?php
//MSQ <=> INI <=> FE links/lookups

function getLookup()
{
	return array(// XML/INI Name => pretty name, [xAxisXmlName, yAxisXmlName]
		'veTable' => array('name' => 'VE Table', 'x' => 'frpm_table', 'y' => 'fmap_table', 'units' => '%', 'hot' => 'descending'),
		'veTable1' => array('name' => 'VE Table 1', 'x' => 'frpm_table1', 'y' => 'fmap_table1', 'units' => '%', 'hot' => 'descending'),
		'veTable2' => array('name' => 'VE Table 2', 'x' => 'frpm_table2', 'y' => 'fmap_table2', 'units' => '%', 'hot' => 'descending'),
		'advanceTable1' => array('name' => 'Timing Advance', 'x' => 'srpm_table1', 'y' => 'smap_table1', 'units' => 'degrees', 'hot' => 'ascending'),
		'advanceTable2' => array('name' => 'Timing Advance', 'x' => 'srpm_table2', 'y' => 'smap_table2', 'units' => 'degrees', 'hot' => 'ascending'),
		'afrTable1' => array('name' => 'AFR Targets', 'x' => 'arpm_table1', 'y' => 'amap_table1', 'hot' => 'ascending'),
		'egoType' => array('name' => 'O2 Sensor Type'),
		'nCylinders' => array('name' => 'Cylinders')
	);
}

?>
