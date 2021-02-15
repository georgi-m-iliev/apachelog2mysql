
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
                    <li class="nav-item"><a class="nav-link" href="/ip-track">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/ip-track/search.php">Search</a></li>
                    <li class="nav-item"><a class="nav-link activ" href="/ip-track/ip-search.php">Search for IPs</a></li>
                    <li class="nav-item"><a class="nav-link" href="/ip-track/show_last.php">Show last entries</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <main>
		<div class="cust_container">
			<div class ="form-check-inline">
			<form action ="" class="form-inline">
				<label for="search_box" class="m-1">Enter city: </label>
				<input type="text" name="search_query" id="search_box" class="form-control m-1">
				<input type="submit" value="Submit" class="btn btn-primary m-1">
			</form>

			</div>
		</div>

		<?php
			$servername = "localhost";
			$username = "ip_tracker";
			$password = "GyHbfsYZdXw7RSVT";
			$dbname = "ip_tracker";
			// Create connection

			$search_query = $_GET['search_query'];

			if($search_query != NULL) {
				$conn = mysqli_connect($servername, $username, $password, $dbname);

				if (!$conn) {
				  die("Connection failed: " . mysqli_connect_error());
				}
				else {
					//echo "<p>Database connection successfully established<p>";
				}
				echo '<div class="cust_container">';
				//echo "<p>" . $ip . "</p>";
				$query = "SELECT * FROM `geolocation` WHERE `city`='" . $search_query . "';";

				//echo "<p>" . $query2 . "</p>"; 
				$result = mysqli_query($conn, $query);

				if(mysqli_num_rows($result) > 0) {
					//echo "<p>there is rows</p>";
					echo '<table class="table table_ip_data">
							<thead style="color: var(--light);">
								<tr style="color: var(--light);">
									<th style="color: var(--light);" class="table_ip_data_id">ID</th>
									<th style="color: var(--light);" class="table_ip_data_ip">IP</th>
									<th style="color: var(--light);" class="table_ip_data_date">Country</th>
									<th style="color: var(--light);" class="table_ip_data_date">City</th>
									<th style="color: var(--light);" class="table_ip_data_request">Latitude</th>
									<th style="color: var(--light);" class="table_ip_data_request">Longitude</th>
							  	</tr>
							</thead>
							<tbody style="color: var(--light);">';
					while($row = mysqli_fetch_assoc($result)) {
						echo '<tr>
								<td class="table_ip_data_id">' . $row["id"] . '</td>
								<td class="table_ip_data_ip">' . $row["ip"] . '</td>
								<td class="table_ip_data_user">' . $row["country_name"] . '</td>
								<td class="table_ip_data_date">' . $row["city"] . '</td>
								<td class="table_ip_data_date">' . $row["latitude"] . '</td>
								<td class="table_ip_data_date">' . $row["longitude"] . '</td>
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
