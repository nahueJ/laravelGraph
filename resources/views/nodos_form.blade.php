@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div>
            <div class="panel panel-default">
                <div class="panel-heading">{{$title}}</div>
                <div class="panel-body">
                    <ul class="nav nav-tabs">
                       <li class="active"><a id="data_tab" data-toggle="tab" href="#data">Datos del Nodo</a></li>
                       <li id="settings_tab"><a data-toggle="tab" href="#add_aparato">Agregar Aparato</a></li>
                    </ul>
                    <div class="tab-content">
                       <div id="data" class="tab-pane fade in active">
                            @if (count($errors) > 0)
                                <div class="alert alert-danger">
                                    <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                    </ul>
                                </div>
                            @endif
                            <form method=post action="/{{$action}}">
                                {{ csrf_field() }}

                                @if (isset($a->id))
                                <input type="hidden" name="id" value="{{$a->id}}" id="id"/>
                                @endif
                                <div class="form-group">
                                    <label>Nombre</label>
                                    <input class="form-control" type=text name="nombre" value="{{ old('nombre',(isset($a->nombre) ? $a->nombre:'')) }}">
                                </div>
                                <div class="form-group">
                                    <label>Coordenadas</label>
                                    <input class="form-control" type=text name="coordenadas" value="{{ old('coordenadas',(isset($a->coordenadas) ? $a->coordenadas:'')) }}">
                                </div>
								<div class="checkbox">
									<label><input type="checkbox" name="root" @if($a->jerarquia==0) checked @endif>Root</label>
								</div>
                                <div class="form-group">
                                @if ($action=='nodo_actualizar')
                                    <button type="submit" class="btn-warning">Actualizar</button>
                                    <button id="borrar" type="button" class="btn-danger">Eliminar</button>
                                @else
                                    <button type="submit" class="btn-success">Crear</button>
                                @endif
                                </div>
                                <h3>PTPs</h3>
                                <table id="aparatos_list_ptps" class="table table-striped table-bordered dt-responsive" style="width:100% !important" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Puerto</th>
                                            <th>Plataforma</th>
                                            <th>&nbsp;</th>
                                        </tr>
                                    </thead>
                                </table>
                                <h3>Routers</h3>
                                <table id="aparatos_list_routers" class="table table-striped table-bordered dt-responsive" style="width:100% !important" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Puerto</th>
                                            <th>Plataforma</th>
                                            <th>&nbsp;</th>
                                        </tr>
                                    </thead>
                                </table>
                                <h3>Switches</h3>
                                <table id="aparatos_list_switches" class="table table-striped table-bordered dt-responsive" style="width:100% !important" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Puerto</th>
                                            <th>Plataforma</th>
                                            <th>&nbsp;</th>
                                        </tr>
                                    </thead>
                                </table>
                                <h3>APs</h3>
                                <table id="aparatos_list_aps" class="table table-striped table-bordered dt-responsive" style="width:100% !important" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Puerto</th>
                                            <th>Plataforma</th>
                                            <th>&nbsp;</th>
                                        </tr>
                                    </thead>
                                </table>

                            </div>
                            <div id="add_aparato" class="tab-pane fade">
                                <table id="aparatos_list" class="table table-striped table-bordered dt-responsive" style="width:100% !important" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Rol</th>
                                            <th>Puerto</th>
                                            <th>Plataforma</th>
                                        </tr>
                                    </thead>
                                </table>
                                <button type=button id="agregar_aparatos" class="btn-warning">Agregar Aparatos</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


@section('scripts')
<script>

