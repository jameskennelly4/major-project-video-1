<?php
$html = file_get_contents('https://www.basketball-reference.com/teams/CLE/2021.html');

/*
TRUE SHOOTING PERCENTAGE
*/
$pattern = '|<a href="/players/.*.html">(.*)<\/a>|U';
preg_match_all($pattern, $html, $players);

$pattern1 = '|<td class="right " data-stat="pts_per_poss" >(.*)<\/td>|U';
preg_match_all($pattern1, $html, $pts);

$pattern2 = '|<td class="right " data-stat="fga_per_poss" >(.*)<\/td>|U';
preg_match_all($pattern2, $html, $fga);

$pattern3 = '|<td class="right " data-stat="fta_per_poss" >(.*)<\/td>|U';
preg_match_all($pattern3, $html, $fta);

//get the list of players from the Per 100 table
$players_sorted = array();
for ($i=0; $i<count($pts[1]); $i++) {
  $players_sorted[$i] = $players[1][$i+24];
}

//calculate true shooting percentage
$ts = array();
for ($i=0; $i<count($pts[1]); $i++) {
  $ts[$i] = 100*($pts[1][$i]/(2*($fga[1][$i]+0.44*$fta[1][$i])));
}

//unsorted ts array
$ts_unsorted = array();
for ($i=0; $i<count($ts); $i++) {
  $ts_unsorted[$i] = $ts[$i];
}

//unsorted ts player array
$players_unsorted = array();
for ($i=0; $i<count($pts[1]); $i++) {
  $players_unsorted[$i] = $players_sorted[$i];
}

//sort ts array, and then sort the players array in the same order
array_multisort($ts, SORT_DESC, $players_sorted);

/*
USAGE RATE
*/
$pattern4 = '|<td class="right " data-stat="mp_per_g" >(.*)<\/td>|U';
preg_match_all($pattern4, $html, $mp);

$pattern5 = '|<td class="right " data-stat="fga_per_g" >(.*)<\/td>|U';
preg_match_all($pattern5, $html, $fga_pg);

$pattern6 = '|<td class="right " data-stat="fta_per_g" >(.*)<\/td>|U';
preg_match_all($pattern6, $html, $fta_pg);

$pattern7 = '|<td class="right " data-stat="tov_per_g" >(.*)<\/td>|U';
preg_match_all($pattern7, $html, $tov_pg);

$pattern8 = '|<td class="center " data-stat="mp_per_g" >(.*)<\/td>|U';
preg_match_all($pattern8, $html, $mp_tm);

$pattern9 = '|<td class="center " data-stat="fga_per_g" >(.*)<\/td>|U';
preg_match_all($pattern9, $html, $fga_pg_tm);

$pattern10 = '|<td class="center " data-stat="fta_per_g" >(.*)<\/td>|U';
preg_match_all($pattern10, $html, $fta_pg_tm);

$pattern11 = '|<td class="center " data-stat="tov_per_g" >(.*)<\/td>|U';
preg_match_all($pattern11, $html, $tov_pg_tm);

//fixing tov array because one html tag was different than the rest
$tov_pg_fixed = array();
for ($i=0; $i<(count($tov_pg[1])+1); $i++) {
  if ($i == 18) {
    $tov_pg_fixed[$i] = 0.0;
  } else if ($i == 19) {
    $tov_pg_fixed[$i] = $tov_pg[1][$i-1];
  } else {
    $tov_pg_fixed[$i] = $tov_pg[1][$i];
  }
}

//calculate usage rate
$usg = array();
for ($i=0; $i<count($mp[1]); $i++) {
  $usg[$i] = 100 * ((($fga_pg[1][$i] + 0.44 * $fta_pg[1][$i] + $tov_pg_fixed[$i]) * ($mp_tm[1][0] / 5)) /
  ($mp[1][$i] * ($fga_pg_tm[1][0] + 0.44 * $fta_pg_tm[1][0] + $tov_pg_tm[1][0])));
}

//unsorted usg array and also fixed to match ts order of players
$usg_unsorted = array();
for ($i=0; $i<count($usg); $i++) {
  if ($i == 1) {
    $usg_unsorted[$i] = $usg[$i+1];
  } else if ($i == 2) {
    $usg_unsorted[$i] = $usg[$i-1];
  } else if ($i == 3) {
    $usg_unsorted[$i] = $usg[$i+3];
  } else if ($i == 5) {
    $usg_unsorted[$i] = $usg[$i-1];
  } else if ($i == 6) {
    $usg_unsorted[$i] = $usg[$i-1];
  } else if ($i == 7) {
    $usg_unsorted[$i] = $usg[$i+3];
  } else if ($i == 8) {
    $usg_unsorted[$i] = $usg[$i+3];
  } else if ($i == 9) {
    $usg_unsorted[$i] = $usg[$i+3];
  } else if ($i == 10) {
    $usg_unsorted[$i] = $usg[$i+6];
  } else if ($i == 11) {
    $usg_unsorted[$i] = $usg[$i-4];
  } else if ($i == 12) {
    $usg_unsorted[$i] = $usg[$i+3];
  } else if ($i == 13) {
    $usg_unsorted[$i] = $usg[$i-4];
  } else if ($i == 15) {
    $usg_unsorted[$i] = $usg[$i+2];
  } else if ($i == 16) {
    $usg_unsorted[$i] = $usg[$i-3];
  } else if ($i == 17) {
    $usg_unsorted[$i] = $usg[$i-9];
  } else {
    $usg_unsorted[$i] = $usg[$i];
  }
}

