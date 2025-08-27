// =====================================================
// FRONTEND GENÉRICO - CATÁLOGO DE DESCARGAS
// =====================================================

class CatalogoDescargas {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.baseUrl = '/catalogo'; // Ajustar según tu configuración
        this.init();
    }

    async init() {
        await this.cargarCatalogo();
        this.setupEventListeners();
    }

    async cargarCatalogo() {
        try {
            this.mostrarLoader(true);
            
            const response = await fetch(`${this.baseUrl}/listar`);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Error cargando catálogo');
            }
            
            this.renderCatalogo(data);
            
        } catch (error) {
            this.mostrarError('Error cargando el catálogo: ' + error.message);
        } finally {
            this.mostrarLoader(false);
        }
    }

    renderCatalogo(recursos) {
        if (!recursos || recursos.length === 0) {
            this.container.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    No hay recursos disponibles en este momento.
                </div>
            `;
            return;
        }

        const html = `
            <div class="row">
                ${recursos.map(recurso => this.renderRecursoCard(recurso)).join('')}
            </div>
        `;
        
        this.container.innerHTML = html;
    }

    renderRecursoCard(recurso) {
        const fechaFormateada = this.formatearFecha(recurso.fecha_creacion);
        const botones = recurso.acciones
            .sort((a, b) => a.orden - b.orden)
            .map(accion => this.renderBotonAccion(recurso.id, accion))
            .join('');

        return `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-database me-2"></i>
                            ${recurso.titulo}
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <p class="card-text text-muted mb-2">
                            <i class="fas fa-calendar me-1"></i>
                            ${fechaFormateada}
                        </p>
                        <p class="card-text flex-grow-1">
                            ${recurso.descripcion}
                        </p>
                        <div class="d-flex flex-wrap gap-2 mt-auto">
                            ${botones}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    renderBotonAccion(recursoId, accion) {
        const iconos = {
            'csv': 'fas fa-file-csv',
            'xlsx': 'fas fa-file-excel',
            'geojson': 'fas fa-map-marked-alt',
            'pdf': 'fas fa-file-pdf',
            'docx': 'fas fa-file-word',
            'preview': 'fas fa-eye'
        };

        const colores = {
            'csv': 'btn-success',
            'xlsx': 'btn-primary',
            'geojson': 'btn-warning',
            'pdf': 'btn-danger',
            'docx': 'btn-info',
            'preview': 'btn-outline-secondary'
        };

        const icono = iconos[accion.formato] || 'fas fa-download';
        const color = colores[accion.formato] || 'btn-secondary';

        return `
            <button class="btn ${color} btn-sm" 
                    onclick="catalogoApp.handleAction(${recursoId}, '${accion.accion}', '${accion.formato}')"
                    data-id="${recursoId}" 
                    data-accion="${accion.accion}" 
                    data-formato="${accion.formato}">
                <i class="${icono} me-1"></i>
                ${accion.formato.toUpperCase()}
            </button>
        `;
    }

    // =====================================================
    // FUNCIÓN PRINCIPAL DE ACCIONES
    // =====================================================
    async handleAction(id, accion, formato) {
        try {
            // Mostrar indicador de carga en el botón
            const btn = document.querySelector(`button[data-id="${id}"][data-formato="${formato}"]`);
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Procesando...';

            if (accion === 'ver' && formato === 'preview') {
                await this.mostrarPreview(id);
            } else if (accion === 'descargar') {
                await this.descargarArchivo(id, accion, formato);
            }

        } catch (error) {
            this.mostrarError(`Error en ${accion}: ${error.message}`);
        } finally {
            // Restaurar botón
            const btn = document.querySelector(`button[data-id="${id}"][data-formato="${formato}"]`);
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    }

    async descargarArchivo(id, accion, formato) {
        const url = `${this.baseUrl}/accion?id=${id}&accion=${accion}&formato=${formato}`;
        
        try {
            const response = await fetch(url);
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Error en la descarga');
            }

            // Obtener el nombre del archivo del header Content-Disposition
            const contentDisposition = response.headers.get('content-disposition');
            let filename = `descarga_${formato}_${Date.now()}.${formato}`;
            
            if (contentDisposition) {
                const filenameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                if (filenameMatch && filenameMatch[1]) {
                    filename = filenameMatch[1].replace(/['"]/g, '');
                }
            }

            // Crear blob y descargar
            const blob = await response.blob();
            const downloadUrl = window.URL.createObjectURL(blob);
            
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(downloadUrl);

            this.mostrarExito(`Archivo ${filename} descargado exitosamente`);

        } catch (error) {
            throw error;
        }
    }

    async mostrarPreview(id) {
        try {
            const response = await fetch(`${this.baseUrl}/accion?id=${id}&accion=ver&formato=preview`);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'Error obteniendo preview');
            }

            this.renderPreviewModal(data);
            
        } catch (error) {
            throw error;
        }
    }

    renderPreviewModal(data) {
        // Crear modal dinámicamente
        const modalHtml = `
            <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-eye me-2"></i>
                                Vista Previa (primeras 50 filas)
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="table-responsive">
                                ${this.renderPreviewTable(data)}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Eliminar modal anterior si existe
        const existingModal = document.getElementById('previewModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Agregar nuevo modal
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        modal.show();
    }

    renderPreviewTable(data) {
        if (!data.columnas || !data.filas || data.filas.length === 0) {
            return '<div class="alert alert-info">No hay datos para mostrar</div>';
        }

        const headers = data.columnas.map(col => `<th>${col}</th>`).join('');
        const rows = data.filas.map(row => {
            const cells = data.columnas.map(col => `<td>${row[col] || ''}</td>`).join('');
            return `<tr>${cells}</tr>`;
        }).join('');

        return `
            <table class="table table-striped table-hover table-sm">
                <thead class="table-dark">
                    <tr>${headers}</tr>
                </thead>
                <tbody>
                    ${rows}
                </tbody>
            </table>
        `;
    }

    // =====================================================
    // UTILIDADES DE UI
    // =====================================================
    
    mostrarLoader(show) {
        if (show) {
            this.container.innerHTML = `
                <div class="d-flex justify-content-center align-items-center" style="min-height: 200px;">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2">Cargando catálogo...</p>
                    </div>
                </div>
            `;
        }
    }

    mostrarError(mensaje) {
        this.mostrarToast(mensaje, 'error');
    }

    mostrarExito(mensaje) {
        this.mostrarToast(mensaje, 'success');
    }

    mostrarToast(mensaje, tipo = 'info') {
        // Crear toast container si no existe
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }

        const toastId = 'toast-' + Date.now();
        const bgClass = tipo === 'error' ? 'bg-danger' : tipo === 'success' ? 'bg-success' : 'bg-info';
        const iconClass = tipo === 'error' ? 'fas fa-exclamation-circle' : 
                         tipo === 'success' ? 'fas fa-check-circle' : 'fas fa-info-circle';

        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass}" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="${iconClass} me-2"></i>
                        ${mensaje}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                            data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 5000 });
        toast.show();

        // Limpiar después de ocultar
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }

    formatearFecha(fechaString) {
        const fecha = new Date(fechaString);
        return fecha.toLocaleDateString('es-AR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    setupEventListeners() {
        // Botón de refresh
        const refreshBtn = document.getElementById('refresh-catalogo');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => this.cargarCatalogo());
        }
    }

    // Método público para refrescar desde fuera
    refresh() {
        this.cargarCatalogo();
    }
}

// =====================================================
// HTML BÁSICO PARA EL MÓDULO
// =====================================================

const HTML_TEMPLATE = `
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Descargas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .card { transition: transform 0.2s; }
        .card:hover { transform: translateY(-2px); }
        .btn-group-vertical .btn { margin-bottom: 5px; }
        .toast-container { z-index: 9999; }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-download me-2"></i>Catálogo de Descargas</h1>
            <button id="refresh-catalogo" class="btn btn-outline-primary">
                <i class="fas fa-sync-alt me-1"></i>Actualizar
            </button>
        </div>
        
        <div id="catalogo-container">
            <!-- El contenido se carga dinámicamente aquí -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inicializar la aplicación
        let catalogoApp;
        document.addEventListener('DOMContentLoaded', function() {
            catalogoApp = new CatalogoDescargas('catalogo-container');
        });
    </script>
</body>
</html>
`;

// =====================================================
// INICIALIZACIÓN (para usar en proyecto existente)
// =====================================================

// Usar así en tu página:
// const catalogoApp = new CatalogoDescargas('mi-container-id');