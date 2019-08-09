
<?php

/*
	The admin page handles editing and adding data to the database. It calls all of the functions to create the data and form info.
*/
	include_once "LIB_project1.php";
	require_once "DB.class.php";

	$db = new DB();
//creates the standard header for the pages
	createHeader("admin");

	echo "<h2 class='heading'>Administer Inventory Page</h2>";
//grabs an array off all of the products
	$productList = $db->getProducts();
	$productListSize = sizeof($productList);
	$productListSize --;
//creats the list of items that can be edited
	createEditList($productListSize, $productList);
		//waits for an item to be chosen
		if(!empty($_POST["pickOne"])) {
			echo addItemForm("Edit",$_POST["pickOne"],$productList);
		}
		//waits for the edit button to be hit so that the database can be updated
		if(!empty($_POST["Edit"])) {
			$db->updateItem($_POST["id"], $_POST["name"],$_POST["description"],$_POST["price"],$_POST["quantity"],$_POST["salesPrice"],$_POST["password"]);
		}
	//adds the form that allows useres to add new items to the database
	addItemForm("Add");
	//This waits for the add button to pressed, then activates the addItem function
		if(!empty($_POST["Add"])) {
			$db->addItem($_POST["id"], $_POST["name"],$_POST["itemImage"],$_POST["description"],$_POST["price"],$_POST["quantity"],$_POST["salesPrice"],$_POST["password"]);
		}
?>