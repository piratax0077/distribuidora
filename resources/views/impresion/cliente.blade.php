<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Información del cliente</title>
    <style>
        *{
            margin:0px;
            padding: 0px;
        }
        .contenedor{
            text-align: center;
            width: 90%;
            margin: 0px auto;

        }

        #datos{
            width: 90%;
            margin: 10px auto;
            font-size: 9px;
        }

        strong{
            font-size: 35px;
        }

        .title{
            border-bottom: 1px solid black;
            font-weight: bold;
            font-style: italic;
        }

        .logo_{
            width: 20px;
        }

        table{
            width: 100%;
            border-collapse: collapse;
            font-family: Arial;
            font-size: 9px;
        }
    </style>
</head>
<body>
    @php
        $fechaActual = date('d-m-Y');
    @endphp
    <table>
        <tbody>
            <tr>
                <td style="text-align: center;"><span style="font-weight: bold;">{{Session::get('PARAM_RAZ_SOC')}}</span><br><span>{{Session::get('PARAM_GIRO')}}</span></td>
            </tr>
        </tbody>
    </table>
    <div id="datos">
        <h5>Razón social: {{$cliente->empresa}}</h5>
        <h5>Rut: {{$cliente->rut}}</h5>
        <h5>Dirección: {{ $cliente->direccion }}</h5>
        <h5>Ciudad: {{ $cliente->ciudad }}</h5>
        <h5>Factura: </h5>
        <h5>Bultos: </h5>
        <h5>Transporte:</h5>
    </div>


</body>
</html>
