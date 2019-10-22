<?php
include("includes/init.php");

//I used Lab 6 from class as a reference. The lab is designed by Professor Kyle Harms and Sharon Jeong.
// Source: Kyle Harms and Sharon Jeong: Lab (6)

$db = open_sqlite_db("secure/data.sqlite");
$errors =  array();

//User-defined function 1
function insert($db, $query, $params)
{

  $result = exec_sql_query($db, $query, $params);
  if ($result) {
    array_push($errors, "Thank you for adding your recommendation!");
  } else {
    array_push($errors, "Product not added. Check your submission.");
  }
}

//User-defined function 2
function print_vals($sql)
{
  ?>
<tr>
    <td><?php echo htmlspecialchars($sql["rec_name"]); ?></td>
    <td><?php echo htmlspecialchars($sql["rec_category"]); ?></td>
    <td><?php echo htmlspecialchars($sql["rec_desc"]); ?></td>
    <td><?php echo htmlspecialchars($sql["rec_price"]); ?></td>
    <td><?php echo htmlspecialchars($sql["rec_seller"]); ?></td>
    <td><?php echo htmlspecialchars($sql["rec_rating"]); ?></td>
</tr>

<?php

}

const SEARCHES = [
  "rec_name" => "By Name",
  "rec_seller" => "By Seller",
  "rec_category" => "By Category",
  "rec_rating" => "By Rating"
];


if (isset($_GET['search']) && isset($_GET['search_category'])) {

  $search_var = true;
  $search_category =  filter_input(INPUT_GET, 'search_category', FILTER_SANITIZE_STRING);


  if (in_array($search_category, array_keys(SEARCHES))) {
    $search_by = $search_category;
  } else {
    $search_var = false;
    array_push($errors, "Please use a valid category to search!");
  }

  $input_search =  filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
  $input_search =  trim($input_search);
} else {
  $search_var = false;
  $input_search =  null;
  $search_category = null;
}

$recommendations = exec_sql_query($db, "SELECT DISTINCT rec_name FROM recommendations", null)->fetchAll(PDO::FETCH_COLUMN);

if (isset($_POST["submit_insert"])) {

  $submit = true;
  $hid = "name_error2 hidden";
  $hid2 = "seller_error hidden";
  $hid3 = "price_error hidden";

  $name = $_POST['contact_name'];
  $email = $_POST['contact_email'];
  $message = $_POST['contact_message'];

  $add = true;

  $rec_name = filter_input(INPUT_POST, 'rec_name', FILTER_SANITIZE_STRING);
  $rec_category = filter_input(INPUT_POST, 'rec_category', FILTER_SANITIZE_STRING);
  $rec_desc = filter_input(INPUT_POST, 'rec_desc', FILTER_SANITIZE_STRING);
  $rec_price = filter_input(INPUT_POST, 'rec_price', FILTER_VALIDATE_FLOAT);
  $rec_seller = filter_input(INPUT_POST, 'rec_seller', FILTER_SANITIZE_STRING);
  $rec_rating = filter_input(INPUT_POST, 'rec_rating', FILTER_VALIDATE_INT);



  if ($rec_price < 0) {
    $add = false;
  }

  if (trim($rec_name) == '') {
    $add = false;
    $hid = "name_error2";
  }

  if (trim($rec_seller) == '') {
    $add = false;
    $hid2 = "seller_error";
  }

  if (trim($rec_price) == '') {
    $add = false;
    $hid3 = "price_error";
  }


  if ($add) {
    $hid = "name_error2 hidden";
    $hid2 = "seller_error hidden";
    $hid3 = "price_error hidden";

    $query = "INSERT INTO recommendations (rec_name, rec_category, rec_desc, rec_price, rec_seller, rec_rating) VALUES (:rec_name, :rec_category, :rec_desc, :rec_price, :rec_seller, :rec_rating)";

    $params = array(
      ':rec_name' => $rec_name,
      ':rec_category' => $rec_category,
      ':rec_desc' => $rec_desc,
      ':rec_price' => $rec_price,
      ':rec_seller' => $rec_seller,
      ':rec_rating' => $rec_rating

    );

    //User-defined function insert().
    insert($db, $query, $params);
    array_push($errors, "Your review was successfully added!");
  } else {
    array_push($errors, "Review was not added. Check submission.");
  }
} else {
  $hid = "name_error2 hidden";
  $hid2 = "seller_error hidden";
  $hid3 = "price_error hidden";
}



?>
<!DOCTYPE html>
<html>
<?php include("includes/head.php"); ?>

