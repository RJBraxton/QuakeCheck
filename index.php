<!DOCTYPE html>
<html>
<head>
	<title>Bootstrap 3 Template</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
	<script src="http://momentjs.com/downloads/moment.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/topojson/1.6.19/topojson.min.js"></script>
	<script src="./d3-tip.js"></script>
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

	<!-- Bootstrap core CSS -->
	<link href="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet" media="screen">
	<link href="./main.css" rel="stylesheet" media="screen">

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="http://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv.js"></script>
      <script src="http://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.js"></script>
      <![endif]-->
  </head>
  <body>
  	<div class="container">	
  		<div class="row">
  			<div class="map"></div>	
  			<br/>
  			<div class="col-md-4">
  				<div class="well">
  					<div>
  						<table class="dataTable table table-bordered">
  							<tr>
  								<td>Magnitude</td>
  								<td>Location</td>
  								<td>Depth</td>
  							</tr>
  						</table>
  					</div>
  				</div>
  			</div>
  			<div class="col-md-8">
  				<div class="well">
  					<p>This was a small 'speed-run' project to see how quickly I could create a small application to show nearby earthquakes. It was a fun challenge since I hadn't worked with the earthquake database or d3.js in a few years. The website and code was written in ~1hr 45 minutes, and was fine-tuned the next day.</p>
  					<p>ADD TECTONICS</p>
  				</div>
  			</div>
  		</div>	
  	</div>	

  	<script type="text/javascript">

  		navigator.geolocation.getCurrentPosition(GetLocation);
  		function GetLocation(location) {
  			$.get( "http://earthquake.usgs.gov/fdsnws/event/1/query?format=geojson&starttime=" + moment().subtract(1, 'days').format("YYYY-MM-DD") + "&latitude=" + location.coords.latitude + "&longitude=" + location.coords.longitude + "&maxradiuskm=500&minmagnitude=2", function( data ) {
  				console.log(location.coords.accuracy);
  				console.log(location.coords.latitude);
  				console.log(location.coords.longitude);
  				console.log(data);
  				var quakes = [];
  				for (var i = 0; i < data.features.length; i++) {
  					quakes.push({
  						long: data.features[i].geometry.coordinates[0],
  						lat: data.features[i].geometry.coordinates[1],
  						mag: data.features[i].properties.mag,
  						time: moment(data.features[i].properties.time).format("HH:MM")
  					});
  					$('.dataTable').append('<tr><td>' + data.features[i].properties.mag + '</td><td>' + data.features[i].properties.place + '</td><td>' + data.features[i].geometry.coordinates[2] + 'km</td></tr>')
  				};

  				var width = '100%',
  				height = 545;

  				var projection = d3.geo.mercator()
  				.center([location.coords.longitude, location.coords.latitude])
  				.scale(1300)
  				.precision(.1);

  				var path = d3.geo.path()
  				.projection(projection);

  				var graticule = d3.geo.graticule();

  				var svg = d3.select(".map").append("svg")
  				.attr("width", width)
  				.attr("height", height);

  				svg.append("path")
  				.datum(graticule)
  				.attr("class", "graticule")
  				.attr("d", path);

  				d3.json("./world-50m.json", function(error, world) {
  					if (error) throw error;

  					svg.insert("path", ".graticule")
  					.datum(topojson.feature(world, world.objects.land))
  					.attr("class", "land")
  					.attr("d", path);

  					svg.insert("path", ".graticule")
  					.datum(topojson.mesh(world, world.objects.countries, function(a, b) { return a !== b; }))
  					.attr("class", "boundary")
  					.attr("d", path);
  				});
  				d3.json("./us.json", function(error, us) {
  					if (error) throw error;

  					svg.append("path")
  					.datum(topojson.mesh(us, us.objects.states, function(a, b) { return a !== b; }))
  					.attr("class", "boundary")
  					.attr("d", path);
  				});


  				d3.select(self.frameElement).style("height", height + "px");

  				/* Initialize tooltip */
  				var tip = d3.tip().attr('class', 'd3-tip').html(function(d) { return "M" + d.mag + ", " + d.time; });

  				/* Invoke the tip in the context of your visualization */
  				svg.call(tip)

  				svg.selectAll(".mark")
  				.data(quakes)
  				.enter()
  				.append("svg:circle")
  				.attr("cx", function(d) { return projection([d.long,d.lat])[0];})
  				.attr("cy", function(d) {return projection([d.long,d.lat])[1];})
  				.attr("fill", "red").attr("r", 8)
  				.on('mouseover', tip.show)
  				.on('mouseout', tip.hide)

  			});
}




</script>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/js/bootstrap.min.js"></script>
</body>
</html>