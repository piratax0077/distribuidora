@if($formas->count()>0)

    <div class="row" >
        <div class="col-sm-12">
            <table border="0" class="table table-sm letra-chica">
                <thead>
                    <th width="50%" scope="col"><center>Forma de Pago</center></th>
                </thead>
                <tbody>
            @foreach ($formas as $forma)
                <tr>
                    <td>
                        <input type="checkbox" name="forma_pago" id="formita-{{$forma->id}}" value="{{$forma->id}}"
                            {{ in_array($forma->id, $formas_cliente_ids ?? []) ? 'checked' : '' }}>
                        {{$forma->formapago}}
                    </td>
                </tr>
            @endforeach

            </tbody>
        </table>

        </div>

    </div>

@else
    <div class="row">
    <div class="alert alert-info">
        <h4><center>No hay Formas de Pago en la BD...</center></h4>
    </div>
    </div>
@endif
