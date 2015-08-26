# QuakeCheck

## What does it do?

QuakeCheck is a small web application that grabs the user's location via GPS, then checks with the USGS Earthquake Hazards database to see if there have been any recent quakes in the nearby vicinity (500km).

## Motivation

My inspiration for this came after Dallas started feeling earthquakes both near and far away from the oil-drilling sites. I had just left the city, but would see countless Facebook statuses asking, "Did we just have an earthquake???". I figured this would be a good answer.

## What does it use?

A healthy blend of [d3.js](http://d3js.org/), [moment.js](http://momentjs.com/docs/), and [Twitter Bootstrap](http://getbootstrap.com/). [d3.tip](https://github.com/Caged/d3-tip), a small d3.js plugin, was also used.