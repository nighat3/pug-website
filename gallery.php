<?php
include("includes/init.php");

$messages = array();
const MAX_FILE_SIZE = 1000000;
$db = open_or_init_sqlite_db("secure/gallery.sqlite", "secure/init.sql");

//(Kyle Harms) I used Lab 8 from Info 2300 as a reference. This Lab was created by Professor Kyle Harms.

if (is_user_logged_in() && isset($_POST["submit_upload"])) {

  //"up_image" corresponds to the image file the user uploads.
  $uploaded_info = $_FILES["up_image"];
  $uploaded_tag = filter_input(INPUT_POST, 'tag_name', FILTER_SANITIZE_STRING);
  $uploaded_desc = filter_input(INPUT_POST, 'desc', FILTER_SANITIZE_STRING);
  $uploaded_cite = filter_input(INPUT_POST, 'citation', FILTER_SANITIZE_STRING);

  if ($uploaded_info['error'] == UPLOAD_ERR_OK) {

    $up_name = basename($uploaded_info["name"]);
    $up_ext = strtolower(pathinfo($up_name, PATHINFO_EXTENSION));

    $query = "INSERT INTO images(user_id, file_name, file_ext, desc, citation) VALUES (:user_id, :file_name, :file_ext, :desc, :citation);";
    $params = array(':user_id' => $current_user['id'], ':file_name' => $up_name, ':file_ext' => $up_ext, ':desc' => $uploaded_desc, ':citation' => $uploaded_cite);

    $query2 = "INSERT INTO tags(name) VALUES (:tag_name);";
    $params2 = array(':tag_name' => $uploaded_tag);


    $res1 = exec_sql_query($db, $query, $params);
    $img_id = $db->lastInsertId("id");

    $res2 = exec_sql_query($db, $query2, $params2);
    $tag_id = $db->lastInsertId("id");
    if ($res1 and $res2) {
      $query3 = "INSERT INTO image_tags(image_id, tag_id) VALUES(:image_id, :tag_id)";
      $params3 = array(':image_id' => $img_id, ':tag_id' => $tag_id);
      //Add to the image_tags table.
      $res3 = exec_sql_query($db, $query3, $params3);

      //Move uploaded file into correct folder after all tables are updated.
      if ($res3) {

        $rec_id = $db->lastInsertId("id");
        $store_img = 'uploads/images/' . $img_id . '.' . $up_ext;
        if (move_uploaded_file($uploaded_info["tmp_name"], $store_img)) { } else {
          array_push($messages, "Failed to upload image.");
        }
      } else {
        array_push($messages, "Failed to upload image.");
      }
    } else {
      array_push($messages, "Failed to upload image.");
    }
  }
}



//TAGS ADDING
//
$all_tags = exec_sql_query($db, "SELECT DISTINCT name FROM tags", NULL)->fetchAll(PDO::FETCH_COLUMN);

if (isset($_POST["tag_submitted"])){

  $success = TRUE;
  $added_tag =  filter_input(INPUT_POST, 'added_tag', FILTER_SANITIZE_STRING);
  //$existing_tag = $_POST["existing_tag"];

  if(trim($added_tag == '')){
    array_push($messages, "Tag cannot be blank. Try again.");
    $success = FALSE;
  }

  $qu = "SELECT name FROM tags INNER JOIN image_tags on (image_tags.tag_id = tags.id) WHERE image_tags.image_id = :id;";

  $params = (array(":id" => $_GET["image_clicked"]));

  $check_imgtags = exec_sql_query($db, $qu, $params)->fetchAll();

  if($success){
    $new = TRUE; //Maintains whether or not this input is a unique tag.
    foreach($all_tags as $tag){
      if($tag == strtolower($added_tag)){
        $new = FALSE; //We already have this tag in our database.
      }

    }
    foreach($check_imgtags as $t){
      if($t['name'] == strtolower($added_tag)){

        $exists = TRUE;

      }
    }

    if ($new){
      $query = "INSERT INTO tags(name) VALUES (:name);";
      $params  = array(':name' => $added_tag);
      $adding_result = exec_sql_query($db, $query, $params);
    }

    $tag_ids = exec_sql_query($db, "SELECT id FROM tags WHERE name = '$added_tag';", NULL)->fetchAll(PDO::FETCH_COLUMN);

    $id = $tag_ids[0];

    $url = $_SERVER['REQUEST_URI'];
    $image_id = (int)substr($url, -1, 1);

    if($exists){
      array_push($messages, "Tag Already Exists!");
    }
    if ($tag_ids and !$exists){

    $query_add  = "INSERT INTO image_tags (image_id, tag_id) VALUES (:image_id, :tag_id);";
    $inserting_tag = exec_sql_query($db, $query_add, array(':image_id'=> $image_id, ':tag_id' => $id));
    }


    if ($inserting_tag){
      array_push($messages, "Success!");
    }
    else{
      array_push($messages, "Tag was not added.");
    }

  }
}

//DELETING TAGS

