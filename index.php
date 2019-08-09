
<?php

/*
	Written by: Quinn Bissen
	This is the index page for my site.
	It calls functions ffrom LIB_project1.php so that it can generate html
	It requires both the database and the library classes.
*/
	include_once "LIB_project1.php";
	require_once "DB.class.php";

//creates a new database class
	$db = new DB();
//gets and array of products from the database
	$saleData = $db->getProducts();
//This function generates the header for the page
	createHeader("index");

	echo "<h2 class='heading'>Monocles for Bugger All!</h2>";

	$saleSize = sizeof($saleData);
	$saleSize -= 1;
//This generates all of the sale items
	for($i = 0; $i <= $saleSize; $i++) {

		if($saleData[$i]["salePrice"] != NULL && $saleData[$i]["salePrice"] != 0){
			createSaleItem($saleData[$i]["id"], $saleData[$i]["itemName"], $saleData[$i]["itemImage"], $saleData[$i]["itemDesc"], $saleData[$i]["salePrice"], $saleData[$i]["regularPrice"], $saleData[$i]["numberLeft"]);
			
		}
		
	}	
	
	echo "<h2 class='heading'>Lord Hunton-Blather's Select Catalog</h2>";

 

	$catalogItemCounter = 0; //Keeps track of how many catalog items are displayed on a page
//This generates the catalog items	
	for($j = 0; $j <= $saleSize; $j++) {
			if($saleData[$j]["salePrice"] == NULL){
				createSaleItem($saleData[$j]["id"], $saleData[$j]["itemName"], $saleData[$j]["itemImage"], $saleData[$j]["itemDesc"],$saleData[$j]["salePrice"], $saleData[$j]["regularPrice"], $saleData[$j]["numberLeft"]);
				$catalogItemCounter++;
			}	
			
	}	
?>

