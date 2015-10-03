<div>
	<form id="filter" action="browse.php" method="get">
		<fieldset>
		<legend>Browse Filter</legend>
		<div>Make: <input name="make" type="text" placeholder="Nissan" maxlength="32" style="width:3em;"/></div>
		<div>Engine Code: <input name="ecode" type="text" placeholder="VG30" maxlength="32" style="width:3em;"/></div>
		<div>Cylinders: <input name="cylinders" type="number" min="0" value="6" max="99" style="width:3em;"/></div>
		<div>Displacement (liters): <input name="liters" type="number" min="0" step="0.01" value="3.0" style="width:4em;"/> +/- <span><input name="literTol="number" min="0" max="50" step="5" value="10">%</span></div>
		<div>Compression (X:1) <input name="compression" type="number" min="0" step="0.1" value="9.0" style="width:4em;"/> +/- <span><input name="compressionTol" type="number" min="0" max="50" step="5" value="10">%</span></div>
		<div>Aspiration: 
			<select>
				<option value="na" title="AKA: Slow">Naturally Aspirated</option>
				<option value="fi" title="The way God intended">Forced Induction</option>
			</select>
		</div>
		<div>Firmware
			<select name="firmware">
				<option value="any" selected>-- Any --</option>
				<?php
					$fwList = $msqur->getFirmwareList();
					foreach ($fwList as $fw)
					{
						echo "<option value=\"$fw\">$fw</option>";
					}
				?>
			</select>
		</div>
		<div>Firmware Version:
			<select name="version">
				<option value="any" selected>-- Any --</option>
				<option value="comms333e2">comms333e2</option>
				
			</select>
		</div>
		<div><button>Apply</button></div>
		</fieldset>
	</form>
</div>
