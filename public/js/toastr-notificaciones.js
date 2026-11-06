/* Muestra con toastr las notificaciones de session que emite
   App\NotificacionesSession desde el layout.
   El tema negro vive en public/css/estilos.css con la clase .toast-negro,
   y los iconos son los ::before de cada tipo, tambien definidos ahi. */
(function () {
  'use strict';

  var origen = document.getElementById('notificaciones-session');

  if (!origen) {
    return;
  }

  var notificaciones;

  try {
    notificaciones = JSON.parse(origen.textContent);
  } catch (e) {
    return;
  }

  if (!Array.isArray(notificaciones) || notificaciones.length === 0) {
    return;
  }

  /* Si toastr no llego a cargar no se toca nada: los mensajes siguen visibles
     en su bloque en linea original de cada vista. */
  if (typeof toastr === 'undefined') {
    return;
  }

  toastr.options = {
    closeButton: true,
    progressBar: true,
    timeOut: 7000,
    extendedTimeOut: 4000,
    newestOnTop: true,
    preventDuplicates: false,
    positionClass: 'toast-top-right',
    toastClass: 'toast-negro'
  };

  notificaciones.forEach(function (notificacion) {
    var tipo = notificacion && notificacion.tipo;
    var texto = notificacion && notificacion.texto;

    if (typeof texto !== 'string' || texto === '') {
      return;
    }

    /* Un tipo desconocido cae en info en vez de romper. */
    if (['success', 'error', 'warning', 'info'].indexOf(tipo) === -1) {
      tipo = 'info';
    }

    toastr[tipo](texto);
  });
})();
