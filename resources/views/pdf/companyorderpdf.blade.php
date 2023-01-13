  <!DOCTYPE html>
  <html lang="en">
  <head>
    <title>Afrobizfind</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href='https://fonts.googleapis.com/css?family=Bitter' rel='stylesheet'>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

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
		.tblheader
  		{  		
  			padding: 15px;
  			font-size: 22px;
  			font-weight: bold;
  			color: white;
  			background: #0e6aba;
  		}
  		.mycustom{
  			font-weight: 600;
  			/*font-family: system-ui;*/
  			color: #0E6ABA;
  		}
  		.customimg{
  			height: 90px;
  			width: 90px; 
  			object-fit: cover;
  		}
	</style>
  </head>
  <body>

  	<div class="comp-border">
  		<table width="100%" cellpadding="5px" cellspacing="0" class="comp-tbl" border="0">
  			<tr>
  				<td align="center">
  					<img src="{{ $getcompany->image }}" class="customimg">
  				</td>
  				<td style="float: left;">
  					<center class="mycustom">
  						{{ $getcompany->company_name }}, {{ $getcompany->building_number }}, {{ $getcompany->address_line_1 }}, {{ $getcompany->city }}, {{ $getcompany->country }}, {{ $getcompany->postcode }}
  					</center>

  					<center class="mycustom">
  						{{ $getcompany->telephone }}, {{ $getcompany->email }}, {{ $getcompany->website }}
  					</center>

  					<center class="mycustom">Company no. {{ $getcompany->company_number }}</center>
  				</td>
  			</tr>
  		</table>
  	</div><hr>

  <div class="container-fluid">
  	
  	@if($cmpproductorder)
	  	<div class="tblheader">Product Orders</div>
	    <table class="table table-striped">
	    	<thead>
	    		<tr style="color: #0E6ABA;">
	    			<th width="16%">Order Date</th>
	    			<th>OrderNo</th>
	    			<th>Product Name</th>
	    			<th>Customer no</th>
	    			<th width="14%">Price</th>
	    			<th>Quantity</th>
	    			<th width="18%">Total</th>
	    		</tr>	
	    	</thead>
	      <tbody>
	      	<?php 
	      		
	      		$finalproductorder=0;
	      		$finaleventorder=0;

	      	?>
	    @foreach($cmpproductorder as $productorder)
	    <?php $finalproductorder=$finalproductorder+$productorder->totalprice; ?>
	    <tr>
	    	<td>    	
	    	{{ date('d-m-Y', strtotime($productorder->created_at)) }}    	
	    	</td>
	    	<td>
	    	{{ $productorder->orderno }}    		
	    	</td>
	    	<td>
	    	{{ $productorder->product_name }}    		
	    	</td>
	    	<td>
	    	{{ $productorder->user_number }}
	    	</td>
	    	<td>
	    		<?php	preg_match_all('/\(([^\)]*)\)/', $productorder->currency, $mymatch); ?>
	    		{{ $mymatch[1][0] }} {{ $productorder->price }}		
	    	</td>
	    	<td>
	    	{{ $productorder->quantity }}    		
	    	</td>
	    	<td>
	    	{{ $mymatch[1][0] }} {{ $productorder->totalprice }}
	    	</td>
	    </tr>
	    @endforeach
	      </tbody>
	    </table>
	    <div class="tblheader" style="text-align: center;">Products Order Total: {{ $mymatch[1][0] }} {{ $finalproductorder }}</div>
	    
  	@endif

   	@if($cmpeventorder)
   	 	<hr><br>

	    <div class="tblheader">Event Orders</div>
	    <table class="table table-striped" style="text-align: center;">
	    	<thead>
	    		<tr style="color: #0E6ABA">
	    			<th width="18%">Order Date</th>
	    			<th>OrderNo</th>
	    			<th>Event Name</th>
	    			<th>Customer no</th>
	    			<th>Price</th>
	    			<th width="7%">Quantity</th>
	    			<th>Total</th>
	    		</tr>	
	    	</thead>
	      <tbody>

	    @foreach($cmpeventorder as $eventorder)
	    <?php $finaleventorder=$finaleventorder+$eventorder->totalprice; ?>
	    <tr>
	    	<td>    	
	    	{{ date('d-m-Y', strtotime($eventorder->created_at)) }}    	
	    	</td>
	    	<td>
	    	{{ $eventorder->orderno }}    		
	    	</td>
	    	<td>
	    	{{ $eventorder->eventname }}    		
	    	</td>
	    	<td>
	    	{{ $eventorder->user_number }}
	    	</td>
	    	<td>
	    		<?php	preg_match_all('/\(([^\)]*)\)/', $eventorder->currency, $mymatch); ?>
	    		{{ $mymatch[1][0] }} {{ $eventorder->price }}
	    	</td>
	    	<td>
	    	{{ $eventorder->quantity }}    		
	    	</td>
	    	<td>
	    	{{ $mymatch[1][0] }} {{ $eventorder->totalprice }}
	    	</td>
	    </tr>
	    @endforeach
	      </tbody>
	    </table>
	    <div class="tblheader" style="text-align: center;">Event Order Total: {{ $mymatch[1][0] }} {{ $finaleventorder }}</div>
   	@endif

  </div>

  </body>
  </html>
