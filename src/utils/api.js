/**
 * Servicio API para consumir el backend PHP del Concejo de Chía
 */

// URL base de la API
export const API_BASE_URL = typeof window !== 'undefined' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1'
  ? '/api'
  : 'http://localhost/api';

/**
 * Obtener noticias activas desde la API PHP / BD del sitio web (noticoncejo)
 */
export async function getNoticias() {
  try {
    const res = await fetch(`${API_BASE_URL}/public/get_noticias.php`);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    return Array.isArray(data) && data.length > 0 ? data : getNoticiasFallback();
  } catch (err) {
    console.warn('API no disponible, cargando datos locales/caché:', err.message);
    return getNoticiasFallback();
  }
}

/**
 * Obtener eventos de agenda
 */
export async function getAgenda(mes, anio) {
  try {
    const params = new URLSearchParams();
    if (mes) params.append('mes', mes);
    if (anio) params.append('anio', anio);
    
    const res = await fetch(`${API_BASE_URL}/public/get_agenda.php?${params.toString()}`);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    return Array.isArray(data) && data.length > 0 ? data : getAgendaFallback();
  } catch (err) {
    console.warn('API no disponible, usando fallback de agenda:', err.message);
    return getAgendaFallback();
  }
}

/**
 * Obtener documentos (docpubli)
 */
export async function getDocumentos(categoria) {
  try {
    const url = categoria 
      ? `${API_BASE_URL}/public/get_documentos.php?categoria=${encodeURIComponent(categoria)}`
      : `${API_BASE_URL}/public/get_documentos.php`;
      
    const res = await fetch(url);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    return Array.isArray(data) && data.length > 0 ? data : getDocumentosFallback();
  } catch (err) {
    console.warn('API no disponible, usando fallback de documentos:', err.message);
    return getDocumentosFallback();
  }
}

/* --- FALLBACKS LOCALE / OFFLINE --- */

function getNoticiasFallback() {
  return [
    {
      id_noticia: 1,
      titulo: 'Aprobado nuevo acuerdo para el desarrollo sostenible de Chía',
      contenido: 'El Concejo Municipal de Chía aprobó por unanimidad el Acuerdo No. 15 de 2026...',
      url_imagen: null,
      fecha_pub: '2026-08-15 10:00:00',
      estado: 'activo'
    },
    {
      id_noticia: 2,
      titulo: 'Sesión Plenaria extraordinaria convocada para el 20 de agosto',
      contenido: 'Se convoca a sesión plenaria extraordinaria del Concejo Municipal...',
      url_imagen: null,
      fecha_pub: '2026-08-12 14:30:00',
      estado: 'activo'
    },
    {
      id_noticia: 3,
      titulo: 'Concejo de Chía rinde informe de gestión del primer semestre 2026',
      contenido: 'En cumplimiento de la Ley de Transparencia, el Concejo Municipal presenta su informe...',
      url_imagen: null,
      fecha_pub: '2026-08-10 09:00:00',
      estado: 'activo'
    }
  ];
}

function getAgendaFallback() {
  return [];
}

function getDocumentosFallback() {
  return [
    {
      id_doc: 1,
      nombre: 'Acuerdo No. 15 de 2026 - Plan de Desarrollo Sostenible',
      categoria: 'Acuerdo',
      url_pdf: '/documentos/acuerdo_015_2026.pdf',
      fecha_sub: '2026-08-15'
    },
    {
      id_doc: 2,
      nombre: 'Resolución No. 08 de 2026 - Participación Ciudadana',
      categoria: 'Resolución',
      url_pdf: '/documentos/resolucion_008_2026.pdf',
      fecha_sub: '2026-08-08'
    }
  ];
}
