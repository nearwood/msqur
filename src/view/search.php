<div ng-controller="SearchController">
	<form id="search" action="search.php" method="get">
		<fieldset>
		<legend>Search Options</legend>
		<table>
			<tr>
				<th><label for="make">Engine Make</label></th>
				<th><label for="ecode">Engine Code</label></th>
				<th><label for="cylinders">Cylinders</label></th>
				<th><label for="liters">Displacement (liters)</label></th>
				<th><label for="compression">Compression (X:1)</label></th>
				<th><label for="aspiration">Aspiration</label></th>
				<th><label for="firmware">Firmware/Version (signature)</label></th>
				<!-- th><label for="uploadDate">Upload Date</label></th -->
			</tr>
			<tr>
				<td><input name="make" type="text" placeholder="Nissan" maxlength="32" style="width:5em;"/></td>
				<td><input name="ecode" type="text" placeholder="VG30" maxlength="32" style="width:5em;"/></td>
				<td><input name="cylinders" type="number" min="0" max="99" style="width:4em;"/></td>
				<td><input name="liters"      type="number" min="0" step="0.01" style="width:4em;"/> +/- <input name="literTol"       type="number" min="0" max="50" step="5" value="10" style="width:4em;">%</td>
				<td><input name="compression" type="number" min="0" step="0.1"  style="width:4em;"/> +/- <input name="compressionTol" type="number" min="0" max="50" step="5" value="10" style="width:4em;">%</td>
				<td>
					<select>
						<option value="any" title="Any">-- Any --</option>
						<option value="na" title="AKA: Slow">Naturally Aspirated</option>
						<option value="fi" title="The way God intended">Forced Induction</option>
					</select>
				</td>
				<td><input name="firmware" type="text" placeholder="MS2Extra" maxlength="32" style="width:5em;"/>/<input name="version" type="text" placeholder="" maxlength="32" style="width:5em;"/></td>
				<!-- td><input name="uploadDate" type="date" /></td -->
			</tr>
		</table>
		<div><label for="query">Text: <input name="query" type="search" placeholder="Search for any text..."/></div>
		<div><button id="btnSearch">Search</button></div>
		</fieldset>
	</form>
</div>
