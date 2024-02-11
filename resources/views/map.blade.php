<!DOCTYPE html>
<html>
<body>


<div id="googleMap" style="width:100%;height:1000px"></div>

<script>
    function myMap() {

        var mapProp = {
            center: new google.maps.LatLng(25.774, -80.190),
            zoom: 5,
        };
        var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);


        @foreach($locations as $location)
        @php
            foreach($location->boundaries as $boundary){
                for ($i = 0; $i < count($boundary); $i++) {
                    $boundaries[] = [
                        'lat' =>$boundary[$i]->getLat(),
                        'lng' => $boundary[$i]->getLng()
                    ];
                }
            }
        @endphp

        var triangleCoords = [
                @foreach($boundaries as $boundary)
            {
                lat: {{ $boundary['lat'] }}, lng: {{ $boundary['lng'] }}
            },
            @endforeach
        ];

        //random color
        var color = '#' + (Math.random() * 0xFFFFFF << 0).toString(16);
        var bermudaTriangle = new google.maps.Polygon({
            paths: triangleCoords,
            strokeColor: color,
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: color,
            fillOpacity: 0.35
        });
        bermudaTriangle.setMap(map);
        @endforeach

        google.maps.event.addListener(map, 'click', function (event) {
            var lat = event.latLng.lat();
            var lng = event.latLng.lng();
            alert("lat=" + lat + "&lan=" + lng);
        });


        //if click on polygon show info lat and lng
        google.maps.event.addListener(bermudaTriangle, 'click', function (event) {
            var lat = event.latLng.lat();
            var lng = event.latLng.lng();
            alert("lat=" + lat + "&lan=" + lng);
        });
    }
</script>

<script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCUK3hUZRbvPLf3yuXoUYAKEEeKNcJkxqo&callback=myMap&libraries=geometry"></script>

</body>
</html>
