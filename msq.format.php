<?php
//MSQ <=> INI <=> FE links/lookups

function getSchema()
{
	return array(// XML/INI Name => pretty name, [xAxisXmlName, yAxisXmlName]
		'veTable' => array('name' => 'VE Table', 'x' => 'frpm_table', 'y' => 'fmap_table', 'units' => '%', 'hot' => 'descending', 'group' => 'fuel'),
		'veTable1' => array('name' => 'VE Table 1', 'x' => 'frpm_table1', 'y' => 'fmap_table1', 'units' => '%', 'hot' => 'descending', 'group' => 'fuel'),
		'veTable2' => array('name' => 'VE Table 2', 'x' => 'frpm_table2', 'y' => 'fmap_table2', 'units' => '%', 'hot' => 'descending', 'group' => 'fuel'),
		'advanceTable1' => array('name' => 'Timing Advance 1', 'x' => 'srpm_table1', 'y' => 'smap_table1', 'units' => 'degrees', 'hot' => 'ascending', 'group' => 'timing'),
		'advanceTable2' => array('name' => 'Timing Advance 2', 'x' => 'srpm_table2', 'y' => 'smap_table2', 'units' => 'degrees', 'hot' => 'ascending', 'group' => 'timing'),
		'afrTable1' => array('name' => 'AFR Targets 1', 'x' => 'arpm_table1', 'y' => 'amap_table1', 'hot' => 'ascending', 'group' => 'afr'),
		'afrTable2' => array('name' => 'AFR Targets 2', 'x' => 'arpm_table2', 'y' => 'amap_table2', 'hot' => 'ascending', 'group' => 'afr'),
//		'nCylinders' => array('name' => 'Cylinders'),
//		'engineType' => array('name' => 'Engine Type'),
		'reqFuel' => array('name' => 'Base Fuel (reqFuel)', 'group' => 'setup'),
//		'twoStroke' => array('name' => '# Strokes'),
//		'injType' => array('name' => 'Injection'),
//		'nInjectors' => array('name' => 'Injectors'),
		'egoType' => array('name' => 'O2 Sensor Type', 'group' => 'setup'),
		'crankingRPM' => array('name' => 'Cranking RPM Threshold', 'group' => 'start'),
		'dwellcorr' => array('name' => 'Dwell Voltage Correction', 'y' => 'dwellvolts', 'units' => '%', 'hot' => 'ascending', 'group' => 'setup'),
		
		'triggerOffset' => array('name' => 'Trigger Offset', 'group' => 'setup'),
		'dwelltime' => array('name' => 'Base Dwell', 'group' => 'setup'),
		
		//'taeBins' => array('name' => 'Tau AE', 'y' => 'taeRates', 'units' => '%', 'hot' => 'ascending')
		//aeEndPW
		 
		'wueBins' => array('name' => 'Warmup Enrichment', 'y' => 'tempTable', 'units' => '%', 'hot' => 'ascending', 'group' => 'start'),
		'cold_adv_table' => array('name' => 'Cold Advance', 'y' => 'tempTable', 'units' => '°', 'hot' => 'ascending', 'group' => 'start'),
		'primePWTable' => array('name' => 'Prime PW', 'y' => 'tempTable', 'units' => 'ms', 'hot' => 'ascending', 'group' => 'start'),
		'matRetard' => array('name' => 'MAT Timing Retard', 'y' => 'matTemps', 'units' => '°', 'hot' => 'ascending', 'group' => 'timing'),
		'crankPctTable' => array('name' => 'Crank PW', 'y' => 'tempTable', 'units' => '%', 'hot' => 'ascending', 'group' => 'start'),
		//'mafflow' => array('name' => 'MAF Flow Rate', 'y' => 'mafv', 'hot' => 'ascending', 'group' => 'setup')
		//mafflow??? 64
		//advanceTable3 srpm_table3 smap_table3
		//veTable3  frpm_table3
		//temp_table_p5?
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
