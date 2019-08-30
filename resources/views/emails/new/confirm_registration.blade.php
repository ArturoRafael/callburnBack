
@include('emails.new.header')

<table class="row" style="border-collapse: collapse;
                         border-spacing: 0;
                         display: table;
                         margin-left: -15px;
                         margin-right: -15px;
                         padding: 0;
                         position: relative;
                         text-align: left;
                         vertical-align: top;
                         width: 100%;">
    <tbody>
        <tr style="padding: 0; 
                   text-align: left;
                   vertical-align: top;">
            <div class="logo" style="margin-top: 20px;">
                <img src="{{config('email_templates.website_url')}}{{config('email_templates.path_to_email_images_root')}}call-burn-l-o-g-o@3x.png" class="img-responsive center-img" style="-ms-interpolation-mode: bicubic; 
                                   clear: both; display: block;
                                   margin: 20px auto;
                                   max-width: 100%; 
                                   outline: none; 
                                   text-decoration: none; 
                                   width: 30%;"
                                   alt="logo">
            </div>
            <div class="message-container" style="background-color: #F5FCFF;
                                                  padding: 10px;
                                                  padding-bottom: 30px; 
                                                  padding-top: 20px;">
                <h1 style="margin: 0;
                           margin-bottom: 10px; 
                           color: #777777; 
                           font-family: 'Montserrat', sans-serif; 
                           font-size: 20px; 
                           font-weight: normal; 
                           line-height: 1.3; 
                           margin: 0; 
                           margin-bottom: 10px; 
                           padding: 0; 
                           text-align: center; 
                           word-wrap: normal;">
                    We are happy to have got a new Callburn
                </h1>
                <h1 style="margin: 0; 
                           margin-bottom: 10px;
                           color: #777777;
                           font-family: 'Montserrat', sans-serif;
                           font-size: 20px;
                           font-weight: normal;
                           line-height: 1.3;
                           margin: 0;
                           margin-bottom: 10px;
                           padding: 0;
                           text-align: center;
                           word-wrap: normal;">
                    Start now to replace sms with <span class="bold" style="font-weight: bold;">Voice messages</span>
                </h1>
                <img src="{{config('email_templates.website_url')}}{{config('email_templates.path_to_email_images_root')}}callburn-icon-copy.png" class="img-responsive center-img mt-20" style="-ms-interpolation-mode: bicubic; 
                                        clear: both; 
                                        display: block; 
                                        margin: 0 auto; 
                                        margin-top: 20px; 
                                        max-width: 100%; 
                                        outline: none; 
                                        text-decoration: none; 
                                        width: 20%;"
                                        alt="callburn-icon">
                <span class="message caller" style="clear: both;
                                                    color: #777777;
                                                    display: block;
                                                    font-family: 'Montserrat', sans-serif;
                                                    font-size: 16px;
                                                    margin-top: 40px;
                                                    text-align: center;">
                    To activate your account we need to know.<br> Please click on the button to confirm that.
                </span>
                <a href="{{url('api/registration/verify/' . $confirmation) }}" class="btn btn-success full-width" 
                        style="-moz-user-select: none; 
                               -ms-user-select: none;
                               -webkit-user-select: none;
                               margin: 0;
                               background-color: #22cd78;
                               background-image: none;
                               border: 1px solid transparent;
                               border-color: #22cd78;
                               border-radius: 4px;
                               color: #fff;
                               cursor: pointer;
                               display: block;
                               font-family: Helvetica, Arial, sans-serif;
                               font-size: 14px;
                               font-weight: bold;
                               line-height: 1.42857;
                               margin: 20px auto;
                               margin-bottom: 0;
                               padding: 6px 12px;
                               text-align: center;
                               text-decoration: none;
                               touch-action: manipulation;
                               user-select: none;
                               vertical-align: middle;
                               white-space: nowrap;
                               width: 60%;">Activate my account</a>
            </div>
        </tr>
    </tbody>
</table>


{{-- 
<!-- <!doctype html>
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
</html> -->
--}}