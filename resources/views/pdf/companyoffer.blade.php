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
  		<div class="tblheader">Offer List</div>
  		<table class="table table-striped">
  			<thead>
  				<tr style="color: #0E6ABA;">
  					<th>Offer Number</th>
  					<th>Name</th>
  					<th>Details</th>
  					<th>Offer Price</th>
                    <th>Discount</th>
  				</tr>	
  			</thead>
  			<?php $totaloffer=0; ?>
  			<tbody>
  				@foreach($getoffer as $offer)
  				<?php $totaloffer=$totaloffer+1; ?>
  				<tr>
  					<td>
  						{{ $offer->offer_number }}    		
  					</td>
  					<td>
  						{{ $offer->name }}    		
  					</td>
  					<td>
  						{{ $offer->offer_details }}
  					</td>
  					<td>
                        <?php preg_match_all('/\(([^\)]*)\)/', $offer->currency, $mymatch);
                         ?>
  						{{ $mymatch[1][0] }} {{ $offer->price }}
  					</td>
                    <td>                        
                        {{ $offer->discount }}                                 
                    </td>
  				</tr>
  				@endforeach
  			</tbody>
  		</table>
  		<div class="tblheader" style="text-align: center;">Number of offers: {{ $totaloffer }}</div>
  		<br><hr>   

  	</div>

  </body>
  </html>