<body>

    <?php include("includes/header.php"); ?>
    <h2 id="recommended"> Recommended by Pug Lovers!</h2>
    <p class="pug-intro">
        Whether you are preparing for your future furriend or looking for the best products, foods, and toys for your puggo, here are some recommendations for pug lovers, by pug lovers! If you have a recommendation to share, be sure to fill out the form below to recommend to others in the community.
    </p>
    <div id="products-body">

        <?php
        foreach ($errors as $er) {
          echo "<p class  = 'messages'>" . htmlspecialchars($er) . "</p>\n";
        }
        ?>

        <form id="search-box" action="products.php" method="get">
            <select name="search_category">
                <option value="" selected disabled>Search By</option>
                <?php
                foreach (SEARCHES as $name => $n) {
                  ?>
                <option value="<?php echo $name; ?>"><?php echo $n; ?></option>
                <?php

              }
              ?>
            </select>
            <input type="text" name="search" />
            <button id="sub-button" type="submit">Search</button>
        </form>

        <?php
        if ($search_var) {
          echo "<p class = 'res'> Search Results: </p>\n";


          $query = "SELECT * FROM recommendations WHERE $search_by LIKE '%'|| :search || '%'";
          $params = array(
            ':search' => $input_search
          );
        } else {
          ?>

        <?php
        echo "<p class = 'res'> All Recommendations: </p>\n";
        $query = "SELECT * FROM recommendations";
        $params = array();
      }

      $result = exec_sql_query($db, $query, $params);
      if ($result) {
        $records = $result->fetchAll();

        if (count($records) > 0) {
          ?>
        <table id="table-products">
            <tr>
                <th> Recommendation </th>
                <th> Category </th>
                <th> Description </th>
                <th> Price </th>
                <th> Seller </th>
                <th> Rating </th>
            </tr>

            <?php
            foreach ($records as $r) {
              print_vals($r);
            }
            ?>
        </table>
        <?php

      } else {
        echo "<p class ='res'> No matches found. Try another search.</p>";
        ?>
        <p class="links">
            <a id="return-button" href="products.php"> All Records </a>
        </p>
        <?php

      }
    }
    ?>
    </div>
    <h3 id="product-heading2"> Got Recommendations?</h3>
    <h2 id="subheading"> Share them with fellow pug lovers! </h2>
    <div id="add-formdiv">
        <form id="add-form" action="products.php" method="post">
            <ul>
                <li>
                    <p class="<?php echo $hid; ?>">Please provide a name for your recommendation.</p>
                    <label class="bold">1. What are you recommending?* </label>
                    <input type="text" name="rec_name" placeholder="Product Name" />
                </li>
                <li>
                    <label class="bold">2. What category does this best fit in?</label>
                <li>
                    <input type="radio" name="rec_category" value="living" checked /> living
                    <input type="radio" name="rec_category" value="food" /> food
                    <input type="radio" name="rec_category" value="parenting" /> parenting
                    <input type="radio" name="rec_category" value="toys" /> toys
                    <input type="radio" name="rec_category" value="care" /> care
                    <input type="radio" name="rec_category" value="medicine" /> medicine
                    <input type="radio" name="rec_category" value="clothes" /> clothes
                </li>

                <li>
                    <label class="bold">3. Description: </label>
                </li>
                <li>
                    <textarea name="rec_desc" placeholder="Describe what makes this recommendation special!" cols="40" rows="5"></textarea>
                </li>

                <li>
                    <p class="<?php echo $hid3; ?>">Please provide a valid, numeric price.</p>
                    <label class="bold">4. Price:* </label>
                    <input type="number" name="rec_price" placeholder="In terms of one unit." />
                </li>

                <li>
                    <p class="<?php echo $hid2; ?>">Please provide a seller.</p>
                    <label class="bold">5. Seller:* </label>
                    <input type="text" name="rec_seller" />
                </li>

                <li>
                    <label class="bold">6. Rating: </label>
                    <input type="radio" name="rec_rating" value="1" /> 1
                    <input type="radio" name="rec_rating" value="2" /> 2
                    <input type="radio" name="rec_rating" value="3" checked /> 3
                    <input type="radio" name="rec_rating" value="4" /> 4
                    <input type="radio" name="rec_rating" value="5" /> 5
                </li>
                <li>
                    <button id="recommend-button" name="submit_insert" type="submit"> Submit Recommendation! </button>
                </li>
            </ul>

        </form>
    </div>

    <?php include("includes/footer.php"); ?>
</body>

</html>
