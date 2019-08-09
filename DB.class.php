<?php
/*
 This file handles all interaction with the database.
 It uses paramaterized queries and binds things.
*/
$db = new DB();

//This simply lissens to see if the 'delete cart button is hit'
if(!empty($_GET["deleteCart"])) {
		$db->deleteCart();	
}

//This is the database class that handles all of the interaction with the database
class DB{
	private $connection;

//The constructor handles the connection with the database
	function __construct() {
		$this->connection = new mysqli($_SERVER['DB_SERVER'],$_SERVER['DB_USER'],$_SERVER['DB_PASSWORD'],$_SERVER['DB']);
		if($this->connection->connect_error){
			echo "connect failed:".mysqli_connect_error();
			die();
		}
	}	
//get products uses a parameterized query to get an array of all of the products int the product table
	function getProducts() {
		$saleData = array();
		if($stmt = $this->connection->prepare("select * from products;")) {
			$stmt -> execute();

			$stmt->store_result();
			$stmt->bind_result($id, $itemName, $itemImage, $itemDesc, $salePrice, $regularPrice, $numberLeft);

			if($stmt ->num_rows >0){
				while($stmt->fetch() ){
					$saleData[] = array(
						'id' => $id,
						'itemName' => $itemName,
						'itemImage' => $itemImage,
						'itemDesc' => $itemDesc,
						'salePrice' => $salePrice,
						'regularPrice' => $regularPrice,
						'numberLeft' => $numberLeft
					);
				}
			}
		}
		return $saleData;
	}
//This function simply returns a single item from products. This is mostly used for editing a product and in cases where I only wanted to display
// a single product
	function getItemFromProducts($id) {
		$saleData = array();
				if($stmt = $this->connection->prepare("select * from products WHERE id = " . $id . ";")) {
						$stmt -> execute();

						$stmt->store_result();
						$stmt->bind_result($id, $itemName, $itemImage, $itemDesc, $salePrice, $regularPrice, $numberLeft);

						if($stmt ->num_rows >0){
							while($stmt->fetch() ){
								$saleData[] = array(
									'id' =>$id,
									'itemName' => $itemName,
									'itemImage' => $itemImage,
									'itemDesc' => $itemDesc,
									'salePrice' => $salePrice,
									'regularPrice' => $regularPrice,
									'numberLeft' => $numberLeft
								);
							}
						}
				}
				return $saleData;
	}
//This function returns an array of the cart table. It is mostly used to reference the cart and find relevent ID numbers inside of the cart
//It works very similarly to the get products function
//I do not feel that I could have combined these functions as they are to different from each other and it would have made writing other sections far more confusing
	function getCart() {
		$cartData = array();
			if($stmt = $this->connection->prepare("select * FROM cart;")) {
				$stmt -> execute();

				$stmt->store_result();
				$stmt->bind_result($id, $itemName, $itemDesc, $numberOf, $price);

				if($stmt ->num_rows >0){
					while($stmt->fetch() ){
						$cartData[] = array(
							'id' =>$id,
							'productName' => $itemName,
							'productDescription' => $itemDesc,
							'numberOf' => $numberOf,
							'price' => $price
						);
					}
				}
			}
		return $cartData;
	}
//This function takes all the items in the cart and returns thier total cost
	function getCartTotal(){
		$totalCost = 0;
		$cartInfo = $this->getCart();
		$cartInfoSize = sizeof($cartInfo);
		$cartInfoSize--;
		for($i=0; $i<=$cartInfoSize; $i++) {
			$totalCost = $totalCost + $cartInfo[$i]["price"];
		}

		return $totalCost;	
	}
//This simply returns a row count of get the products table
	function getRowCount() {
		$saleData = $this->getProducts();
		$saleSize = sizeof($saleData);
		return $saleSize -=1;
	}
//This function allows users to insert items into the cart table. This is accomplished by simply hitting the button labeld: add to cart
//This too uses parameterized queries
	function insertCart($id,$productName, $productDescription, $numberOf, $price) {
		$queryString = "insert into cart (id, productName, productDescription, numberOf, price) values
			(?,?,?,?,?)";
		$insertId = -1;

		if ($stmt = $this->connection->prepare($queryString)) {
			$stmt->bind_param("issii",$id,$productName,$productDescription,$numberOf, $price);
			$stmt->execute();
			$stmt->store_result();
			$insertId = $stmt->insert_id;
		}
		return $insertId;
	}
//This function allows a user to insert an item into the products table
	function insertItem(){
		$cartDB = new DB();

		if(!empty($_GET["cartItem"])) {
			$cartItem = $this->getItemFromProducts($_GET["cartItem"]);
			$this->insertCart($cartItem[0]["id"],$cartItem[0]["itemName"],$cartItem[0]["itemDesc"], 1, $cartItem[0]["regularPrice"]);
			$numberOf = $cartItem[0]["numberLeft"];
			$numberOf --;
			$this->updateNumberOf($numberOf);
		}		
	}
//This function simply deletes the entire cart
	function deleteCart() {
		$queryString = "DELETE FROM cart;";

		if ($stmt = $this->connection->prepare($queryString)) {
			$stmt->execute();
			$stmt->store_result();
			header("Location: http://serenity.ist.rit.edu/~qsb2538/341/project1/cart.php");
		}
	}
//This function is called when an item is added to the cart. It reduces the column: number left by one so emmulate an item being removed from storage
	function updateNumberOf($numberOf){
		$queryString = "UPDATE products SET numberLeft=? WHERE id=?";

		if ($stmt = $this->connection->prepare($queryString)) {
			$stmt->bind_param("ii",$numberOf,$_GET["cartItem"]);
			$stmt->execute();
			$stmt->store_result();
			$insertId = $stmt->insert_id;
		}
	}
/*
	This function allows a users to update an existing item. The user must go into admin page, select the item that they want to edit, and hit the select button.
	When the user hits the 'edit' button, the form vields are taken in and first sanitized, then the validate. If the validation fails the user is notified.
	The function also checks to see if the password field is correct.
	Once this is completed the existing item will finally be updated.
*/
	function updateItem($id, $itemName, $itemDesc, $itemPrice, $numberLeft, $salesPrice, $password) {
		if($password == "testyMcTestFace") {
			$id = $this->sanitizeData($id);
			$itemName = $this->sanitizeData($itemName);
			$itemDesc = $this->sanitizeData($itemDesc);
			$itemPrice = $this->sanitizeData($itemPrice);
			$numberLeft = $this->sanitizeData($numberLeft);
			$salesPrice = $this->sanitizeData($salesPrice);

			if($this->validate($id, $itemName, $itemDesc, $itemPrice, $numberLeft, $salesPrice) == true) {

				if(!empty($salesPrice)){
					if($this->getNumberOfSaleItems() > 3 || $salesPrice == 0) {
						$queryString = "UPDATE products SET itemName=?, itemDesc=?, salePrice=?, regularPrice = ?, numberLeft= ? WHERE id=?";

						if ($stmt = $this->connection->prepare($queryString)) {
							$stmt->bind_param("ssiiii",$itemName, $itemDesc, $salesPrice, $itemPrice, $numberLeft, $id);
							$stmt->execute();
							$stmt->store_result();
							$insertId = $stmt->insert_id;
						}	
					} else {
						echo "I am sorry millord, but you have to many items on sale! Try lowering the number of items on sale to less than three.";
					}
				}
				if(empty($salesPrice) || $salesPrice == 0)	{
					$queryString = "UPDATE products SET itemName=?, itemDesc=?, salePrice=?, regularPrice = ?, numberLeft= ? WHERE id=?";

					if ($stmt = $this->connection->prepare($queryString)) {
						$stmt->bind_param("ssiiii",$itemName, $itemDesc, $salesPrice, $itemPrice, $numberLeft, $id);
						$stmt->execute();
						$stmt->store_result();
						$insertId = $stmt->insert_id;
					}
				}
			} else {
				echo "Invalid form fields good sir. Please redo them.";
			}	
		} else {
			echo "My good sir! You have entered the wrong password, please try again.";
		}	
	}

