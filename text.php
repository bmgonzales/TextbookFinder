<?php

//form submit
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$isbn = '';
  $wskey = 'wskey';
	$ollu_isbn = '';
	$nearby_isbn = '';
	$result = array();
	$output = '';

//retrieve ISBN 
	$isbn = $_POST['isbn'];
	$isbn = str_replace('-', '', $isbn);
	if (strlen($isbn) == 9) {
		$isbn = "0" . $isbn;
	}

//get ISBN citation info
	$get_citation = "http://www.worldcat.org/webservices/catalog/content/citations/isbn/{$isbn}?wskey=[$wskey}";
	$citation = file_get_contents($get_citation);
	if ($citation == "info:srw/diagnostic/1/65Record does not exist") {
			$citation = "<p><span style='color:red;weight:bold;'>A record for this ISBN was not found in WorldCat</span></p>";
	} else {
		$citation = "<strong><h3 class='subheader'>Citation:</strong></h3><br>" . $citation . "<hr>";
	}

//search OLLU catalog
	$ollu_search = "http://www.worldcat.org/webservices/catalog/content/libraries/isbn/{$isbn}?oclcsymbol=SAT&wskey={$wskey}";
	$ollu_results = simplexml_load_file($ollu_search);

//search nearby catalogs
	$nearby_search = "http://www.worldcat.org/webservices/catalog/content/libraries/isbn/{$isbn}?location=78207&wskey={$wskey}";
		$nearby_results = simplexml_load_file($nearby_search);

//parse results - @ OLLU
	if (!$ollu_results) {
			$ollu_holdings = "<p>Could not retrieve record from WorldCat</p>";
		} else {
			if ($ollu_results->holding) {
				$href = $ollu_results->holding->electronicAddress->text;
				$ollu_holdings = "<h3 class='subheader'><a href='" . $href . "' target='_blank'><strong><i class='fa fa-book'></i> Available in the OLLU Library</strong></a></h3>";

				} elseif ($ollu_results->diagnostic) {
					$notfound = $ollu_results->diagnostic->message;
						if ($notfound == "Record does not exist") {
							$ollu_holdings = "<p><span style='color:red;'>This record does not exist in Worldcat</span></p>";
						} elseif ($notfound == "Holding not found") {
							$ollu_holdings = "<p><span style='color:red;'>This book is not owned by the OLLU Library</span></p>";
							}
					}
			}

	
//parse results - @ nearby libraries
	if ($nearby_results->holding) {
		foreach($nearby_results->holding as $holding) {
			$result[] = array(
				"Library" => (string) $holding->{'physicalLocation'},
				"Location" => (string) $holding->{'physicalAddress'}->{'text'},
				"URL" => (string) $holding->{'electronicAddress'}->{'text'}
			);
		}

		$lib_num = count($result);
		$nearby_holdings = "<table class='output'>";
		$zips = array();
    $key = "google api key";
				
		foreach($result as $library) {
			$name = $library['Library'];
			$url = $library['URL'];
			$location = $library['Location'];
			preg_match('/^\D+/', $location, $match_loc);
			preg_match('/\d{5}/', $location, $match_zip);
			$location = $match_loc[0];
			$zip = $match_zip[0];
			
			$get_distance = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=78207&destinations={$zip}&key={$key}";
			$distance = file_get_contents($get_distance);
			$distance = json_decode($distance, true);
			$miles = $distance['rows'][0]['elements'][0]['distance']['text'];
		
			
			if ((substr($zip, 0, 2) >= 75) && (substr($zip, 0, 2) <= 79)) {
			$nearby_holdings .= "<tr><td><a href='" . $url . "' target='blank'>" . $name . "</a></td><td>" . $location . "</td><td>" . $miles . "</td></tr>";
			$zips[] = $zip;
			} 
		}
		
		$nearby_holdings .= "</table>";
		
		$locations = array();
		
		foreach($zips as $location) {
			$locations_file = fopen('locations.txt', 'r') or die();
			while(!feof($locations_file)) {
				$line = fgets($locations_file);
				$match = substr($line,0,5);
				if ($location == $match) {
					$locations[] = $line;
				}
			}
			fclose($locations_file);
		}

		$num = count($locations);
		$i = 0;
		for ($i = 0; $i < $num; $i++) {
			$locations[$i] = explode(',', $locations[$i]);
			unset($locations[$i][0]);
			$locations[$i] = array_values($locations[$i]);
			$locations[$i] = array_map('trim', $locations[$i]);
		} 
		



	} else {
		$nearby_holdings = "<p>No copies of this book are available in any nearby locations</p>";
	}


//return results output
ob_start();
print $citation;
print $ollu_holdings . "<br>";
print "<strong><h3 class='subheader'><i class='fa fa-book'></i> Available at " . $lib_num . " nearby libraries:</strong></h3><br>";
print $nearby_holdings;
$output = ob_get_clean();	
}

?>
