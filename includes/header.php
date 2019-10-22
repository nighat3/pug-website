  <header>

      <h1 id = "main-title"> PUGS </h1>
      <nav>
          <ul>
          <?php
            $navbar = [['index.php', 'HOME'], ['about-breed.php', 'THE BREED'], ['nutrition.php', 'NUTRITION'], ['lifestyle.php', 'LIFESTYLE'], ['products.php', 'PRODUCTS'],['gallery.php', 'GALLERY'], ['contact.php', 'CONTACT']];

            foreach($navbar as $element) {
                if (basename($_SERVER['PHP_SELF']) == $element[0]){
                    echo "<li><a id = 'current-page' class = 'navlines' href='$element[0]'>$element[1]</a></li>";
                }
                else{
                  echo "<li><a class = 'navlines' href='$element[0]'>$element[1]</a></li>";
                }
              }

              if ( is_user_logged_in() ) {
                $log_url = htmlspecialchars( $_SERVER['PHP_SELF'] ) . '?' . http_build_query( array( 'logout' => '' ) );

                echo '<li><a class = "logout" href="' . $log_url . '">SIGN OUT</a></li>';
              }
              ?>

          </ul>
      </nav>
  </header>
