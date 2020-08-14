@extends('layouts.app')

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
                        <p>Recuerda sólo seleccionar los totales al momento de exportar tus actas.</p>
                        <img src="tutorial.png" class="img-fluid">
                        <div class="custom-file mt-3">
                            <input type="file" name="reporte" class="custom-file-input" lang="es" required>
                            <label class="custom-file-label">Reporte en Excel o CSV</label>
                        </div>

                        <div class="input-group mt-3">
                            <div class="input-group-prepend">
                              <label class="input-group-text" for="inputGroupSelect01">Nivel</label>
                            </div>
                            <select class="custom-select" name="nivel" form="actas" required>
                              <option selected disabled>Elige</option>
                              <option value="bac">Bachillerato</option>
                              <option value="lic">Licenciatura</option>
                              <option value="mae">Maestría</option>
                            </select>
                          </div>
                        
                        <button class="btn btn-success mt-3">Generar</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
