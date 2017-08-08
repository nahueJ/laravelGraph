@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div>
            <div class="panel panel-default">
                <div class="panel-heading">Nodos</div>
                <div class="panel-body">
                    <div class="actions">
                        <a href="/nodo_nuevo"><button type="button" class="btn btn-success">Nuevo Nodo</button></a>
                    </div>

                    <table id="record_list" class="table table-striped table-bordered dt-responsive" style="width:100% !important" cellspacing="0">
                        <thead>
                            <tr>
                                <th>&nbsp;</th>
                                <th>Nombre</th>
                                <th>Coordenadas</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


@section('scripts')
<script>

var nuevo=false;

$(document).ready(function() {

    $('#record_list').DataTable( {
        "order": [[ 1, "asc" ]],
        stateSave: true,
        "ajax":{
        "url": "/nodos_list",
        "type": "GET"
        },
        "columns": [
            {"data":"id"},
            {"data":"nombre"},
            {"data":"coordenadas"},
        ],
        "language": {
            "url":"/js/Spanish.json"
        },

        "columnDefs": [
            {
                "orderable":false,
                "targets" : 0,
                "render": function ( data, type, row ) {
                    return '<a href="/nodo/'+row.id+'"><i class="fa fa-pencil-square-o"></i><a>';
                }
            },  
            {
                "orderable":false,
                "targets" : 3,
                "render": function ( data, type, row ) {
                    return '<a href=# onClick="delete_record($(this).parents(\'tr\'))"><i class="fa fa-trash-o"></i><a>';
                }
            }   

        ]
    });
});

function delete_record(dom_row) {
   var dt_row=$('#record_list').DataTable().row(dom_row);
   var data = dt_row.data();
    bootbox.confirm("Est&aacute; seguro de borrar este nodo? No hay deshacer!",function(answer) {
            if(answer) {
                $.ajax({
                    url: '/nodo/'+data.id,
                    type: "DELETE",
                    dataType: "json",
                    contentType: "application/json",
                    success: function(result) {
                        if(result.status=='ok') {
                            $('#record_list').DataTable().ajax.reload();
                        }
                    },
                    error: function(e) {
                        alert("Error 003");
                    }

             });
            }
        });
}
</script>
@endsection


