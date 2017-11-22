<?php
include_once 'sam_matrix.php';
// echo "<pre>";
// print_r($Names);
// print_r($newoutput);exit;


?>


<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
	<title>Stretched Chord to show Flows</title>

	<!-- D3.js -->	
    <script src="http://d3js.org/d3.v3.js"></script>
	<script src="d3.stretched.chord.js"></script>
	<script src="d3.layout.chord.sort.js"></script>
	
	<!-- jQuery -->
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	
	<!-- Open Sans & CSS -->
	<link href='http://fonts.googleapis.com/css?family=Open+Sans:700,400,300' rel='stylesheet' type='text/css'>
	  <style>
		body {
		  font-family: 'Open Sans', sans-serif;
		  font-size: 12px;
		  font-weight: 400;
		  color: #525252;
		  text-align: center;
		}	
		
		line {
		  stroke: #000;
		  stroke-width: 1.px;
		}

		text {
		  font-size: 8px;
		}

		.titles{
		  font-size: 10px;
		}

		path.chord {
		  fill-opacity: .80;
		}
	  </style>
  </head>
  <body>

    <div id="chart"></div>	
    

<script >
    	
	$(function(){
		////////////////////////////////////////////////////////////
		//////////////////////// Set-up ////////////////////////////
		////////////////////////////////////////////////////////////
		var screenWidth = $(window).width(),
			mobileScreen = (screenWidth > 400 ? false : true);

		var margin = {left: 50, top: 10, right: 50, bottom: 10},
			width = Math.min(screenWidth, 800) - margin.left - margin.right,
			height = (mobileScreen ? 300 : Math.min(screenWidth, 800)*5/6) - margin.top - margin.bottom;
					
		var svg = d3.select("#chart").append("svg")
					.attr("width", (width + margin.left + margin.right))
					.attr("height", (height + margin.top + margin.bottom));
					
		var wrapper = svg.append("g").attr("class", "chordWrapper")
					.attr("transform", "translate(" + (width / 2 + margin.left) + "," + (height / 2 + margin.top) + ")");;
					
		var outerRadius = Math.min(width, height) / 2  - (mobileScreen ? 80 : 100),
			innerRadius = outerRadius * 0.95,
			pullOutSize = (mobileScreen? 20 : 50),
			opacityDefault = 0.7, //default opacity of chords
			opacityLow = 0.02; //hover opacity of those chords not hovered over
			
		////////////////////////////////////////////////////////////
		////////////////////////// Data ////////////////////////////
		////////////////////////////////////////////////////////////


		// var Names = ["Administrative Staff","Crafts","Business Management","Basic Occupations","Health",
		// 			"IT","Juridical & Cultural","Management functions","Teachers",
		// 			"Salesmen & Service providers","Caretakers","Science & Engineering", "Other", "",
		// 			"Engineering","Education","Agriculture","Art, Language & Culture","Health","Behavior & Social Sciences","Economy",""];

		var Names= <?php echo json_encode($Names); ?>

		console.log(Names);

		// var respondents = 17533, //Total number of respondents (i.e. the number that make up the total group
		var respondents = <?php echo $respondents; ?>,
			emptyPerc = 0.3, //What % of the circle should become empty
			emptyStroke = Math.round(respondents*emptyPerc);


		var matrix =<?php echo json_encode($newoutput); ?>

		console.log(matrix);
		/*var matrix = [
			[0,0,0,0,0,0,0,0,0,0,0,0,0,0,232,65,44,57,39,123,1373,0], //Administratief personeel
			[0,0,0,0,0,0,0,0,0,0,0,0,0,0,32,0,0,11,0,0,24,0], //Ambachtslieden
			[0,0,0,0,0,0,0,0,0,0,0,0,0,0,173,43,52,55,36,125,2413,0], //Bedrijfsbeheer (vak)specialisten
			[0,0,0,0,0,0,0,0,0,0,0,0,0,0,32,16,13,23,10,37,54,0], //Elementaire beroepen
			[0,0,0,0,0,0,0,0,0,0,0,0,0,0,161,24,17,0,2089,85,60,0], //Gezondheidszorg (vak)specialisten
			[0,0,0,0,0,0,0,0,0,0,0,0,0,0,510,0,0,57,0,0,251,0], //IT (vak)specialisten
			[0,0,0,0,0,0,0,0,0,0,0,0,0,0,16,118,10,454,99,1537,271,0], //Juridisch en culturele (vak)specialisten
			[0,0,0,0,0,0,0,0,0,0,0,0,0,0,76,21,10,15,125,41,261,0], //Leidinggevende functies
			[0,0,0,0,0,0,0,0,0,0,0,0,0,0,32,2206,37,292,32,116,76,0], //Onderwijsgevenden
			[0,0,0,0,0,0,0,0,0,0,0,0,0,0,96,74,43,116,51,135,752,0], //Verkopers en verleners persoonlijke diensten
			[0,0,0,0,0,0,0,0,0,0,0,0,0,0,15,34,0,22,27,156,36,0], //Verzorgend personeel
			[0,0,0,0,0,0,0,0,0,0,0,0,0,0,1141,0,111,291,0,0,48,0], //Wetenschap en techniek (vak)specialisten
			[0,0,0,0,0,0,0,0,0,0,0,0,0,0,36,0,39,0,0,20,109,0], //Other
			[0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,emptyStroke], //dummyBottom
			[232,32,173,32,161,510,16,76,32,96,15,1141,36,0,0,0,0,0,0,0,0,0], //Techniek
			[65,0,43,16,24,0,118,21,2206,74,34,0,0,0,0,0,0,0,0,0,0,0], //Onderwijs
			[44,0,52,13,17,0,10,10,37,43,0,111,39,0,0,0,0,0,0,0,0,0], //Landbouw
			[57,11,55,23,0,57,454,15,292,116,22,291,0,0,0,0,0,0,0,0,0,0], //Kunst, Taal en Cultuur
			[39,0,36,10,2089,0,99,125,32,51,27,0,0,0,0,0,0,0,0,0,0,0], //Gezondheidszorg
			[123,0,125,37,85,0,1537,41,116,135,156,0,20,0,0,0,0,0,0,0,0,0], //Gedrag & Maatschappij
			[1373,24,2413,54,60,251,271,261,76,752,36,48,109,0,0,0,0,0,0,0,0,0], //Economie
			[0,0,0,0,0,0,0,0,0,0,0,0,0,emptyStroke,0,0,0,0,0,0,0,0] //dummyTop
		];*/

		/*var Names = ["women empowerment","Diseases","Gender Ratio","","Abhijit18691164","BeTrue4Changes","hashshimla",""];

		var respondents = 95, //Total number of respondents (i.e. the number that makes up the total group)
			emptyPerc = 0.4, //What % of the circle should become empty
			emptyStroke = Math.round(respondents*emptyPerc); 
		var matrix = [
			[0,0,0,0,10,5,15,0], //X
			[0,0,0,0,5,15,20,0], //Y
			[0,0,0,0,15,5,5,0], //Z
			[0,0,0,0,0,0,0,emptyStroke], //Dummy stroke
			[10,5,15,0,0,0,0,0], //C
			[5,15,5,0,0,0,0,0], //B
			[15,20,5,0,0,0,0,0], //A
			[0,0,0,emptyStroke,0,0,0,0] //Dummy stroke
		];*/
		//Calculate how far the Chord Diagram needs to be rotated clockwise to make the dummy
		//invisible chord center vertically
		var offset = (2 * Math.PI) * (emptyStroke/(respondents + emptyStroke))/4;

		//Custom sort function of the chords to keep them in the original order
		function customSort(a,b) {
			return 1;
		};

		//Custom sort function of the chords to keep them in the original order
		var chord = customChordLayout() //d3.layout.chord()//Custom sort function of the chords to keep them in the original order
			.padding(.02)
			.sortChords(d3.descending) //which chord should be shown on top when chords cross. Now the biggest chord is at the bottom
			.matrix(matrix);
			
		var arc = d3.svg.arc()
			.innerRadius(innerRadius)
			.outerRadius(outerRadius)
			.startAngle(startAngle) //startAngle and endAngle now include the offset in degrees
			.endAngle(endAngle);

		var path = stretchedChord()
			.radius(innerRadius)
			.startAngle(startAngle)
			.endAngle(endAngle)
			.pullOutSize(pullOutSize);

		////////////////////////////////////////////////////////////
		//////////////////// Draw outer Arcs ///////////////////////
		////////////////////////////////////////////////////////////

		var g = wrapper.selectAll("g.group")
			.data(chord.groups)
			.enter().append("g")
			.attr("class", "group")
			.on("mouseover", fade(opacityLow))
			.on("mouseout", fade(opacityDefault));

		g.append("path")
			.style("stroke", function(d,i) { return (Names[i] === "" ? "none" : "#00A1DE"); })
			.style("fill", function(d,i) { return (Names[i] === "" ? "none" : "#00A1DE"); })
			.style("pointer-events", function(d,i) { return (Names[i] === "" ? "none" : "auto"); })
			.attr("d", arc)
			.attr("transform", function(d, i) { //Pull the two slices apart
						d.pullOutSize = pullOutSize * ( d.startAngle + 0.001 > Math.PI ? -1 : 1);
						return "translate(" + d.pullOutSize + ',' + 0 + ")";
			});


		////////////////////////////////////////////////////////////
		////////////////////// Append Names ////////////////////////
		////////////////////////////////////////////////////////////

		//The text also needs to be displaced in the horizontal directions
		//And also rotated with the offset in the clockwise direction
		g.append("text")
			.each(function(d) { d.angle = ((d.startAngle + d.endAngle) / 2) + offset;})
			.attr("dy", ".35em")
			.attr("class", "titles")
			.attr("text-anchor", function(d) { return d.angle > Math.PI ? "end" : null; })
			.attr("transform", function(d,i) { 
				var c = arc.centroid(d);
				return "translate(" + (c[0] + d.pullOutSize) + "," + c[1] + ")"
				+ "rotate(" + (d.angle * 180 / Math.PI - 90) + ")"
				+ "translate(" + 55 + ",0)"
				+ (d.angle > Math.PI ? "rotate(180)" : "")
			})
		  .text(function(d,i) { return Names[i]; });

		////////////////////////////////////////////////////////////
		//////////////////// Draw inner chords /////////////////////
		////////////////////////////////////////////////////////////
		 
		var chords = wrapper.selectAll("path.chord")
			.data(chord.chords)
			.enter().append("path")
			.attr("class", "chord")
			.style("stroke", "none")
			.style("fill", "#C4C4C4")
			.style("opacity", function(d) { return (Names[d.source.index] === "" ? 0 : opacityDefault); }) //Make the dummy strokes have a zero opacity (invisible)
			.style("pointer-events", function(d,i) { return (Names[d.source.index] === "" ? "none" : "auto"); }) //Remove pointer events from dummy strokes
			.attr("d", path);	

		////////////////////////////////////////////////////////////
		///////////////////////// Tooltip //////////////////////////
		////////////////////////////////////////////////////////////

		//Arcs
		// g.append("title")	
		// 	.text(function(d, i) {return Math.round(d.value) + " people in " + Names[i];});
			
		// //Chords
		// chords.append("title")
		// 	.text(function(d) {
		// 		return [Math.round(d.source.value), " people from ", Names[d.target.index], " to ", Names[d.source.index]].join(""); 
		// 	});
			
		////////////////////////////////////////////////////////////
		////////////////// Extra Functions /////////////////////////
		////////////////////////////////////////////////////////////

		//Include the offset in de start and end angle to rotate the Chord diagram clockwise
		function startAngle(d) { return d.startAngle + offset; }
		function endAngle(d) { return d.endAngle + offset; }

		// Returns an event handler for fading a given chord group
		function fade(opacity) {
		  return function(d, i) {
			svg.selectAll("path.chord")
				.filter(function(d) { return d.source.index !== i && d.target.index !== i && Names[d.source.index] !== ""; })
				.transition("fadeOnArc")
				.style("opacity", opacity);
		  };
		}//fade
	})
    	
    </script>
	
  </body>
</html>