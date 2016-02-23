<!DOCTYPE html>
<html>
<head>
<title>Attenuation Relations</title>
</head>

<body>

<?php 
require('dbInfo.php');


//Vijaywada cities Lattitude and Longitude
$cityLat = 16.5083;
$cityLong = 80.6417;
$radius = 6371;
$deg2Rad = (pi()/180);

/*
   Selecting all the earthquakes occurences in the area of 300km radius from Vijaywada
 */

$seleQuery = "Select Lattitude, Longitude, Ms, Mw, mb, Mw, Depth, Year from IndianCatalogue";

$exeQuery = mysqli_query($connection, $seleQuery) or die("error: ".mysqli_error($connection));

echo "<table>";
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
		$r = sqrt(pow($dist, 2) + pow($allEQ[6], 2));
		$pga1 = exp($c1 + $c2*($m - 6) + $c3*($m - 6)^2 - log($r) - $c4*$r + $lnE);
		echo "<tr><td>".$pga1."<td/>";

		/*
		   Second Attentuation relationship - For PI by Dunbar et al.

		   The equation is given by - 
		   log(A) = -1.402 + 0.249*Mw;
		 */
		$pga2 = -1.402 + 0.249*$m;
		echo "<td>".$pga2."</td></tr>";
	}

}
echo "</table>";

?>

</body>
</html>
