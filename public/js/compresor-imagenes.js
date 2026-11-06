/**
 * Compresor de imágenes para los formularios de soporte.
 *
 * El despliegue vive en Vercel, que rechaza con 413 cualquier cuerpo de peticion
 * mayor a 4.5 MB antes de que PHP lo vea. Ese limite es de la plataforma y ningun
 * php.ini lo puede levantar, asi que la unica forma de aceptar fotos de celular
 * (que pesan 4 a 15 MB) es reducirlas en el navegador antes de mandarlas.
 *
 * En vez de rehacer el envio con fetch(), se reemplaza el FileList del input con
 * un DataTransfer. Asi el formulario sigue siendo un POST nativo y no hay que
 * tocar el flujo de errores, el redirect ni el resto del formulario.
 */
(function (global) {
  'use strict';

  const DEFAULTS = {
    ladoMaximo: 1600,        // px del lado mayor
    ladoMinimo: 300,         // px del lado menor; el servidor rechaza menos de 300
    calidad: 0.85,           // calidad JPEG
    umbralBytes: 400 * 1024, // por debajo de esto no se toca el archivo
  };

  /** Tipos que el navegador no puede re-codificar sin perder algo importante. */
  function noSePuedeComprimir(tipo, nombre) {
    // GIF puede ser animado; un canvas solo conservaria el primer fotograma.
    if (tipo === 'image/gif') return true;
    // Safari viejo y Windows no saben decodificar HEIC: createImageBitmap tira.
    if (tipo === 'image/heic' || tipo === 'image/heif') return true;
    if (/\.(gif|heic|heif)$/i.test(nombre || '')) return true;
    return false;
  }

  function nombreComoJpeg(nombre) {
    const base = (nombre || 'imagen').replace(/\.[^.]+$/, '');
    return base + '.jpg';
  }

  /**
   * Regresa el mismo archivo si no conviene tocarlo, o uno nuevo ya reducido.
   * Nunca devuelve un archivo mas grande que el original.
   */
  async function comprimir(archivo, opciones) {
    const cfg = Object.assign({}, DEFAULTS, opciones || {});

    if (!archivo || !archivo.type || archivo.type.indexOf('image/') !== 0) {
      return { archivo: archivo, comprimida: false, motivo: 'no-es-imagen' };
    }
    if (noSePuedeComprimir(archivo.type, archivo.name)) {
      return { archivo: archivo, comprimida: false, motivo: 'formato' };
    }
    if (typeof global.createImageBitmap !== 'function' || typeof global.DataTransfer !== 'function') {
      return { archivo: archivo, comprimida: false, motivo: 'navegador' };
    }

    let bitmap = null;
    try {
      // imageOrientation corrige la rotacion EXIF: con un <img> normal las fotos
      // de iPhone se verian acostadas.
      bitmap = await global.createImageBitmap(archivo, { imageOrientation: 'from-image' });

      const ladoMayor = Math.max(bitmap.width, bitmap.height);
      const ladoMenor = Math.min(bitmap.width, bitmap.height);
      if (ladoMayor <= cfg.ladoMaximo && archivo.size <= cfg.umbralBytes) {
        return { archivo: archivo, comprimida: false, motivo: 'ya-esta-bien' };
      }

      // Se toma la escala MAYOR de las dos: si una imagen es muy panoramica,
      // reducirla para que el lado mayor llegue a 1600 dejaría el menor por
      // debajo de 300 px, y el servidor la rechazaria por resolucion minima.
      // Antes eso que encoger el lado mayor, aunque se pase de 1600.
      const escalaPorLargo = cfg.ladoMaximo / ladoMayor;
      const escalaPorCorto = cfg.ladoMinimo / ladoMenor;
      const escala = Math.min(1, Math.max(escalaPorLargo, escalaPorCorto));
      const ancho = Math.max(1, Math.round(bitmap.width * escala));
      const alto = Math.max(1, Math.round(bitmap.height * escala));

      const canvas = document.createElement('canvas');
      canvas.width = ancho;
      canvas.height = alto;
      const ctx = canvas.getContext('2d');

      // Lienzo en blanco: si no, un PNG con transparencia sale con fondo negro
      // al pasarlo a JPEG.
      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, ancho, alto);
      ctx.drawImage(bitmap, 0, 0, ancho, alto);

      const blob = await new Promise(function (resolve) {
        canvas.toBlob(resolve, 'image/jpeg', cfg.calidad);
      });

      if (!blob) {
        return { archivo: archivo, comprimida: false, motivo: 'no-se-pudo-codificar' };
      }

      const nuevo = new File([blob], nombreComoJpeg(archivo.name), {
        type: 'image/jpeg',
        lastModified: Date.now(),
      });

      // Por seguridad, si la "compresion" empeoro el archivo se deja el original.
      if (nuevo.size >= archivo.size) {
        return { archivo: archivo, comprimida: false, motivo: 'ya-esta-bien' };
      }

      return { archivo: nuevo, comprimida: true, ancho: ancho, alto: alto };
    } catch (e) {
      return { archivo: archivo, comprimida: false, motivo: 'error' };
    } finally {
      if (bitmap && typeof bitmap.close === 'function') {
        bitmap.close();
      }
    }
  }

  /**
   * Comprime todas las imágenes del input y deja el FileList listo para enviar.
   *
   * @param  {HTMLInputElement} input
   * @param  {object} opciones
   * @return {Promise<{detalle: Array, errores: Array}>}
   */
  async function procesar(input, opciones) {
    const archivos = Array.from(input.files || []);
    if (!archivos.length) {
      return { detalle: [], errores: [] };
    }

    const transferible = new global.DataTransfer();
    const detalle = [];
    const errores = [];

    for (const archivo of archivos) {
      let resultado;
      try {
        resultado = await comprimir(archivo, opciones);
      } catch (e) {
        resultado = { archivo: archivo, comprimida: false, motivo: 'error' };
      }

      transferible.items.add(resultado.archivo);
      detalle.push({
        nombre: resultado.archivo.name,
        tamano: resultado.archivo.size,
        anterior: archivo.size,
        comprimida: resultado.comprimida,
        motivo: resultado.motivo || null,
        ancho: resultado.ancho || null,
        alto: resultado.alto || null,
        // El File ya comprimido, para que la vista lo pueda previsualizar sin
        // volver a buscarlo por nombre (que cambio al re-codificar).
        archivo: resultado.archivo,
      });
    }

    input.files = transferible.files;
    return { detalle: detalle, errores: errores };
  }

  global.CompresorImagen = {
    procesar: procesar,
    comprimir: comprimir,
    // exportado para las pruebas y para reutilizar los topes en otras vistas
    DEFAULTS: DEFAULTS,
  };
})(window);
