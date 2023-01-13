<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Order</title>
	<link href='https://fonts.googleapis.com/css?family=Bitter' rel='stylesheet'>

	<style type="text/css">
		body{
			margin: 50px;
			font-family: Bitter;
			/*font-family: times new roman;*/
			/*font-family: Arial, Helvetica, sans-serif;*/
			/*background-color: #b4e2f7;*/
		}
		@page{
			margin: 0px;
			padding: 0px;
		}
		.comp-tbl{
			font-size: 15px;
			/*font-weight: bold;*/
			color: #0e6aba;
		}
		.tbl-bg{
			background: #eff3f6;
		}
		.tbl-head{
			padding: 15px;
			font-size: 22px;
			font-weight: bold;
			color: white;
			background: #0e6aba;
		}
		.tbl-th{
			color: #0e6aba;
			padding: 13px;
			font-size: 18px;
		}
		.tbl-td{
			/*font-family: Arial, Helvetica, sans-serif;*/
			font-size: 15px;
			padding: 13px;
			padding-top: 0px;
		}
		.customimg{
  			height: 90px;
  			width: 90px;
  			object-fit: cover;
  		}
  		.mycustom{
  			font-weight: 600;
  			/*font-family: system-ui;*/
  			color: #0E6ABA;
  		}
	</style>
</head>
<body>

	<?php $uniques = []; ?>

	@foreach($product as $prod)
		@if(!in_array($prod->company_image, $uniques))
			<?php
	        	$uniques[] = $prod->company_image;
	        ?>
	        <div class="comp-border">

				<table width="100%" cellpadding="5px" cellspacing="0" class="comp-tbl" border="0">
					<tr>
						<td align="center">
							<img src="{{ $prod->company_image }}" class="customimg">
						</td>
						<td style="float: left;">
							<center class="mycustom">
								{{ $prod->company_name }}, {{ $prod->building_number }}, {{ $prod->address_line_1 }}, {{ $prod->city }}, {{ $prod->country }}, {{ $prod->postcode }}
							</center>

						 	<center class="mycustom">
						 		{{ $prod->telephone }}, {{ $prod->email }}, {{ $prod->website }}
						 	</center>

						 	<center class="mycustom">Company no. {{ $prod->company_number }}</center>
						 </td>
					</tr>
				</table>
			</div><br>
		@endif
	@endforeach


	<?php $unique_order = []; ?>

	<table width="100%" border="0" class="tbl-bg">
		<tr>
			<th align="left" colspan="2" class="tbl-head">Order Details</th>
		</tr>
		@foreach($product as $prod)
			@if(!in_array($prod->orderno, $unique_order))
				<?php
		        	$unique_order[] = $prod->orderno;
		        ?>
				<tr>
					<th align="left" class="tbl-th">Order number</th>
					<th align="left" class="tbl-th">Customer number</th>
				</tr>
				<tr>
					<td align="left" class="tbl-td">{{ $prod->orderno }}</td>
					<td align="left" class="tbl-td">{{ $prod->user_number }}</td>
				</tr>
				<tr>
					<th align="left" class="tbl-th">Order date</th>
					<th align="left" class="tbl-th">Paid by</th>
				</tr>
				<tr>
					<td align="left" class="tbl-td"><?php echo date('d-m-Y',strtotime($prod->created_at)); ?></td>
					<td align="left" class="tbl-td">{{ $prod->first_name }} {{ $prod->surname }}</td>
				</tr>
			@endif
		@endforeach
			<tr>
				<th align="left" class="tbl-th">Quantity</th>
				<th align="left" class="tbl-th">Order total price</th>
			</tr>
			<tr>
				<td align="left" class="tbl-td">
					<?php $qty = 0; ?>
					@foreach($product as $prod)
					<?php
						$qty += $prod->orderquantity;
					?>
					@endforeach

					{{ $qty }}
				</td>
				<td align="left" class="tbl-td">
					<?php $totalprice = 0; ?>
					@foreach($product as $prod)
					<?php
						$totalprice += $prod->ordertotal;
						$text = $prod->product_currency;
						preg_match('#\((.*?)\)#', $text, $match);
					?>
					@endforeach

					{{ isset($match[1]) ? $match[1] : '' }} {{ $totalprice }}
				</td>
			</tr>
	</table><br><br>

	<table width="100%" border="0" class="tbl-bg">
		<tr>
			<th align="left" colspan="3" class="tbl-head">Product Details</th>
		</tr>
		@foreach($product as $prod)
			<tr>
				<td align="center" width="20%" rowspan="3" style="padding-top: 20px;">
					<img src="{{ $prod->product_image }}" width="100px" height="100px" style="border-radius: 10px;">
					<br>
					<center>{{ $prod->product_number }}</center>
				</td>
				<td align="left" width="60%" colspan="2" style="font-size: 20px;">{{ $prod->product_name }}</td>
			</tr>
			<tr>
				<td align="left" width="20%" style="font-size: 17px;">Quantity: {{ $prod->orderquantity }}</td>
				<td align="left" style="font-size: 17px;">
					Price:
					<?php
						$text = $prod->product_currency;
						preg_match('#\((.*?)\)#', $text, $match);
					?>
					{{ isset($match[1]) ? $match[1] : '' }} {{ $prod->price }}
				</td>
			</tr>
			<tr>
				<th align="left" colspan="2" style="font-weight: bold;font-size: 20px;color: #0e6aba;">
					<?php
						$text = $prod->product_currency;
						preg_match('#\((.*?)\)#', $text, $match);
					?>
					{{ isset($match[1]) ? $match[1] : '' }} {{ $prod->ordertotal }}
				</th>
			</tr>
			<tr>
				<td style="background: 0e6aba;" colspan="3"></td>
			</tr>
		@endforeach
	</table><br><br>


	<table width="100%" border="0" class="tbl-bg">
		<tr>
			<?php $total = 0; ?>
			@foreach($product as $prod)
			<?php
				$total += $prod->ordertotal;
				$text = $prod->product_currency;
				preg_match('#\((.*?)\)#', $text, $match);
			?>
			@endforeach
			<th colspan="3" class="tbl-head">Total price: {{ isset($match[1]) ? $match[1] : '' }} {{ $total }}</th>
		</tr>
	</table>

</body>
</html>
