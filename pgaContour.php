<!DOCTYPE html>
<html>
	<head>
		<title>Attenuation Relations</title>
		<style>
			table {
				width:100%;
			}
			table, th, td {
				border: 1px solid black;
				border-collapse: collapse;
			}
			th, td {
				padding: 5px;
				text-align: left;
			}
			table#t01 tr:nth-child(even) {
				background-color: #eee;
			}
			table#t01 tr:nth-child(odd) {
				background-color:#fff;
			}
			table#t01 th	{
				background-color: #1F1FC7;
				color: white;
			}
		</style>
	</head>

	<body>

	<?php
	require('dbInfo.php');
	function array2Matrix($array) {
		$strMatrix = '[';
		//foreach ($array as $val) {
		//	$strMatrix = $strMatrix.$val.",";
		//}
		//$strMatrix[strlen($strMatrix)-1] = ']';
		$strMatrix = $strMatrix.implode(",", $array);
		$strMatrix[strlen($strMatrix)] = ']';
		return $strMatrix;
	}
	function twoDimArray2Matrix($array) {
		$strMatrix = '[';
		//echo count($array);
		$out = implode(";",array_map(function($a) {return implode(",",$a);},$array));

		$strMatrix = $strMatrix.$out.']';
		//for($i = 0;$i<count($array);$command)
		return $strMatrix;
	}

	$radius = 6371;
	$deg2Rad = (pi()/180);
	$intensityPGA = [0.017, 0.014, 0.039, 0.092, 0.18, 0.34, 0.65, 1.24, 1.24];

	/*
	 * Here our earthquake data is there.. Taking dummy one for now
	 */
	$lat = 16.420524;
	$long = 80.322040;
	$m = 6;
	$depth = 40;


	/*
	 * Making a mesh here of 50x50 for the area of Vijayawada
	 */
	$cityLatStart = 16.466048543507053;
	$cityLatEnd = 16.566770475953764;
	$cityLongStart = 80.5569076538086;
	$cityLongEnd = 80.73440551757812;
	$latDiv = ($cityLatEnd - $cityLatStart) / 50;
	$longDiv = ($cityLongEnd - $cityLongStart) / 50;
	$latMesh = [];
	$longMesh = [];
	$pgaMesh = [[]];

	echo "<table id=\"t01\">\n<tr>\n\t<th>Lattitude</th>\n\t<th>Longitude</th>\n\t<th>Distance</th>\n\t<th>PGA using Raghukanth Attenuation Relations</th>\n\t<th>Intensity</th>\n</tr>\n";
	for($cityLat = $cityLatStart, $ii = 0; $cityLat <= $cityLatEnd ; $cityLat += $latDiv, $ii++)
	{
		$latMesh[] = $cityLat;
		for($cityLong = $cityLongStart, $jj = 0; $cityLong <= $cityLongEnd ; $cityLong += $longDiv, $jj++)
		{
			$dist =  $radius * acos(sin($deg2Rad*($cityLat)) * sin($deg2Rad*($lat)) +  cos($deg2Rad*($cityLat)) * cos($deg2Rad*($lat)) * cos($deg2Rad*($cityLong-$long)));
			/*
			 * Our First Attenuation relationship - which is given by Raghukanth for Southern Region as Vijaywada lies in southern region only
			 *
			 * The equation is given by
			 * ln(A) = c1 + c2*(m - 6) + c3*(m - 6)^2 - ln(r) - c4*r + ln(Ebr);
			 *
			 * The equations constants are c1 = 1.7816, c2 = 0.9205, c3 = -0673, c4 = 0.0035, lnE = 0.3136
			 */
			$c1 = 1.7816; $c2 = 0.9205; $c3 = -0.0673; $c4 = 0.0035; $lnE = 0.3136;
			//$m = $allEQ[5];// Taking moment magnitude for calculation
			$r = sqrt(pow($dist, 2) + pow($depth, 2));
			$pga1 = exp($c1 + ($c2*($m - 6)) + ($c3*pow(($m - 6),2)) - log($r) - ($c4*$r) + $lnE);
			if($ii == 0)
			{
				$longMesh[] = $cityLong;
			}
			$pgaMesh[$ii][$jj] = $pga1;
			for($i = 0;$i < 9;$i++)
			{
				if($pga1 < $intensityPGA[$i])
				{
					$intensity = $i+1;
					break;
				}
				if($i == 1)
					$i++;
			}
			echo "<tr>\n\t<td>".$cityLat."</td>\n\t<td>".$cityLong."</td>\n\t<td>".$r."</td>\n\t<td>".$pga1."</td>\n\t<td>".$intensity."</td>\n</tr>\n";
		}
	}
	//echo var_dump($latMesh)."<br/>";
	//echo var_dump($pgaMesh)."<br/>";
	//echo var_dump(twoDimArray2Matrix($pgaMesh))."<br/>";
	$command = "cd /home/anchal/ContourPlot; bash /home/anchal/MATLAB/R2012a/bin/matlab -r \"ContourPlot(".array2Matrix($latMesh).",".array2Matrix($longMesh).",".twoDimArray2Matrix($pgaMesh).");\" 2>&1";
	//$command = "cat /home/anchal/sample1.txt;";
//	$command = "pwd";
	//echo shell_exec("sudo whoami")."<br/>";
	//$command = "cd /home/anchal/ContourPlot; ls -la; 2>&1";
	echo $command."<br/>";
	//$output = exec($command);
	exec($command,$output, $r);
	//var_dump($output, $r);
	//echo var_dump($output)."<br/>";
	?>

	</body>
</html>
