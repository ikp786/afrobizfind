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
			/*font-family: Bitter;*/
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

	<div class="comp-border">
		<table width="100%" cellpadding="5px" cellspacing="0" class="comp-tbl" border="0">
			<tr>
				<td align="center">
					<img src="{{ $event->company_image }}" class="customimg">
				</td>
				<td style="float: left;">
					<center class="mycustom">
						{{ $event->company_name }}, {{ $event->building_number }}, {{ $event->address_line_1 }}, {{ $event->city }}, {{ $event->country }}, {{ $event->postcode }}
					</center>
				 	
				 	<center class="mycustom">
				 		{{ $event->telephone }}, {{ $event->email }}, {{ $event->website }}
				 	</center>
				 	<center class="mycustom">Company no. {{ $event->company_number }}</center>
				 </td>
			</tr>
		</table>
	</div><br>


	<table width="100%" border="0" class="tbl-bg" style="margin-bottom: 10px;">
		<tr>
			<th align="left" colspan="2" class="tbl-head">Order Details</th>
		</tr>

		<tr>
			<th align="left" class="tbl-th">Order number</th>			
			<th align="left" class="tbl-th">Customer number</th>
		</tr>
		<tr>
			<td align="left" class="tbl-td">{{ $event->orderno }}</td>
			<td align="left" class="tbl-td">{{ $event->user_number }}</td>
		</tr>
		<tr>
			<th align="left" class="tbl-th">Order date</th>
			<th align="left" class="tbl-th">Paid by</th>
		</tr>
		<tr>
			<td align="left" class="tbl-td"><?php echo date('d-m-Y',strtotime($event->created_at)); ?></td>
			<td align="left" class="tbl-td">{{ $event->first_name }} {{ $event->surname }}</td>
		</tr>
		<tr>
			<th align="left" class="tbl-th">Quantity</th>	
			<th align="left" class="tbl-th">Order total price</th>		
		</tr>
		<tr>
			<td align="left" class="tbl-td">{{ $event->orderquantity }}</td>
			<td align="left" class="tbl-td">
				<?php
				$text = $event->event_currency;
				preg_match('#\((.*?)\)#', $text, $match);
				?>
				{{ $match[1] }} {{ $event->ordertotal }}
			</td>			
		</tr>
	</table>


	<table width="100%" border="0" class="tbl-bg">
		<tr>
			<th align="left" colspan="2" class="tbl-head">Event Details</th>
		</tr>

		<tr>
			<th align="left" class="tbl-th">Event number</th>
			<th align="left" class="tbl-th">Event name</th>
		</tr>
		<tr>
			<td align="left" class="tbl-td">{{ $event->id }}</td>
			<td align="left" class="tbl-td">{{ $event->eventname }}</td>
		</tr>
		<tr>
			<th align="left" class="tbl-th">Location</th>
			<th align="left" class="tbl-th">Event price</th>
		</tr>
		<tr>
			<td align="left" class="tbl-td">{{ $event->eventbuilding_number }}, {{ $event->eventaddress_line_1 }}, {{ $event->eventcity }}, {{ $event->eventcountry }}, {{ $event->eventpostcode }}</td>
			<td align="left" class="tbl-td">{{ $event->price }}</td>
		</tr>
		<tr>
			<th align="left" class="tbl-th">Start date</th>
			<th align="left" class="tbl-th">End date</th>
		</tr>
		<tr>
			<td align="left" class="tbl-td"><?php echo date('d-m-Y H:i:s',strtotime($event->start_date)); ?></td>
			<td align="left" class="tbl-td"><?php echo date('d-m-Y H:i:s',strtotime($event->end_date)); ?></td>
		</tr>
		<tr>
			<th align="left" class="tbl-th">Event organizer</th>
			<th align="left" class="tbl-th">Terms & Condition</th>
		</tr>
		<tr>
			<td align="left" class="tbl-td">{{ $event->organizer }}</td>
			<td align="left" class="tbl-td">{{ $event->termscondition }}</td>
		</tr>		
	</table><br>
	

	<table width="100%" border="0" class="tbl-bg">
		<tr>
			<th align="left" colspan="2" class="tbl-head">Ticket Details</th>
		</tr>

		<tr>
			<th align="left" class="tbl-th">Ticket ref number</th>			
		</tr>

		@forelse($event->ticketlist as $ticket)
		<tr>
			<td align="left" class="tbl-td">{{ $ticket->ticketrefno }}</td>			
		</tr>
		@empty
		<tr>
			<td align="left" class="tbl-td">-</td>
			<td align="left" class="tbl-td">-</td>
		</tr>
		@endforelse
		
	</table><br><br>


	<table width="100%" border="0" class="tbl-bg">
		<tr>
			<?php
				$text = $event->event_currency;
				preg_match('#\((.*?)\)#', $text, $match);
			?>
			<th colspan="2" class="tbl-head">Total Price: {{ $match[1] }} {{ $event->ordertotal }}</th>
		</tr>
	</table>

</body>
</html>