$(document).ready(function() {
    $('#borrar').click(function() {
        bootbox.confirm("Est&aacute; seguro de borrar este nodo? No hay deshacer!",function(answer) {
                if(answer) {
                    $.ajax({
                        url: '/nodo/'+$('#id').val(),
                        type: "DELETE",
                        dataType: "json",
                        contentType: "application/json",
                        success: function(result) {
                            if(result.status=='ok') {
                                $.unblockUI();
                                window.location.href='/nodos';
                            }
                        },
                        error: function(e) {
                            alert("Error 003");
                        }

                 });
                }
            });
    });
    $('#aparatos_list').DataTable( {
        "order": [[ 1, "asc" ]],
        stateSave: true,
        "ajax":{
        "url": "/aparatos_unassigned_list",
        "type": "GET"
        },
        "columns": [
            {"data":"nombre"},
            {"data":"rol"},
            {"data":"puerto"},
            {"data":"plataforma"},
        ],
        "language": {
            "url":"/js/Spanish.json"
        },

        "columnDefs": [
            {
                "orderable":false,
                "targets" : 1,
                "render": function ( data, type, row ) {
                    if(row.rol==1)
                        return 'PTP';
                    else if(row.rol==2)
                        return 'Router';
                    else if(row.rol==3)
                        return 'Switch';
                    else if(row.rol==4)
                        return 'AP';                    
                }
            },   
            {
                "orderable":false,
                "targets" : 3,
                "render": function ( data, type, row ) {
                     if(row.plataforma==0)
                         return '<a href="#">Detectar</a>';
                     else {
                        if(row.plataforma==1)
                            return 'Ubiquiti';
                        else if(row.plataforma==2)
                            return 'Mikrotik';                  
                        }
                    }
            }, 
        ]
    });

    $('#aparatos_list tbody').on( 'click', 'tr', function () {
        $(this).toggleClass('active');
    });

    $('#agregar_aparatos').click(function() {
        $.each($('#aparatos_list').DataTable().rows('.active').data(),function(k,value) {
            $.ajax({
                url: '/nodo_add_aparato/'+$('#id').val()+'/'+value.id,
                type: "POST",
                dataType: "json",
                contentType: "application/json",
                success: function(result) {
                },
                error: function(e) {
                    alert("Error 003");
                }
            });
        });
        reload_datatables();
    });

    $('#aparatos_list_ptps').DataTable( {
        "order": [[ 1, "asc" ]],
        stateSave: true,
        "ajax":{
        "url": '/nodo_ptps/'+$('#id').val(),
        "type": "GET"
        },
        "columns": [
            {"data":"nombre"},
            {"data":"rol"},
            {"data":"puerto"},
            {"data":"plataforma"},
        ],
        "language": {
            "url":"/js/Spanish.json"
        },

        "columnDefs": [
            {
                "orderable":false,
                "targets" : 2,
                "render": function ( data, type, row ) {
                     if(row.plataforma==0)
                         return '<a href="#">Detectar</a>';
                     else {
                        if(row.plataforma==1)
                            return 'Ubiquiti';
                        else if(row.plataforma==2)
                            return 'Mikrotik';                  
                        }
                    }
            }, 
            {
                "orderable":false,
                "targets" : 3,
                "render": function ( data, type, row ) {
                    return '<a href=# onClick="quitar_aparato($(this).parents(\'tr\'),$(this).parents(\'table\'))"><i class="fa fa-trash-o"></i><a>';
                }
            }   

        ]
    });

    $('#aparatos_list_routers').DataTable( {
        "order": [[ 1, "asc" ]],
        stateSave: true,
        "ajax":{
        "url": '/nodo_routers/'+$('#id').val(),
        "type": "GET"
        },
        "columns": [
            {"data":"nombre"},
            {"data":"rol"},
            {"data":"puerto"},
            {"data":"plataforma"},
        ],
        "language": {
            "url":"/js/Spanish.json"
        },

        "columnDefs": [
            {
                "orderable":false,
                "targets" : 2,
                "render": function ( data, type, row ) {
                     if(row.plataforma==0)
                         return '<a href="#">Detectar</a>';
                     else {
                        if(row.plataforma==1)
                            return 'Ubiquiti';
                        else if(row.plataforma==2)
                            return 'Mikrotik';                  
                        }
                    }
            }, 
            {
                "orderable":false,
                "targets" : 3,
                "render": function ( data, type, row ) {
                    return '<a href=# onClick="quitar_aparato($(this).parents(\'tr\'),$(this).parents(\'table\'))"><i class="fa fa-trash-o"></i><a>';
                }
            }   

        ]
    });

    $('#aparatos_list_switches').DataTable( {
        "order": [[ 1, "asc" ]],
        stateSave: true,
        "ajax":{
        "url": '/nodo_switches/'+$('#id').val(),
        "type": "GET"
        },
        "columns": [
            {"data":"nombre"},
            {"data":"rol"},
            {"data":"puerto"},
            {"data":"plataforma"},
        ],
        "language": {
            "url":"/js/Spanish.json"
        },

        "columnDefs": [
            {
                "orderable":false,
                "targets" : 2,
                "render": function ( data, type, row ) {
                     if(row.plataforma==0)
                         return '<a href="#">Detectar</a>';
                     else {
                        if(row.plataforma==1)
                            return 'Ubiquiti';
                        else if(row.plataforma==2)
                            return 'Mikrotik';                  
                        }
                    }
            }, 
            {
                "orderable":false,
                "targets" : 3,
                "render": function ( data, type, row ) {
                    return '<a href=# onClick="quitar_aparato($(this).parents(\'tr\'),$(this).parents(\'table\'))"><i class="fa fa-trash-o"></i><a>';
                }
            }   

        ]
    });

    $('#aparatos_list_aps').DataTable( {
        "order": [[ 1, "asc" ]],
        stateSave: true,
        "ajax":{
        "url": '/nodo_aps/'+$('#id').val(),
        "type": "GET"
        },
        "columns": [
            {"data":"nombre"},
            {"data":"rol"},
            {"data":"puerto"},
            {"data":"plataforma"},
        ],
        "language": {
            "url":"/js/Spanish.json"
        },

        "columnDefs": [
            {
                "orderable":false,
                "targets" : 2,
                "render": function ( data, type, row ) {
                     if(row.plataforma==0)
                         return '<a href="#">Detectar</a>';
                     else {
                        if(row.plataforma==1)
                            return 'Ubiquiti';
                        else if(row.plataforma==2)
                            return 'Mikrotik';                  
                        }
                    }
            }, 
            {
                "orderable":false,
                "targets" : 3,
                "render": function ( data, type, row ) {
                    return '<a href=# onClick="quitar_aparato($(this).parents(\'tr\'),$(this).parents(\'table\'))"><i class="fa fa-trash-o"></i><a>';
                }
            }   

        ]
    });


});

function reload_datatables() {
    //manda reload al ajax de todas las DT, tb cuando borras un aparato
    $('#aparatos_list_ptps').DataTable().ajax.reload();
    $('#aparatos_list_routers').DataTable().ajax.reload();
    $('#aparatos_list_switches').DataTable().ajax.reload();
    $('#aparatos_list_aps').DataTable().ajax.reload();
    $('#aparatos_list').DataTable().ajax.reload();

}

function quitar_aparato(dom_row,table) {
   var dt_row=$(table).DataTable().row(dom_row);
   var data = dt_row.data();
    bootbox.confirm("Est&aacute; seguro de quitar este aparato del nodo? No hay deshacer!",function(answer) {
            if(answer) {
                $.ajax({
                    url: '/nodo_del_aparato/'+data.id,
                    type: "DELETE",
                    dataType: "json",
                    contentType: "application/json",
                    success: function(result) {
                        if(result.status=='ok') {
                            reload_datatables();                            
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


