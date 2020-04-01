@component('mail::message')
# Hola,

Has sido invitado a unirte al equipo.
**{{ $invitation->team->name }}**.
Debido a que no está registrado en la plataforma, por favor
[Registrate gratis]({{ $url }}), entonces puede aceptar o rechazar la invitación en su consola de administración.

@component('mail::button', ['url' => $url])
Registrate gratis
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