	/*
		This function works similarly to the update function except that it adds an item 
		The function first checks to see if the password is correct
		Next it sanitizes the data
		Then it validates the data
		Finally if all of these conditions pass, the data is entered into he database.
	*/
	function addItem($id, $itemName, $itemDesc, $itemImage, $itemPrice, $numberLeft, $salesPrice, $password) {
		if($password == "testyMcTestFace") {
			$id = $this->sanitizeData($id);
			$itemName = $this->sanitizeData($itemName);
			$itemImage = $this->sanitizeData($itemImage);
			$itemDesc = $this->sanitizeData($itemDesc);
			$itemPrice = $this->sanitizeData($itemPrice);
			$numberLeft = $this->sanitizeData($numberLeft);
			$salesPrice = $this->sanitizeData($salesPrice);
			if($this->validate($id, $itemName, $itemDesc, $itemPrice, $numberLeft, $salesPrice) == true) {
				if(!empty($salesPrice)){
					if($this->getNumberOfSaleItems() > 3 || $salesPrice == 0) {

						$queryString = "INSERT INTO products VALUES (?,?,?,?,?,?,?)";

						if ($stmt = $this->connection->prepare($queryString)) {
							$stmt->bind_param("isssiii", $id, $itemName, $itemImage, $itemDesc, $salesPrice, $itemPrice, $numberLeft);
							$stmt->execute();
							$stmt->store_result();
							$insertId = $stmt->insert_id;
						}
					} else {
						echo "I am sorry millord, but you have to many items on sale! Try lowering the number of items on sale to less than three.";
					}	
				} 
				if(empty($salesPrice) || $salesPrice == 0)	{
					$queryString = "INSERT INTO products VALUES (?,?,?,?,?,?,?)";

					if ($stmt = $this->connection->prepare($queryString)) {
							$stmt->bind_param("isssiii", $id, $itemName, $itemImage, $itemDesc, $salesPrice, $itemPrice, $numberLeft);
							$stmt->execute();
							$stmt->store_result();
							$insertId = $stmt->insert_id;
					}
				}
			} else {
				echo "Invalid form fields, please fix them my good sir.";
			}		
		} else {
			echo "My good sir! You have entered the wrong password, please try again.";
		}	
	}

	//This function simply returns the total number of sale items from the prodcuts table

	function getNumberOfSaleItems() {
		if($stmt = $this->connection->prepare("select salePrice from products;")) {
				$stmt -> execute();
				$stmt->store_result();
				$stmt->bind_result($salePrice);

				if($stmt ->num_rows >0){
					while($stmt->fetch() ){
						$saleData[] = array(
							'salePrice' => $salePrice
						);
					}
				}
				$salePrice = array_filter($saleData);
				$numberOfSaleItems = sizeof($salePrice);
		}
		return $numberOfSaleItems;
	}

	//Sanitize data trims, strips slashes, and removes special characters from the data
	function sanitizeData($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);

		return $data;
	}
	// validate checks to see if the field is empty and that the correct data type has been inputed.
	function validate($id, $salePrice, $regularPrice, $numberLeft) {
		if(is_int($id) && is_int($salePrice) && is_int($regularPrice) && is_int($numberLeft)) {
			if(is_string($itemName) && is_string($itemDesc)){
				return true;
			}
		}
	}


}
?>