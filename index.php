<!DOCTYPE html>
<html>
<head>
	<title>QuakeCheck by Richard Braxton</title>
  <meta name="description" content="A small web application to check if there have been any earthquakes near your location.">
  <meta name="keywords" content="earthquakes, USGS, Richard, Braxton">
  <meta name="author" content="Richard Braxton">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="./favicon.ico" />

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
        <div class="map well"></div>	
        <div class="alert alert-warning statusAlert" role="alert">
          <text class="status text-center"><strong>Heads up!</strong> This application will not load without your GPS location. Once it has it, the map will load shortly.</text>
        </div>
        <br/>
        <div class="col-md-5">
          <div class="well">
           <div class="text-center">
             <strong class="updatedText"></strong>
             <table class="dataTable table table-bordered">
               <tr>
                 <td>Time</td>
                 <td>Magnitude</td>
                 <td>Location</td>
                 <td>Depth</td>
               </tr>
             </table>
           </div>
         </div>
       </div>
       <div class="col-md-7">
        <div class="well">
          <p>
            <button class="btn btn-success reloadBtn" click=>Reload</button>
          </p>
          <p>This was a 'speed-run' project to see how quickly I could create a small application to show nearby earthquakes. It was a fun challenge since I hadn't worked with the earthquake database or d3.js in a few years. The website and code was written in ~1hr 45 minutes, and was fine-tuned the next day.</p>
          <p>My inspiration for this came after Dallas started feeling earthquakes both near and far away from the oil-drilling sites. I had just left the city, but would see countless Facebook statuses asking, "Did we just have an earthquake???". I figured this would be a good answer.</p>
          <p>This application primarily uses <a href="http://d3js.org/">d3.js</a>, a Javascript library built for creating and manipulating data visualizations. It also utilizes <a href="https://github.com/caged/d3-tip">d3-tip</a> (a small plugin for displaying details), <a href="http://momentjs.com/">moment.js</a> (for converting Unix times) and <a href="http://getbootstrap.com/">Twitter Bootstrap</a> (for design).</p>
          <p>Major tectonic plates are superimposed on the map in red. Earthquakes are shown as circles, and colored based on their magnitude.</p>
          <p>The app only returns the 10 most recent earthquakes, to prevent high-activity locations (California, Japan, etc) from being too confusing.</p>
        </div>
      </div>
    </div>	<!-- Row -->
    <div class="row">
     <footer>
       <div class="well">
         Created by <a href="http://www.braxton.one">Richard Braxton</a> - <a href="https://github.com/RJBraxton/EarthquakeFelt">Github</a>
       </div>
     </footer>
   </div>
 </div>	<!-- Container -->

 <script type="text/javascript">

   loadMap();
   $('.reloadBtn').click(function() { loadMap();});

   function loadMap() {
    navigator.geolocation.getCurrentPosition(GetLocation);
  };


  function GetLocation(location) {
    //Removing previous info if it was there
    $("svg").remove();
    $('.dataTable').children().remove();
    $('.status').text("Location acquired. Map loading...");

    //Begin the HTTP call.
    $.get( "http://earthquake.usgs.gov/fdsnws/event/1/query?format=geojson&starttime=" + moment().subtract(24, 'hours').format("YYYY-MM-DD") + "&latitude=" + location.coords.latitude + "&longitude=" + location.coords.longitude + "&maxradiuskm=500&minmagnitude=2&limit=10", function( data ) {
      console.log(data);
      $(".updatedText").text("data last updated " + moment(data.metadata.generated).format("HH:MMa"));
      var quakes = [];
      for (var i = 0; i < data.features.length; i++) {
       quakes.push({
        long: data.features[i].geometry.coordinates[0],
        lat: data.features[i].geometry.coordinates[1],
        mag: data.features[i].properties.mag,
        time: moment(data.features[i].properties.time).calendar()
      });
       $('.dataTable').append('<tr><td>' + moment(data.features[i].properties.time).calendar() + '</td><td>' + data.features[i].properties.mag + '</td><td>' + data.features[i].properties.place + '</td><td>' + data.features[i].geometry.coordinates[2] + 'km</td></tr>')
     };

     var colorScale = d3.scale.linear()
     .domain([0,2,3,5,7])
     .range(["green", "blue", "yellow", "orange", "red"]);

     var width = '100%',
     height = 545;

     var projection = d3.geo.mercator()
     .center([location.coords.longitude, location.coords.latitude])
     .scale(3000)
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
     d3.json('tectonics.json', function(error, data) {
      if (error) throw error;

      svg.insert("path", ".graticule")
      .datum(topojson.mesh(data, data.objects.tec))
      .attr("class", "tectonic")
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
     .attr("fill", function(d) {return colorScale(d.mag);}).attr("r", 8).attr("fill-opacity", 0.9)
     .on('mouseover', tip.show)
     .on('mouseout', tip.hide);

     $('.status').text('Map loaded!');
     setTimeout(function() { $('.statusAlert').remove();}, 5000);
     

   });
}




</script>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="http://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/js/bootstrap.min.js"></script>
</body>
</html>