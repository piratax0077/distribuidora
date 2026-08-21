@extends('plantillas.app')

@section('contenido')
    <h1>Importar Precios de Compra desde Excel</h1>

    <form action="{{ url('/factuprodu/import-excel-precios-compra') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="excel_file">Seleccionar archivo Excel:</label>
            <input type="file" name="excel_file" id="excel_file" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Importar</button>
    </form>
@endsection
@section('titulo','Importar Precios de Compra desde Excel')
