@component('mail::message')
# Hola,

Has sido invitado a unirte al equipo.
**{{ $invitation->team->name }}**.
Debido a que ya está registrado en la plataforma, simplemente debe aceptar  o rechazar la invitación en su
[Consola de gestión de equipo]({{ $url }}).

@component('mail::button', ['url' => $url])
Ir al panel de control
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
