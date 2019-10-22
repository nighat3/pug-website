<?php
include("includes/init.php");


if (isset($_GET['id'])) {

  $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
   $query = "SELECT * FROM image_tags
           LEFT JOIN tags ON (image_tags.tag_id = tags.id)
           LEFT JOIN images ON (image_tags.image_id = images.id)
           WHERE image_tags.image_id = :id;";
  $params = array(
    ':id' => $id
  );
  $result = exec_sql_query($db, $query, $params);

  if ($result) {

    $image_info = $result->fetchAll();
    if ( count($image_info) > 0 ) {
      $one_image = $image_info[0];
    }
  }
}


?>
<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>

<body>
  <?php include("includes/header.php"); ?>

  <div>
    <?php if ( isset($one_image) ) { ?>

      <h2><?php echo htmlspecialchars($one_image['id']) ?></h2>

      <figure>
        <img src="uploads/images/<?php echo $one_image['id']; ?>. <?php echo $r['file_ext']; ?>" alt="<?php echo htmlspecialchars($one_image['image_id']); ?>"/>
      </figure>

    <?php } else { ?>

        <figure>
            <p> No tags </p>

      </figure>

    <?php } ?>
</div>




  <?php include("includes/footer.php");?>
</body>

</html>
