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
                            <input class="form-control" type=text name="nombre" value="{{ old('nombre',(isset($a->name) ? $a->name:'')) }}">
                        </div>
                        <div class="form-group">
                            <label>Rol</label>
                            <select class="form-control"name="rol">
                            <option @if (old('rol',(isset($a->rol) ? $a->rol:''))==1) selected @endif value="1">Super Admin</option>
                            <option @if (old('rol',(isset($a->rol) ? $a->rol:''))==2) selected @endif value="2">Support</option>
                            <option @if (old('rol',(isset($a->rol) ? $a->rol:''))==3) selected @endif value="3">Commercial</option>
                            </select>
                            </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input class="form-control" type=text name="email" value="{{ old('email',(isset($a->email) ? $a->email:'')) }}">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input class="form-control" type=password name="password" value="">
                        </div>
                        <div class="form-group">
                            <label>Confirmar Password</label>
                            <input class="form-control" type=password name="password_confirmation" value="">
                        </div>
                        <div class="form-group">
                        @if ($action=='user_actualizar')
                            <button type="submit" class="btn-warning">Actualizar</button>
                            <button id="borrar" type="button" class="btn-danger">Eliminar</button>
                        @else
                            <button type="submit" class="btn-success">Crear</button>
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

$(document).ready(function() {
    $('#borrar').click(function() {
        bootbox.confirm("Est&aacute; seguro de borrar este usuario? No hay deshacer!",function(answer) {
                if(answer) {
                    $.ajax({
                        url: '/user/'+$('#id').val(),
                        type: "DELETE",
                        dataType: "json",
                        contentType: "application/json",
                        success: function(result) {
                            if(result.status=='ok') {
                                $.unblockUI();
                                window.location.href='/users';
                            }
                        },
                        error: function(e) {
                            alert("Error 003");
                        }

                 });
                }
            });
    });
});
</script>
@endsection


