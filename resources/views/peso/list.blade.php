@extends('layouts.app')

@section('content')


<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.7/dt-1.10.15/kt-2.2.1/datatables.min.css"/>

<div class="container">
	<div class="col-md-12">
		<div class="panel panel-default">
			
			<div class="panel-heading">
				<h3 class="panel-title">Nodos</h3>
			</div>

			<div class="panel-body">
			
				<form method="POST" action="{{ url('peso/nodos') }}" class="form-inline" id="ff">
					{!! csrf_field() !!}
					
					<label class="mr-sm-2" for="inlineForm">Servidor</label>
					
					<select id="inlineForm" name="server_id" class="form-control" onchange="document.getElementById('ff').submit()" style="width:100px" >
						<option value="0" @if( 0 == $data)selected @endif>Todos</option>
						@foreach ($servers as $srv)
							<option value="{{ ($srv->server_id) }}" @if($srv->server_id == $data)selected @endif>{{ ($srv->name) }}</option>
						@endforeach
					</select>
					
					<noscript>
						<button type="submit" class="btn btn-primary">Refresh</button>
					</noscript>
					
				</form>
			
			</div>

			<div class="panel-body">
			
				<table data-order='[[ 2, "desc" ]]' id="myTable" class="table table-striped table-bordered dataTable  no-footer" cellspacing="0" width="100%" role="grid" aria-describedby="myTable_info" style="width: 100%;">

					<thead>
						<tr role="row">
							<th>Servidor</th>
							<th>Nodo</th>
							<th>Conexiones</th>
							<th>Nodos Hijos</th>
 						</tr>
					</thead>
					
					<tbody>
						@foreach ($query as $nodo)
							<tr role="row" >
								<td>{{ ($nodo->server_name) }}</td>
								<td>{{ ($nodo->nodo_name) }}</td>
								<td>{{ ($nodo->conexiones)}}</td>
								<td>Proximamente</td>
							</tr>
						@endforeach
					</tbody>
					
					
				</table>
				
				<tfoot>
					<div class="alert alert-info" role="alert">Conexiones
						<span class="badge">{{ ($total_conexiones[0]->conexiones) }}</span>
					</div>
				</tfoot>

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
