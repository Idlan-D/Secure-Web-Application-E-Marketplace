<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- basic -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- mobile metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <!-- site metas -->
    <title>Lan Bakery</title>
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- bootstrap css -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <!-- style css -->
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <!-- Responsive-->
    <link rel="stylesheet" href="css/responsive.css">
    <!-- fevicon -->
    <link rel="icon" href="images/fevicon.png" type="image/gif" />
    <!-- Scrollbar Custom CSS -->
    <link rel="stylesheet" href="css/jquery.mCustomScrollbar.min.css">
    <!-- Tweaks for older IEs-->
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css">
    <!-- fonts -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,700&display=swap" rel="stylesheet">
    <!-- font awesome -->
    <link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- owl stylesheets -->
    <link href="https://fonts.googleapis.com/css?family=Great+Vibes|Poppins:400,700&display=swap&subset=latin-ext" rel="stylesheet">
</head>
<body>
    <!-- banner bg main start -->
    <div class="banner_bg_main">
        <!-- header top section start -->
        <div class="container">
            <div class="header_section_top">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="custom_menu">
                            <ul>
                                <li><a href="about.html">About Us</a></li>
                                <li><a href="login.html">Login</a></li>
                                <li><a href="contact.html">Contact</a></li>
                                <li><a href="menu.php">Menu</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- header top section start -->

        <!-- header section start -->
        <div class="header_section">
            <div class="container">
                <div class="containt_main">
                    <div id="mySidenav" class="sidenav">
                        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
                        <a href="index.html">Lan Bakery</a>
                        <a href="login.html">Login</a>
                        <a href="contact.html">Contact</a>
                        <a href="menu.php">Menu</a>
                    </div>
                    <span class="toggle_icon" onclick="openNav()"><img src="images/toggle-icon.png"></span>
                    <div class="main">
                    </div>
                    <div class="header_box">
                        <div class="login_menu">
                            <ul>
                                <li><a href="cart.php">
                                    <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                                    <span class="padding_10">Cart</span></a>
                                </li>
                                <li><a href="profile.php">
                                    <i class="fa fa-user" aria-hidden="true"></i>
                                    <span class="padding_10">Profile</span></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- header section end -->
        <!-- banner section start -->
        <div class="banner_section layout_padding">
            <div class="container">               
                <div class="buynow_bt"><a href="menu.php">Menu</a></div>
            </div>
        </div>
        <!-- banner section end -->
    </div>
    <!-- banner bg main end -->

    <div class="untree_co-section product-section before-footer-section">
        <div class="container">
            <div class="row">
                <!-- Fetching and displaying product data from the database -->
                <?php
                // Database connection
                $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=u875650075_idlan_database', 'u875650075_idlan', 'Idlan@123');

                // Fetch product data from the database
                $stmt = $pdo->query("SELECT name, price, image_url FROM items");
                $productData = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Loop through each product and display it
                foreach ($productData as $product) {
                ?>
                <div class="col-12 col-md-4 col-lg-3 mb-5">
                    <a class="product-item">
                        <img src="<?php echo $product['image_url'];?>" class="img-fluid product-thumbnail">
                        <h3 class="product-title"><?php echo $product['name'];?></h3>
                        <strong class="product-price">RM<?php echo $product['price'];?></strong>

                        <!-- Quantity input field placed under the price -->
                        <input type="number" class="quantity-input" value="1" min="1" style="width: 60px; margin-top: 10px;">

                        <!-- Add a data attribute to store product name, price, image URL, and quantity -->
                        <span class="icon-cross" onclick="addtocart(
                          '<?php echo $product['name'];?>',
                          '<?php echo $product['price'];?>',
                          '<?php echo $product['image_url'];?>',
                          this.previousElementSibling.value  // Get the value of the quantity input
                         ); event.preventDefault();">
                         <img src="images/cross.svg" class="img-fluid">
                       </span>
                    </a>
                </div> 
                <?php } ?>
                <!-- End Fetching and displaying product data -->
            </div>
        </div>
    </div>

     <!-- footer section start -->
      <div class="footer_section layout_padding">
         <div class="container">
            <div class="footer_logo"><a href="index.html"><img src="images/footer-logo-1.png"></a></div>
            <div class="input_bt">
               <input type="text" class="mail_bt" placeholder="Email" name="Email">
               <span class="subscribe_bt" id="basic-addon2"><a href="#">Join Us</a></span>
            </div>
            <div class="footer_menu">
               <ul>
                  <li><a href="about.html">About us</a></li>
                  <li><a href="login.html">Log in</a></li>
                  <li><a href="contact.html">Contact</a></li>
                  <li><a href="menu.php">Menu</a></li>
               </ul>
            </div>
            <div class="location_main">Contact Number : <a href="#">+011 23771211</a></div>
         </div>
      </div>
      <!-- footer section end -->
      <!-- copyright section start -->
      <div class="copyright_section">
         <div class="container">
            <p class="copyright_text">© 2024 All Rights Reserved. Design by Idlan Durrani</a></p>
         </div>
      </div>
      <!-- copyright section end -->
      <!-- Javascript files-->
      <script src="js/jquery.min.js"></script>
      <script src="js/popper.min.js"></script>
      <script src="js/bootstrap.bundle.min.js"></script>
      <script src="js/jquery-3.0.0.min.js"></script>
      <script src="js/plugin.js"></script>
      <!-- sidebar -->
      <script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
      <script src="js/custom.js"></script>
      <script>
         function openNav() {
           document.getElementById("mySidenav").style.width = "250px";
         }
         
         function closeNav() {
           document.getElementById("mySidenav").style.width = "0";
         }
      </script>

      <!-- Add a JavaScript function to handle the "add to cart" functionality -->
      <script>
        function addtocart(name, price, imageUrl, quantity) {
          // Create a JSON object to send to the server
          var data = {
            "product_name": name,
            "price": price,
            "image_url": imageUrl,
            "quantity": quantity // Include quantity in the data object
          };

          // Send an AJAX request to the server
          $.ajax({
            type: "POST",
            url: "addtocart.php",
            data: data,
            dataType: "json",
            success: function(response) {
              if (response.success) {
                alert("Product added to cart successfully!");
              } else {
                alert("Error: " + response.message);
              }
            }
          });
        }
      </script>
   </body>
</html>
