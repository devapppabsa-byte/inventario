/**
 * Loader a pantalla completa dentro de un modal.
 *
 * Se usa en tickets y en tintas. El overlay cuelga de .modal-content, que en
 * Bootstrap 5.3.3 es position: relative, asi que cubre header, body y footer.
 *
 * El motivo del watchdog: con un overlay opaco el usuario no tiene salida si
 * la peticion se queda colgada. Ya paso con el error de SMTP, donde la peticion
 * terminaba en 500 y el modal se quedaba bloqueado. Si pasa el tiempo de espera
 * sin respuesta, se saca el overlay, se vuelven a habilitar los controles y se
 * avisa.
 */
(function (global) {
  'use strict';

  var ESPERA_MAXIMA_MS = 90000;
  var AVISO_COLGADO =
    'No pudimos confirmar el envío. Revisa tu conexión e intenta de nuevo.';

  // Un overlay por modal, cacheado en el elemento para no recrearlo en cada
  // envio.
  function construir(modal) {
    var contenido = modal.querySelector('.modal-content');

    if (!contenido) {
      return null;
    }

    var overlay = document.createElement('div');
    overlay.className = 'modal-loader d-none';
    overlay.setAttribute('role', 'status');
    overlay.setAttribute('aria-live', 'polite');

    var caja = document.createElement('div');
    caja.className = 'w-100 h-100 d-flex flex-column align-items-center justify-content-center';

    var spinner = document.createElement('div');
    spinner.className = 'spinner-border text-success';
    spinner.style.width = '4rem';
    spinner.style.height = '4rem';

    var texto = document.createElement('p');
    texto.className = 'fw-bold mt-3 mb-0';
    texto.dataset.modalLoaderTexto = '';

    caja.appendChild(spinner);
    caja.appendChild(texto);
    overlay.appendChild(caja);

    // Al final del .modal-content para que pinte encima del resto. Es absolute,
    // asi que no altera el layout ni el alto del modal.
    contenido.appendChild(overlay);

    return overlay;
  }

  function obtener(modal) {
    if (!modal) {
      return null;
    }

    if (!modal._modalLoader) {
      modal._modalLoader = construir(modal);
    }

    return modal._modalLoader;
  }

  function ponerTexto(overlay, texto) {
    var destino = overlay.querySelector('[data-modal-loader-texto]');

    if (destino) {
      destino.textContent = texto;
    }
  }

  function cancelarWatchdog(modal) {
    if (modal._modalLoaderWatchdog) {
      clearTimeout(modal._modalLoaderWatchdog);
      modal._modalLoaderWatchdog = null;
    }
  }

  function controles(modal) {
    return modal.querySelectorAll('[data-loader-bloquea]');
  }

  function quitarAviso(modal) {
    var aviso = modal.querySelector('[data-modal-loader-aviso]');

    if (aviso) {
      aviso.remove();
    }
  }

  function avisaColgado(modal) {
    if (modal.querySelector('[data-modal-loader-aviso]')) {
      return;
    }

    var aviso = document.createElement('div');
    aviso.className = 'alert alert-danger';
    aviso.dataset.modalLoaderAviso = '';
    aviso.textContent = AVISO_COLGADO;

    var destino = modal.querySelector('.modal-body') || modal.querySelector('.modal-content');

    if (destino) {
      destino.prepend(aviso);
    }
  }

  function mostrar(modal, texto) {
    var overlay = obtener(modal);

    if (!overlay) {
      return;
    }

    ponerTexto(overlay, texto);
    quitarAviso(modal);
    overlay.classList.remove('d-none');
    modal.setAttribute('aria-busy', 'true');

    controles(modal).forEach(function (control) {
      control.disabled = true;
    });

    // Cada llamada reinicia el reloj. Asi, en tickets, la espera de la compresion
    // no consume los segundos que le tocan a la red del envio real.
    cancelarWatchdog(modal);
    modal._modalLoaderWatchdog = setTimeout(function () {
      ocultar(modal);
      avisaColgado(modal);
    }, ESPERA_MAXIMA_MS);
  }

  function ocultar(modal) {
    var overlay = obtener(modal);

    cancelarWatchdog(modal);

    if (overlay) {
      overlay.classList.add('d-none');
    }

    if (modal) {
      modal.removeAttribute('aria-busy');
    }

    controles(modal).forEach(function (control) {
      control.disabled = false;
    });
  }

  global.ModalLoader = {
    mostrar: mostrar,
    ocultar: ocultar,
    ESPERA_MAXIMA_MS: ESPERA_MAXIMA_MS,
  };
})(window);
