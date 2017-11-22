<?php
require_once 'create_matrix.php';
?>


<html>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <!-- <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/core.js"></script> -->
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
    
  </head>
  <body>
    <div id="sankey_basic" style="width: 900px; height: 300px; margin-top: 20px;" align="center" ></div>
    <!-- <div id="json_data" style="display: none;"><?php echo $json; ?></div> -->


        <script type="text/javascript">
        
          $(function(){
              google.charts.load('current', {'packages':['sankey']});
          google.charts.setOnLoadCallback(drawChart);

          function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'From');
            data.addColumn('string', 'To');
            data.addColumn('number', 'Weight');
            // data.addRows([
            //   [ 'A', 'X', 5 ],
            //   [ 'A', 'Y', 7 ],
            //   [ 'A', 'Z', 6 ],
            //   [ 'B', 'X', 2 ],
            //   [ 'B', 'Y', 9 ],
            //   [ 'B', 'Z', 4 ]
            // ]);

            var row = <?php echo json_encode($json); ?>

            data.addRows(row);

            var colors = ['#a6cee3', '#b2df8a', '#fb9a99', '#fdbf6f',
                          '#cab2d6', '#ffff99', '#1f78b4', '#33a02c'];
            // Sets chart options.
            var options = {
              width: 1000,
              height:800,
              sankey:{
                node:{
                  colors:colors
                },
                link:{
                  colorMode:'gradient',
                  colors:{stroke:'black',strokeWidth:2}
                }
              }
            };



            // Instantiates and draws our chart, passing in some options.
            var chart = new google.visualization.Sankey(document.getElementById('sankey_basic'));
            chart.draw(data, options);
          }
          });

          
         
        </script>
  </body>
</html>