if (isset($_POST["delete_tag"])){

  $delete_tag = $_POST["delete_tag"];

  $query_delete = "DELETE FROM image_tags WHERE image_tags.tag_id = :delete_tag;";
  $delete_params = array(":delete_tag" => $delete_tag);

  $result_delete = exec_sql_query($db, $query_delete, $delete_params);

  if($result_delete){

    array_push($messages, "Tag removed Successfully.");
  }

  }

// DELETING IMAGES
if (isset($_POST["delete_image"])){

  $delete_img = $_POST["delete_image"];


  $query = "SELECT file_ext FROM images WHERE id = :del_img;";
  $params = array(":del_img" => $delete_img);
  $result = exec_sql_query($db, $query, $params)->fetchAll(PDO::FETCH_COLUMN);

  $file_ext = $result[0];


  $query_remove = "DELETE FROM images WHERE id = :del_img;";
  $remove_params = array(":del_img" => $delete_img);
  $result_remove = exec_sql_query($db, $query_remove, $remove_params);

  if($result_remove){
    $unlink_file = "uploads/images/$delete_img.$file_ext";
    unlink($unlink_file);

    $query_remove2 = "DELETE FROM image_tags WHERE image_id = :del_img;";
    $remove_params2 = array(":del_img" => $delete_img);
    $result_remove2 = exec_sql_query($db, $query_remove2, $remove_params2);



    if($result_remove2){
      array_push($messages, "Image removed successfully.");
    }

  }

}


?>
<!DOCTYPE html>
<html>
<?php include("includes/head.php"); ?>

