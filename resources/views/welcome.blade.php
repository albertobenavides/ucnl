@extends('layouts.app')

@section('scripts')
<script>
    $(function() {
        $('#tutorial').on('hidden.bs.collapse', function () {
            $('#tutorial-gif').removeAttr('src', '');
            $('#tutorial-button').html('Mostrar tutorial');
        });
        $('#tutorial').on('show.bs.collapse', function () {
            $('#tutorial-gif').attr('src', '/tutorial.gif');
            $('#tutorial-button').html('Ocultar tutorial');
        });
    });
</script>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Generar Actas</div>
                <div class="card-body">
                    @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        <button type="button" class="close" data-dismiss="alert">x</button>
                        {{ session('status') }}
                    </div>
                    @endif
                    @if ($errors->any())
                    @foreach ($errors->all() as $error)
                    <div class="alert alert-danger">
                        <button type="button" class="close" data-dismiss="alert">x</button>
                        {{ $error }}
                    </div>
                    @endforeach
                    @endif
                    <form action="/actas" method="post" enctype="multipart/form-data" id="actas">
                        @csrf
                        <p>Recuerda sólo seleccionar los totales (y el Examen extraordinario para Bachillerato y Licenciatura) al momento de exportar tus actas.</p>
                        <p>
                            <button id="tutorial-button" class="btn btn-sm btn-info text-white" type="button" data-toggle="collapse" data-target="#tutorial" aria-expanded="false" aria-controls="tutorial">
                                Mostrar tutorial
                            </button>
                        </p>
                        <div class="collapse border border-info rounded" id="tutorial">
                            <img id="tutorial-gif" src="" class="img-fluid">
                            <p class="mt-3">Ítems que deben estar seleccionados:</p>
                            <img src="/tutorial.png" class="img-fluid">
                        </div>
                        <div class="custom-file mt-3">
                            <input type="file" name="reporte" class="custom-file-input" lang="es" required>
                            <label class="custom-file-label">Reporte en Excel</label>
                        </div>
                        <div class="input-group mt-3">
                            <div class="input-group-prepend">
                                <label class="input-group-text" for="inputGroupSelect01">Nivel</label>
                            </div>
                            <select class="custom-select" name="nivel" form="actas" required>
                                <option selected disabled>Elige</option>
                                <optgroup label="Nivel">
                                    <option value="bac">Bachillerato</option>
                                    <option value="lic">Licenciatura</option>
                                    <option value="mae">Maestría</option>
                                </optgroup>
                                <optgroup label="Sólo Primero Licenciatura">
                                    <option value="lic1">Primero Licenciatura</option>
                                </optgroup>
                            </select>
                        </div>
                        <button class="btn btn-success mt-3 {{env('APP_ENV')}}">Generar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
