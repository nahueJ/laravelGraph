@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div>
            <div class="panel panel-default">
                <div class="panel-heading">{{$title}}</div>
                <div class="panel-body">
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
                            <label>Rol</label>
                            <select class="form-control"name="rol">
                            <option @if (old('rol',(isset($a->rol) ? $a->rol:''))==0) selected @endif value="0">---</option>
                            <option @if (old('rol',(isset($a->rol) ? $a->rol:''))==1) selected @endif value="1">PTP</option>
                            <option @if (old('rol',(isset($a->rol) ? $a->rol:''))==2) selected @endif value="2">Router</option>
                            <option @if (old('rol',(isset($a->rol) ? $a->rol:''))==3) selected @endif value="3">Switch</option>
                            <option @if (old('rol',(isset($a->rol) ? $a->rol:''))==4) selected @endif value="4">AP</option>
                            </select>
                            </div>
                        <div class="form-group" id="host_div">
                            <label>IP</label>
                            <input class="form-control" type=text name="ip" id="host" value="{{ old('ip',(isset($a->ip) ? $a->ip:'')) }}">
                            <span class="help-block" id="host_help"></span>
                        </div>
                        <div class="form-group" id="port_div">
                            <label>Puerto</label>
                            <input class="form-control" type=text name="puerto" id="port" value="{{ old('puerto',(isset($a->puerto) ? $a->puerto:'')) }}">
                            <span class="help-block" id="port_help"></span>
                        </div>
                        <div class="form-group">
                            <label>Usuario</label>
                            <input class="form-control" type=text name="usuario" value="{{ old('usuario',(isset($a->usuario) ? $a->usuario:'')) }}">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input class="form-control" type=text name="password" value="{{ old('password',(isset($a->password) ? $a->password:'')) }}">
                        </div>
                        <div class="form-group">
                            <label>Plataforma</label>
                            <select class="form-control"name="plataforma" id="plataforma">
                            <option @if (old('plataforma',(isset($a->plataforma) ? $a->plataforma:''))==0) selected @endif value="0">---</option>
                            <option  @if (old('plataforma',(isset($a->plataforma) ? $a->plataforma:''))==1) selected @endif value="1">Ubiquiti</option>
                            <option  @if (old('plataforma',(isset($a->plataforma) ? $a->plataforma:''))==2) selected @endif value="2">Mikrotik</option>
                            </select>
                        </div>
                        <div class="form-group" id="ubiquiti_only">
                            <label>Tecnolog&iacute;a</label>
                            <select class="form-control"name="ubiquiti_subtype">
                            <option @if (old('ubiquiti_subtype',(isset($a->ubiquiti_subtype) ? $a->ubiquiti_subtype:''))==0) selected @endif value="0">Airmax</option>
                            <option  @if (old('ubiquiti_subtype',(isset($a->ubiquiti_subtype) ? $a->ubiquiti_subtype:''))==1) selected @endif value="1">AC</option>
                            </select>
                        </div>

                        <div class="form-group">
                        @if ($action=='aparato_actualizar')
                            <button type="submit" class="btn-warning">Actualizar</button>
                            <button id="borrar" type="button" class="btn-danger">Eliminar</button>
                        @else
                            <button id="create_button" type="submit" class="btn-success">Crear</button>
                        @endif
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

function boton_dale() {
    $('#create_button').removeClass();
    $('#create_button').addClass('btn-success');
    $('#create_button').html('Crear');
}

function boton_cuidado() {
    $('#create_button').removeClass();
    $('#create_button').addClass('btn-danger');
    $('#create_button').html('Crear de todas formas');

}
$(document).ready(function() {

    $('#host').change(function() {
        $('#port_div').removeClass('has-success');
        $('#port_div').removeClass('has-error');
        $('#port_help').html('');
        //if not valid IP do nothing
        if(!/^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test($(this).val()))
            return;

        $.post('/ping',JSON.stringify({"host":$(this).val()}),function(result) {
            if(result.status=='ok') {
                $('#host_div').addClass('has-success');
                $('#host_help').html("Responde PING OK");
                boton_dale();
            } else {
                $('#host_div').addClass('has-error');
                $('#host_help').html("No responde PING");
                boton_cuidado();
            }
        });
    });

    $('#port').change(function() {
        if(!$.isNumeric($(this).val()))
            return;

        $.post('/portopen',JSON.stringify({"host":$('#host').val(),"port":$(this).val()}),function(result) {
            if(result.status=='ok') {
                $('#port_div').addClass('has-success');
                $('#port_help').html("Se pudo conectar OK");
                boton_dale();
            } else {
                $('#port_div').addClass('has-error');
                $('#port_help').html("No se pudo conectar");
                boton_cuidado();
            }
        });
 
    });

    $('#borrar').click(function() {
        bootbox.confirm("Est&aacute; seguro de borrar este aparato? No hay deshacer!",function(answer) {
                if(answer) {
                    $.ajax({
                        url: '/aparato/'+$('#id').val(),
                        type: "DELETE",
                        dataType: "json",
                        contentType: "application/json",
                        success: function(result) {
                            if(result.status=='ok') {
                                $.unblockUI();
                                window.location.href='/aparatos';
                            }
                        },
                        error: function(e) {
                            alert("Error 003");
                        }

                 });
                }
            });
    });

    @if(isset($a->plataforma))
    if("{{$a->plataforma}}"==1)
        $('#ubiquiti_only').show();
    else
        $('#ubiquiti_only').hide();
    @else
        $('#ubiquiti_only').hide();
    @endif

    $('#plataforma').change(function(){
        if($(this).val()==1)
            $('#ubiquiti_only').show();
        else
            $('#ubiquiti_only').hide();
    });
});
</script>
@endsection


