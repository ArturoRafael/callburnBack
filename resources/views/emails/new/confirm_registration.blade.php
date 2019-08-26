
@include('emails.new.header')
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>Registro actualizado exitosamente</title>
</head>
<body>
    <p>Hola {{ $users->firstname }} {{ $users->lastname }}! Se ha reportado una nueva solicitud con los siguientes datos:</p>
    
    <div>
	    <ul>    	
		    <li>Email: {{ $users->email }}</li>
		    <li>Teléfono: {{ $users->phone }}</li>
		    <li>Nombre del Negocio: {{ $users->businessname }}</li>		    
	    </ul>
		<hr/>
	    <ul>    	
		    <li>Rol: {{ $rol }}</li>
		    <li>Tipo de Negocio: {{ $business }}</li>		    		
	    </ul>

	</div>
	<div style="text-align: center;">
		<a href="{{ url('api/registration/verify/' . $confirmation) }}" style="background-color: #008CBA;
						  border: none;
						  color: white;
						  padding: 10px 35px;
						  text-align: center;
						  text-decoration: none;
						  display: inline-block;
						  font-size: 16px;						 
						  cursor: pointer;">Validar Cuenta</a>
	</div>

    <p>Este correo es unico y exclusivo.  Si de casualidad recibio este correo por equivocación, por favor elimine el mensaje.</p>
</body>
</html>
