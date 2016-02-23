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


//Vijaywada cities Lattitude and Longitude
$cityLat = 16.5083;
$cityLong = 80.6417;
$radius = 6371;
$deg2Rad = (pi()/180);
$intensityPGA = [0.017, 0.014, 0.039, 0.092, 0.18, 0.34, 0.65, 1.24, 1.24];
/*
   Selecting all the earthquakes occurences in the area of 300km radius from Vijaywada
 */

$seleQuery = "Select Lattitude, Longitude, Ms, Mw, mb, Mw, Depth, Year from IndianCatalogue";

$exeQuery = mysqli_query($connection, $seleQuery) or die("error: ".mysqli_error($connection));

echo "<table id=\"t01\">\n<tr>\n\t<th>Moment Magnitude</th>\n\t<th>Distance</th>\n\t<th>PGA using Raghukanth Attenuation Relations</th>\n\t<th>PGA using Dunbar et al</th>\n\t<th>Intensity</th>\n</tr>\n";
while($allEQ = mysqli_fetch_row($exeQuery))
{
	$dist =  $radius * acos(sin($deg2Rad*($cityLat)) * sin($deg2Rad*($allEQ[0])) +  cos($deg2Rad*($cityLat)) * cos($deg2Rad*($allEQ[0])) * cos($deg2Rad*($cityLong-$allEQ[1])));
	if($dist <= 300)
	{
		/*/
		echo $allEQ[0].",\t";//Lattitude
		echo $allEQ[1]."<br />";//Longitude
		echo "Magnitude ".$allEQ[5]."<br />";//Longitude
		echo $dist."<br />";
		echo $allEQ[6]."<br />";//Focal Depth
		if($allEQ[6])
			echo $allEQ[7]."YEAR <br/>";
		/**/
		/*
		   Our First Attenuation relationship - which is given by Raghukanth for Southern Region as Vijaywada lies in southern region only

		   The equation is given by 
		   ln(A) = c1 + c2*(m - 6) + c3*(m - 6)^2 - ln(r) - c4*r + ln(Ebr);

		   The equations constants are c1 = 1.7816, c2 = 0.9205, c3 = -0673, c4 = 0.0035, lnE = 0.3136
		 */
		$c1 = 1.7816; $c2 = 0.9205; $c3 = -0.0673; $c4 = 0.0035; $lnE = 0.3136;
		$m = $allEQ[5];// Taking moment magnitude for calculation
		if($m > 0)
			;
		else
			continue;
		$r = sqrt(pow($dist, 2) + pow($allEQ[6], 2));
		$pga1 = exp($c1 + $c2*($m - 6) + $c3*($m - 6)^2 - log($r) - $c4*$r + $lnE);
		echo "<tr>\n\t<td>".$m."</td>\n\t<td>".$dist."</td>\n\t<td>".$pga1."</td>\n\t";

		/*
		   Second Attentuation relationship - For PI by Dunbar et al.

		   The equation is given by - 
		   log(A) = -1.402 + 0.249*Mw;
		 */
		
		$pga2 = -1.902 + 0.249*$m;
		echo "<td>".$pga2."</td>\n\t";
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
		//$intensity = ($m - 1) / 0.667;
		echo "<td>".$intensity."</td>\n</tr>\n";
	}

}
echo "</table>";

?>

</body>
</html>
