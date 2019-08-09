<?php
	/*
		This is the libray page.
		The library page generates most of the html for the site. It hosts the reusable functions.
	*/

	//This function creates the header for all of the pages.	
	function createHeader($currentPage) {
		echo "<head>
				<meta http-equiv='content-type' content='text/html; charset=utf-8' />
				<title>Project One Home</title>
				<link href='css/project1.css' type='text/css' rel='stylesheet' />
			</head>
			<body>
				<div id='banner'><img src='assets/logo.jpg' alt='logo' width=150px /><span class='banner'>The Good Lads - A Monocle Company</span></div>
					<ul id='navlist'>
						<li><a href='index.php' id=" . $currentPage . ">Home</a></li>
						<li><a href='cart.php' id=" . $currentPage . ">Cart</a></li>
						<li><a href='admin.php' id=" . $currentPage . ">Admin</a></li>
					</ul>
			</body>";			
	}

	//This function creates and formates the items on sale. 
	function createSaleItem($id,$itemName, $itemImage, $itemDesc, $salePrice, $regularPrice, $numberLeft = 0) {
		$isCatalog = NULL;

		if ($salePrice == NULL) {
			$isCatalog = "<p><strong>Price:</strong> " . $regularPrice . "  Only <strong> " . $numberLeft . "</strong> left!</p>";
		} else {
			$isCatalog = "<p><strong>Sale Price:</strong> " . $salePrice . " (Regularly: " . $regularPrice . ").  Only <strong>" . $numberLeft . "</strong> left!</p>";
		}

		echo "
			<div id='sales'>
				<div class='one_item'>
					<h3>" . $itemName . "</h3>
					<img class='aleft' src='" . $itemImage . "' height = 100px />
					<p>" . $itemDesc . "</p>" .
					$isCatalog .

					"<div>
						<form method='get' action='cart.php'>
							<input type='hidden' name='cartItem' value='" . $id  . "' />
							<input type='submit' name='add' value='Add To Cart'/>
						</form>
					</div>
				</div>	
			</div>
		";
	}

	//This function creates the cart list
	function createCartList($productName, $productDesc, $numberOf) {
		echo "
			<div id='sales'>
				<div class='one_item'>
					<h3>" . $productName . "</h3>
					<p>" . $productDesc . "</p>
					<p> Number ordered: " . $numberOf . "</p>
				</div>	
			</div>
		";
	}
	//This function creates the list of items that are available to be edited. Each item is listed as an option and when one of the options is selected a post is sent to the admin list
	function createEditList($productsListSize, $productList) {
		echo "
			<div class='box'>
			<table>
				<tr>
					<td>
						<div>	
						<form action='admin.php' method='post'>
							Choose an item to Edit: 
							<select name='pickOne'>";
								
								for($i = 0; $i <= $productsListSize; $i++) {
											echo "<option value='" . $productList[$i]["id"] . "'> " . $productList[$i]["itemName"] . "</option>";
								}
									
		echo "
							</select>

							<input type='hidden' name='editID value='' />
							<input type='submit' name='editID' value='Select Item'/>
						</form></div>
					</td>
				</tr>
			</table>
			<br />
			</div>";
	}
	//This form functions similar to the edit list function. It creates a form that allows users to input a new item into the database
	function addItemForm($editOrAdd, $id = NULL, $productList = NULL) {	
		$id--;
		echo "
			<div class='box'>

				   <form action='admin.php' method='post' enctype='multipart/form-data'>
							<table>
								<tr><td colspan='2' class='areaHeading'> " . $editOrAdd . " Item:</td></tr>
			   					 <tr>
								   <td>
									   Id:
								   </td>
								   <td>
									   <input type='text' name='id' size='6' value='" . $productList[$id]["id"] . "' />
								   </td>
							   </tr>
							   <tr>
								   <td>
									   Name:
								   </td>
								   <td>
									   <input type='text' name='name' size='40' value='" . $productList[$id]["itemName"] . "' />
								   </td>
							   </tr>
							   <tr>
								   <td>
									   Item Image:
								   </td>
								   <td>
									   <textarea name='itemImage' rows='3' cols='60'>" . $productList[$id]["itemImage"] . "</textarea>
								   </td>
							   </tr>
							   <tr>
								   <td>
									   Description:
								   </td>
								   <td>
									   <textarea name='description' rows='3' cols='60'>" . $productList[$id]["itemDesc"] . "</textarea>
								   </td>
							   </tr>
							   <tr>
								   <td>
									   Price:
								   </td>
								   <td>
									   <input type='text' name='price' size='40' value='" . $productList[$id]["regularPrice"] . "' />
								   </td>
							   </tr>
							   <tr>
								   <td>
									   Quantity in stock:
								   </td>
								   <td>
									   <input type='text' name='quantity' size='40' value='" . $productList[$id]["numberLeft"] . "' />
								   </td>
							   </tr>
							   <tr>
								   <td>
									   Sale Price:
								   </td>
								   <td>
									   <input type='text' name='salesPrice' size='40' value='" . $productList[$id]["salePrice"] . "' />
								   </td>
							   </tr>
							   <tr>
								   <td>
									   New Image:
								   </td>
								   <td>
									   <input type='file' name='image' />
								   </td>
							   </tr>
						   		<tr>
								   <td>
											<strong>Your Password: </strong>
									</td>
									<td>
										<input type='password' name='password' size='15' />
									</td>
										<input type='hidden' name='" . $editOrAdd . "' value = '" . $editOrAdd . "' />
						   </table>
						   <br />
						   <input type='reset' name='reset' value='Reset Form'/>
						   <input type='submit' name='submitItems' value='" . $editOrAdd . "' />
					   </form>
			</div>
			</div>
			";
	}
?>