//create new players array because the per game table has the players listed in a different order
$players_sorted_usg = array();
for ($i=0; $i<count($mp[1]); $i++) {
  $players_sorted_usg[$i] = $players[1][$i+4];
}

//sort usg array, and then sort the players array in the same order
array_multisort($usg, SORT_DESC, $players_sorted_usg);
?>

<html>
  <head>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">

      // Load Charts packages
      google.charts.load('current', {'packages':['bar']});
      google.charts.load('current', {'packages':['table']});
      google.charts.load('current', {'packages':['corechart']});
      // Draw the table for True Shooting Percentage
      google.charts.setOnLoadCallback(drawTrueShootingTable);
      // Draw the table for True Shooting Percentage
      google.charts.setOnLoadCallback(drawTrueShootingChart);
      // Draw the table for True Shooting Percentage
      google.charts.setOnLoadCallback(drawUsageRateTable);
      // Draw the table for True Shooting Percentage
      google.charts.setOnLoadCallback(drawUsageRateChart);
      // Draw the scatter plot 
      google.charts.setOnLoadCallback(drawScatterPlot);

      function drawTrueShootingTable() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Player');
        data.addColumn('number', 'True Shooting Percentage (%)');
        data.addRows([
          <?php
          for ($i=0; $i<count($pts[1]); $i++) {
            echo "['" . $players_sorted[$i] . "'," . number_format($ts[$i], 2, '.', '') . "],";
          }
          ?>
        ]);

        var options = {
          showRowNumber: true,
          width: '40%',
          height: '100%'
        };

        var table = new google.visualization.Table(document.getElementById('table_ts'));
        table.draw(data, google.charts.Bar.convertOptions(options));
      };

      function drawTrueShootingChart() {
        var data = new google.visualization.arrayToDataTable([
          ['Player', 'True Shooting %'],
          <?php
          for ($i=0; $i<count($pts[1]); $i++) {
            echo "['" . $players_sorted[$i] . "'," . number_format($ts[$i], 2, '.', '') . "],";
          }
          ?>
        ]);

        var options = {
          width: 900,
          bars: 'horizontal',
          axes: {
            x: {
              0: { side: 'top', label: 'Percentage'}
            }
          },
          bar: { groupWidth: "90%" },
          hAxis: {
            viewWindowMode: "explicit",
            viewWindow: {min: 30,max:70}
          }
        };

        var chart = new google.charts.Bar(document.getElementById('graph_ts'));
        chart.draw(data, google.charts.Bar.convertOptions(options));
      };

      function drawUsageRateTable() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Player');
        data.addColumn('number', 'Usage Rate (%)');
        data.addRows([
          <?php
          for ($i=0; $i<count($pts[1]); $i++) {
            echo "['" . $players_sorted_usg[$i] . "'," . number_format($usg[$i], 2, '.', '') . "],";
          }
          ?>
        ]);

        var options = {
          showRowNumber: true,
          width: '40%',
          height: '100%'
        };

        var table = new google.visualization.Table(document.getElementById('table_usg'));
        table.draw(data, google.charts.Bar.convertOptions(options));
      };

      function drawUsageRateChart() {
        var data = new google.visualization.arrayToDataTable([
          ['Player', 'Usage Rate'],
          <?php
          for ($i=0; $i<count($pts[1]); $i++) {
            echo "['" . $players_sorted_usg[$i] . "'," . number_format($usg[$i], 2, '.', '') . "],";
          }
          ?>
        ]);

        var options = {
          width: 900,
          bars: 'horizontal',
          axes: {
            x: {
              0: { side: 'top', label: 'Percentage'}
            }
          },
          bar: {groupWidth: "90%"},
          hAxis: {
            viewWindowMode: "explicit",
            viewWindow: {min: 0,max:35}
          }
        };

        var chart = new google.charts.Bar(document.getElementById('graph_usg'));
        chart.draw(data, google.charts.Bar.convertOptions(options));
      };

      function drawScatterPlot() {
        var data = google.visualization.arrayToDataTable([
          ['True Shooting %', 'Players'],
          <?php
          for ($i=0; $i<count($pts[1]); $i++) {
            echo "[{v:" . number_format($usg_unsorted[$i], 2, '.', '') . ",f:'" . $players_unsorted[$i] . "'}," . number_format($ts_unsorted[$i], 2, '.', '') . "],";
          }
          ?>
        ]);

        var options = {
          vAxis: {title: 'True Shooting Percentage'},
          hAxis: {title: 'Usage Rate', viewWindowMode: "explicit", viewWindow: {min: 10,max:35}},
        };

        var chart = new google.visualization.ScatterChart(document.getElementById('scatterplot'));
        chart.draw(data, google.charts.Bar.convertOptions(options));
      };
    </script>
  </head>
  <body>
    <h1>True Shooting Percentage of Cleveland Cavaliers Players (20-21 Season)</h1>
    <div id="table_ts"></div><br><br>
    <div id="graph_ts" style="width: 900px; height: 500px;"></div><br><br><br><br><br><br><br><br><br><br><br><br><br>
    <h1>Usage Rate of Cleveland Cavaliers Players (20-21 Season)</h1>
    <div id="table_usg"></div><br><br>
    <div id="graph_usg" style="width: 900px; height: 500px;"></div><br><br><br><br><br><br><br><br>
    <h1>Usage Rate vs True Shooting Percentage</h1>
    <div id="scatterplot" style="width: 900px; height: 500px;"></div>
  </body>
</html>
