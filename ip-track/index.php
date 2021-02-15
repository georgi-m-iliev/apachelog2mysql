
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>IP Map</title>

    <link rel="stylesheet" href="jquery-jvectormap-2.0.5.css" type="text/css" media="screen"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">

    <script src="jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.3/js/bootstrap.bundle.min.js"></script>
    <script src="jquery-jvectormap-2.0.5.min.js"></script>
    <script src="jquery-jvectormap-world-mill.js"></script>
</head>

<body>
    <header>
        <h1 class="text-center title">Apache Connections On Map</h1>
    </header>
    <nav class="navbar navbar-light navbar-expand-md">
        <div class="container-fluid"><button data-toggle="collapse" class="navbar-toggler" data-target="#navcol-1"><span class="sr-only">Toggle navigation</span><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navcol-1">
                <ul class="nav navbar-nav">
                    <li class="nav-item"><a class="nav-link active" href="/ip-track">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/ip-track/search.php">Search</a></li>
                    <li class="nav-item"><a class="nav-link" href="/ip-track/ip-search.php">Search for IPs</a></li>
                    <li class="nav-item"><a class="nav-link" href="/ip-track/show_last.php">Show last entries</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <main>
		<div class="cust_container" id="map"></div>
		<?php
			$servername = "localhost";
			$username = "ip_tracker";
			$password = "GyHbfsYZdXw7RSVT";
			$dbname = "ip_tracker";
			// Create connection
			$conn = mysqli_connect($servername, $username, $password, $dbname);

			if (!$conn) {
			  die("Connection failed: " . mysqli_connect_error());
			}
			else {
				//echo "<p>Database connection successfully established<p>";
			}
		?>
		<script>
		$(function(){
		  var map = new jvm.Map({
		    map: 'world_mill',
		    container: $('#map'),
		    scaleColors: ['#C8EEFF', '#0071A4'],
		    normalizeFunction: 'polynomial',
		    hoverOpacity: 0.7,
		    hoverColor: false,
		    markerStyle: {
		      initial: {
		        fill: '#F8E23B',
		        stroke: '#383f47',
		        r: 4
		      },
		      selected: {
			    fill: 'blue'
			  }
		    },
		    backgroundColor: '#94b5c0',
		    markers: [
      			<?php
			      	$query = "SELECT ip, city, latitude, longitude FROM `geolocation`;";
			      	$result = mysqli_query($conn, $query);
			      	$temp = true;
			      	if(mysqli_num_rows($result) > 0) {
			      		while($row = mysqli_fetch_assoc($result)) {
			      			if($temp != true) {
			      				echo ",";
			      			}
			      			echo "{
			      				latLng: [" . $row["latitude"] . ",
			      				" . $row["longitude"] . "],
			      				 name: 'IP: ". $row["ip"] . " City: " . $row["city"] ."'
			      				}";
			      			if($temp = true) {
			      				$temp = false;
			      			}

			      		}
			      	}
				?>
		    ],
		    onMarkerClick: function(e, code) {
		    	var name = (map.markers[code]['config']['name'])
		    	var ip = name.slice(name.indexOf("IP:")+4, name.indexOf("City:")-1)
		    	window.location.replace("/ip-track/index.php?ip=" + ip)
		    }
		  });
		});
		</script>
		<?php
			$ip = $_GET['ip'];
			
			if($ip == NULL) {
				##echo "<p>NO IP</p>";
				mysqli_close($conn);
			}
			else {
				echo '<div class="cust_container">';
				//echo "<p>" . $ip . "</p>";
				$query2 = "SELECT * FROM `logs` WHERE `ip`='" . $ip . "' ORDER BY `date` DESC LIMIT 300;";
				//echo "<p>" . $query2 . "</p>"; 
				$result2 = mysqli_query($conn, $query2);

				if(mysqli_num_rows($result2) > 0) {
					//echo "<p>there is rows</p>";
					echo '<table class="table table_ip_data">
							<thead style="color: var(--light);">
								<tr style="color: var(--light);">
									<th style="color: var(--light);" class="table_ip_data_id">ID</th>
									<th style="color: var(--light);" class="table_ip_data_server_name">Server</th>
									<th style="color: var(--light);" class="table_ip_data_ip">IP</th>
									<th style="color: var(--light);" class="table_ip_data_user">User</th>
									<th style="color: var(--light);" class="table_ip_data_date">Date</th>
									<th style="color: var(--light);" class="table_ip_data_request">Request</th>
									<th style="color: var(--light);" class="table_ip_data_request">Request URL</th>
									<th style="color: var(--light);" class="table_ip_data_request">Request UA</th>
									<th style="color: var(--light);" class="table_ip_data_response">Code</th>
									<th style="color: var(--light);" class="table_ip_data_response">Size</th>
							  	</tr>
							</thead>
							<tbody style="color: var(--light);">';
					while($row2 = mysqli_fetch_assoc($result2)) {
						echo '<tr>
								<td class="table_ip_data_id">' . $row2["id"] . '</td>
								<td class="table_ip_data_server_name">' . $row2["server_name"] . '</td>
								<td class="table_ip_data_ip">' . $row2["ip"] . '</td>
								<td class="table_ip_data_user">' . $row2["user"] . '</td>
								<td class="table_ip_data_date">' . $row2["date"] . '</td>
								<td class="table_ip_data_request">' . $row2["request"] . '</td>
								<td class="table_ip_data_request">' . $row2["request_url"] . '</td>
								<td class="table_ip_data_request">' . $row2["request_user_agent"] . '</td>
								<td class="table_ip_data_response">' . $row2["response"] . '</td>
								<td class="table_ip_data_response">' . $row2["size"] . '</td>
							  </tr>';
					}
					echo '</tbody>
						  </table>';
				}
				else {
					echo '<h4 class="no_data_message">No access data from this IP.</h4>';
				}
				print '</div>';
				mysqli_close($conn);
			}
			
			
		?>
    </main>

    <footer>
    	<span class="copyright">
    		Georgi Iliev 2021 ©
    	</span>
		<span class="designedby">
			Map: <a href="https://jvectormap.com/">jVectorMap</a>
		</span>
    </footer>

</body>

</html>