<body>
  <?php include("includes/header.php"); ?>
  <h1 class="nut-title"> Welcome to the Pug Community Gallery! </h1>
  <p class="pug-intro2"> Check out the Pug Community's pugs, favorite products, and much more! </p>

  <!--TAGS -->

  <form action="gallery.php" method="get">
    <h5> Tags </h5>
    <?php
    $tags = exec_sql_query($db, "SELECT DISTINCT name FROM tags", array())->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tags as $tag) {
      ?>
      <button class="tag_style" name="tag_submit" type="submit" value="<?php echo $tag ?>"> <?php echo $tag ?> </button>
    <?php
  }
  ?>
  </form>

  <?php
        foreach ($messages as $er) {
          echo "<p class  = 'messages'>" . htmlspecialchars($er) . "</p>\n";
        }
        ?>

  <?php



  $img_clicked = $_GET['image_clicked'];

  if (isset($img_clicked)) {

    $img_query = "SELECT * FROM images WHERE images.id = :image_clicked;";

    $img_params = array(":image_clicked" => $img_clicked);

    $img_records = exec_sql_query($db, $img_query, $img_params);
    $img_info = $img_records->fetchAll();

    $tags_query = "SELECT tag_id, name FROM image_tags INNER JOIN tags ON image_tags.tag_id = tags.id WHERE image_tags.image_id =:image_clicked;";
    $tags_image = exec_sql_query($db, $tags_query, array(':image_clicked'=> $img_clicked))->fetchAll();

    $arr_tags = [];
    array_push($arr_tags, $tags_image);

    if ($img_info) {

      if(is_user_logged_in() and (((int) $img_info[0]["user_id"]) == $current_user["id"])){

        $class = "tag_style2";
        $class3 = "tag_style3";
        $class2= "gal_del";

      }
      else{
        $class = "tag_style2 hidden";
        $class2 = "gal_del hidden";
        $class3 = "tag_style3 hidden";
      }
      ?>
      <div>
        <?php

          ?>
          <form method = "post">
          <figure class="centered-fig">
            <img class="gal_images" src="uploads/images/<?php echo $img_info[0]['id']; ?>.<?php echo $img_info[0]['file_ext']; ?>" alt="<?php echo htmlspecialchars($img_info[0]['id']); ?>" />
            <h5> Description </h5>
            <p class="pug-intro3"> <?php echo $img_info[0]['desc']; ?> </p>
            <h5> Citation </h5>
            <p class="pug-intro4"> <?php echo $img_info[0]['citation']; ?> </p>
            <h5> Tags</h5>
            <?php

              $length=count($arr_tags[0]);
              for ($i=0;$i<$length;$i++){
              ?>
            <p class="pug-intro3"> <?php echo $tags_image[$i]["name"]; ?> </p>

            <button class="<?php echo $class; ?>" name = "delete_tag" type="submit" value = "<?php echo $tags_image[$i]["tag_id"]?>">Remove Tag </button>
          <?php

            }
            ?>
            <p>
            <button class="<?php echo $class3; ?>" name = "delete_image" type="submit" value = "<?php echo $img_info[0]['id']?>">Delete Image </button>
              </p>

          </figure>
          </form>

          <div id = "add_tagdiv">
          <h2> Add New Tag: </h2>

          <?php
          $current_tags = exec_sql_query($db, "SELECT name FROM tags", NULL)->fetchAll(PDO::FETCH_COLUMN);
          ?>

          <form id="add_tag" method="post" enctype= "application/x-www-form-urlencoded">
            <ul>
              <li>
                <label for="added_tag">Tag:</label>
                <input id="added_tag" type="text" name="added_tag">

              </li>
              <li>
                <button name = "tag_submitted" type="submit">Add tag</button>
              </li>
          </ul>
          </form>
          <h2> Add Existing Tag: </h2>
          <form method = "post" enctype= "application/x-www-form-urlencoded">
              <li>
                    <label>Choose an Existing Tag: </label>
                    <?php
                    foreach($current_tags as $tag){
                      ?>
                    <input type="radio" name="added_tag" value="<?php echo $tag ?>"/><?php echo $tag ?>

                    <?php
                    }

                    ?>
                </li>
                <li>
                <button name = "tag_submitted" type="submit">Add tag</button>
              </li>
            </ul>
          </form>
          </div>
        <?php



    }
  }

  ?>


    <?php
    $input_tag = $_GET['tag_submit'];
    if (isset($input_tag)) {

      $tag_query = "SELECT * FROM image_tags INNER JOIN tags ON (image_tags.tag_id = tags.id) INNER JOIN images ON (images.id = image_tags.image_id) WHERE tags.name = :tag_submit;";

      $tag_params = array(":tag_submit" => $input_tag);

      $tag_records = exec_sql_query($db, $tag_query, $tag_params);

      if ($tag_records) {
        $tag_info = $tag_records->fetchAll();

        if (count($tag_info) > 0) {
          ?>
          <form method = "get">
          <div id="tag_results">

            <h5> Tag Sort Results: </h5>
            <?php
            foreach ($tag_info as $r) {
              ?>
              <div id="tag_figure">
                <figure class="centered-fig">
                  <img class="gal_images" src="uploads/images/<?php echo $r['image_id']; ?>.<?php echo $r['file_ext']; ?>" alt="<?php echo htmlspecialchars($r['image_id']); ?>" />
                  <button class="tag_style" name="image_clicked" type="submit" value="<?php echo $r['image_id'] ?>"> View Details</button>

                </figure>
              </div>
              <form>
            <?php
          }
          ?>
          </div>
        <?php
      } else {
        ?>
          <p class="pug-intro2"> No images corresponding to this tag yet. </p>
        <?php
      }
    }
  }
  ?>
  <!-- Gallery -->

  <div class="gallery_div">
      <h3> All Images </h3>


      <form method="get">
        <?php
        $gall_query = "SELECT * FROM images";
        $gall_results = exec_sql_query($db, $gall_query)->fetchAll();

        foreach ($gall_results as $img) {
          echo '<div class = "div2"> <img class = "gal_images" src="uploads/images/' . $img['id'] . "." . $img['file_ext'] . '" alt="' . htmlspecialchars($img['file_name']) . '"/></div>' . PHP_EOL;
          ?>
          <button class="tag_style" name="image_clicked" type="submit" value="<?php echo $img['id'] ?>"> View Details</button>


        <?php
      }
      ?>
    </form>
    </div>


    <?php
    if (is_user_logged_in()) {

      ?>

      <h2> Add an Image! </h2>

      <form id="image_upload" action="gallery.php" method="post" enctype="multipart/form-data">
        <ul>
          <li>
            <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILE_SIZE; ?>" />
            <label for="up_image">Upload an Image</label>
            <input id="up_image" type="file" name="up_image">
          </li>
          <li>
            <label for="tag_name">Tag:</label>
            <input id="tag_name" type="text" name="tag_name">
          </li>
          <li>
            <label for="desc">Description:</label>
            <input id="desc" type="text" name="desc">
          </li>
          <li>
            <label for="citation">Citation (Personal or Link):</label>
            <input id="citation" type="text" name="citation">
          </li>
          <li>
            <button name="submit_upload" type="submit">Upload Image</button>
          </li>
        </ul>
      </form>


       <!-- User's Images here -->
  <div class="gallery_div">
      <h3> Your Images </h3>

      <form method="get">
        <?php
        $gall2_query = "SELECT images.id, file_name, file_ext FROM images INNER JOIN users on (users.id = images.user_id) WHERE :user = images.user_id;";

        $gall2_params = array(":user" => $current_user["id"]);
        $gall2_results = exec_sql_query($db, $gall2_query, $gall2_params)->fetchAll();

        foreach ($gall2_results as $img) {

          echo '<div class = "div2"> <img class = "gal_images" src="uploads/images/' . $img['id'] . "." . $img['file_ext'] . '" alt="' . htmlspecialchars($img['id']) . '"/></div>' . PHP_EOL;
          ?>
          <button class="tag_style" name="image_clicked" type="submit" value="<?php echo $img['id'] ?>"> View Details</button>


        <?php
      }
      ?>


    </form>
    </div>


    <?php

  } else {
    ?>
      <p>Log in to continue.</p>

      <?php
      include("includes/login.php");
    }

    ?>


  <?php include("includes/footer.php"); ?>
</body>

</html>
