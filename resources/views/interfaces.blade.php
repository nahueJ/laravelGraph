@extends('layouts.app')

@section('content')


<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.7/dt-1.10.15/kt-2.2.1/datatables.min.css"/>

<div class="container">
	<div class="col-md-12">
		<div class="panel panel-default">
			
			<div class="panel-heading">
				<h3 class="panel-title">Interfaces</h3>
			</div>
			
			<div class="panel-body">
			
				<table data-order='[[ 0, "desc" ]]' id="myTable" class="table table-striped table-bordered dataTable  no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="myTable_info" style="width: 100%;">

					<thead>
						<tr role="row">
							<th>aparato</th>
							<th>itf_id</th>
							<th>interface_type</th>
							<th>IP</th>
							<th>Subred</th>
							<th>Est.Con.</th>
							<th>id Itf Par</th>
 						</tr>
					</thead>
					
					<tbody>
						@foreach ($interfaces as $if)
							<tr role="row" >
								<td>{{ ( $if->aparato()->first()->nombre ) }}</td>
								<td>{{ ($if->id)}}</td>
								<td>{{ ($if->interface_type) }}</td>
								<td>{{ ($if->ip)}}</td>
								<td>{{ ($if->subred)}}</td>
								<td>{{ ($if->estado)}}</td>
								<td>{{ ($if->interface_pair_id)}}</td>
							</tr>
						@endforeach
					</tbody>
					
					
				</table>

			</div>
			
		</div>
	</div>
</div>


 
<script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.7/dt-1.10.15/kt-2.2.1/datatables.min.js"></script>
<script>
	$(document).ready(function(){
		$('#myTable').DataTable();
	});
</script> 


@endsection
