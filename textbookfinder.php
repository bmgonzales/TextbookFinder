<!DOCTYPE html><html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv="X-UA-Compatible" content="IE=Edge" />
		<title>Textbook Finder</title>

</head><body>

<?php
include "text.php";
 ?>
 
<h1 id="header_counts">Textbook Finder</h1>

<br><br><br>

<h3 class="subheader"><strong>Enter ISBN: </strong></h3><br>
<form method='post' action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
<input type="text" name="isbn" size="30" style="border: 2px solid #005481;" required></input>&nbsp;<input type='submit' id ='submit' class='submit' name='submit' value='Submit'></form>


<?php 
if ($_SERVER["REQUEST_METHOD"] == "POST"): 
	print $output;
?>
<script>
        function initMap() {
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 11,
                center: {lat: 29.426396, lng: -98.542232}
            });
  
            var locations = <?php echo json_encode($locations) ?>;
                                   
            var bounds = new google.maps.LatLngBounds(); 

            for (var i = 0; i < locations.length; i++) {
                var coords = locations[i];
                var latLng = new google.maps.LatLng(parseFloat(coords[0]),parseFloat(coords[1]));
                var marker = new google.maps.Marker({
                    position: latLng,
                    map: map
                    });
                bounds.extend(latLng);
                map.fitBounds(bounds);
            }

        }  
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key={key}&callback=initMap"></script>
<?php endif; ?>


<div class="col-md-6" id="map"></div>



	</body></html>
