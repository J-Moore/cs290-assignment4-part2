<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

// PASSWORD HERE
include 'local_settings.php';

$tabledata = array();
$resultline = array();

$hostname = "oniddb.cws.oregonstate.edu";
$dbname = "moorjona-db";
$username = "moorjona-db";
$mysqli = new mysqli($hostname, $dbname, $dbpw, $username);

// CHECK FOR DELETE ACTION
if (isset($_POST['delete'])) {

    $delstmt = $mysqli->prepare("DELETE FROM cs290sp2015_rentals WHERE id=?");
    $delstmt->bind_param("i", $_POST['delete']);
    $delstmt->execute();
    $delstmt->close();
}

if (isset($_POST['update-id'])) {

    $updstmt = $mysqli->prepare("UPDATE cs290sp2015_rentals SET rented=? WHERE id=?");
    $updstmt->bind_param("ii", $_POST['update-value'], $_POST['update-id']);
    $updstmt->execute();
    $updstmt->close();
}

if (isset($_POST['movie-name'])) {

    $addstmt = $mysqli->prepare("INSERT INTO cs290sp2015_rentals (name, category, length, rented)
        VALUES (?, ?, ?, ?)");
    $addstmt->bind_param("ssii", $_POST['movie-name'], $_POST['movie-category'], $_POST['movie-length'], $_POST['movie-rented']);
    $addstmt->execute();
    $addstmt->close();
}

if (isset($_POST['killalldata']) && $_POST['killalldata'] == "42") {

    $killstmt = $mysqli->query("TRUNCATE TABLE cs290sp2015_rentals");
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset='utf-8'>
    <title>Assignment4 PHP 2</title>
    <script></script>
  </head>
  <body>
  
    <!-- ADD NEW MOVIE FORM -->
    <h1>Add a New Movie</h1>
    <form action="#" method="post">
      <table>
        <tr>
          <td>Movie Name:
          <td><input type="text" name="movie-name" required />
        </tr>
        <tr>
          <td>Category:
          <td><input type="text" name="movie-category" />
        </tr>
        <tr>
          <td>Length in minutes: 
          <td><input type="number" name="movie-length" />
        </tr>
        <tr>
          <td>
          <td><select name="movie-rented">
            <option value=0>Available</option>
            <option value=1>Checked Out</option>
          </select>
        </tr>
        <tr>
          <td>
          <td><input type="submit" value="Add Movie" />
        </tr>
      </table>
    </form>
    
    
    <br>
    
    <!--FILTER MOVIES-->
    <h1>Filter Movies By Category</h1>
    <form action="#" method="post">
      <select name="movie-filter">
        <option>All Movies</option>
<?php
$filtstmt = $mysqli->prepare("SELECT DISTINCT(category) FROM cs290sp2015_rentals");

$filtstmt->execute();
$filtstmt->bind_result($cat_name);
$filtstmt->store_result();
if ($filtstmt->num_rows > 0) {
// DISPLAY CATEGORIES AS SELECT OPTIONS
    while ($filtstmt->fetch()) {
        echo "<option>" . $cat_name . "</option>\n";
    }
}
$filtstmt->close();

?>
      </select>
      <input type="submit" value="Filter Movies" />
    </form>
    
    <br>
    
    <!-- TABLE THAT SHOWS MOVIES -->
    <h1>List of Movies</h1>
    <table>
      <tbody>
        <tr>
          <th>Name</th>
          <th>Category</th>
          <th>Length (min)</th>
          <th>Status</th>
        </tr>
<?php
// GRAB TABLE DATA FOR DISPLAYING

if (!$mysqli || $mysqli->connect_errno) {
    echo "Connection error " . $mysqli->connect_errno . " " . $mysqli->connect_error;
}

// IF NO FILTER IS SET DISPLAY EVERYTHING
if (!isset($_POST['movie-filter']) || $_POST['movie-filter'] == "All Movies") {
    $stmt = $mysqli->prepare("SELECT id, name, category, length, rented FROM cs290sp2015_rentals");

    if (!$stmt) {
        echo "Error Selecting Data from Rentals Table - Prepare Failed: " . $mysqli->connect_errno . " " . $mysqli->connect_error;
    }
} else {

// OTHERWISE BIND PREPARE STATEMENT BASED ON CATEGORY FILTER
    $stmt = $mysqli->prepare("SELECT id, name, category, length, rented
        FROM cs290sp2015_rentals
        WHERE category=?");
    if (!$stmt) {
        echo "Error Selecting Data from Rentals Table - Prepare Failed: " . $mysqli->connect_errno . " " . $mysqli->connect_error;
    }
    
    if (!$stmt->bind_param("s", $_POST['movie-filter'])) {
        echo "Error binding category filter on select statement - Bind Failed: " . $mysqli->connect_errno . " " . $mysqli->connect_error;
    }
}
    
if (!$stmt->execute()) {
    echo "Error executing Select on Rentals Table - Execute Failed: " . $mysqli->connect_errno . " " . $mysqli->connect_error;
}

if (!$stmt->bind_result($mov_id, $mov_name, $mov_category, $mov_length, $mov_rented)) {
    echo "Error executing Select on Rentals Table - Execute Failed: " . $mysqli->connect_errno . " " . $mysqli->connect_error;
}

$stmt->store_result();
if ($stmt->num_rows > 0) {
// DISPLAY TABLE INFO
    while ($stmt->fetch()) {
        echo "<tr>\n<td>" . $mov_name
           . "</td>\n<td>" . $mov_category
           . "</td>\n<td>" . $mov_length
           . "</td>\n";
                     
        if ($mov_rented == 0) {
            echo "<td>Available</td>\n";
        } else {
            echo "<td>Checked Out</td>\n";
        }

        // delete button
        echo '<td><form name="Delete This" action="#" method="post">';
        echo '<input type="hidden" name="delete" value=' . $mov_id . '>';
        echo '<input type="submit" value="Delete">';
        echo '</form>';

        // change status button
        echo '<td><form name="Change Status" action="#" method="post">';
        echo '<input type="hidden" name="update-id" value=' . $mov_id . '>';
        echo '<input type="hidden" name="update-value" value=' . !$mov_rented . '>';
        echo '<input type="submit" value="Check In/Out">';
        echo '</form>';
    }
}
            
$stmt->close();

?>
      </tbody>
    </table>
    
    <br>
    
    <!--ERASE ALL MOVIES IN DATABASE-->
    <h1>ERASE TABLE DATA</h1>
    <form name="Delete All" action="#" method="post">
      <input type="hidden" name="killalldata" value="42" />
      <input type="submit" value="DELETE ALL VIDEOS" />
    </form>

  </body>
</html>