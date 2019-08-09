<?php
/*
This class generates the cart page
*/
	include_once "LIB_project1.php";
	include "DB.class.php";
//creats the standared header for the page
	createHeader("cart");
	$cartDB = new DB();
//calls the insertItem to generate the needed items for the page
	$cartDB->insertItem();	
//grabs the cart list as an array
	$cartList = $cartDB->getCart();
	$cartSize = sizeof($cartList);
	$cartSize -= 1;
//iterates throught the cart list generating the contents of the cart
	for($i = 0; $i <= $cartSize; $i++) {
		createCartList($cartList[$i]["productName"], $cartList[$i]["productDescription"],$cartList[$i]["numberOf"]);
	}

	$totalPrice = $cartDB->getCartTotal();
//echos out the total price and the delete button
	echo "
		<p>Total price: " . $totalPrice ." </p>
		<div>
			<form method='get' action='DB.class.php'>
				<input type='hidden' name='deleteCart' value='true' />
				<input type='submit' name='add' value='Delete Cart'/>
			</form>
		</div>";	
?>