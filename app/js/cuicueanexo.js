$(document).ready(function () {
    // Botón Listado
    $('#btnListado').click(function () {
        $.get('../ajax/get_cui_cueanexo.php', function (data) {
            $('#resultadoListado').html(data);
        }).fail(function () {
            $('#resultadoListado').html('<div class="alert alert-danger">Error al cargar los datos.</div>');
        });
    });
});
$(document).ready(function () {
    // Botón inconsistencias EDDY
    $('#btnInconsistencias').click(function () {
        cargarPagina();
    });
    // Cargar una página específica
    window.cargarPagina = function () {
        $.get('../ajax/get_inconsistencias.php', function (data) {
            $('#resultadoInconsistencias').html(data);
        });
    };
});
$(document).on('click', '.btnEditar', function () {
    var btn = $(this);
    var row = btn.closest('tr');
    var idrel = btn.data('idrel');
    var currentCui = btn.data('cui');
    var currentCueanexo = btn.data('cueanexo');
    // Celdas
    var $cuiCell = row.find('td').eq(1);
    var $cueanexoCell = row.find('td').eq(2);
    var $accionesCell = row.find('td').eq(3);
    // Obtener lista de CUI desde el servidor
    $.getJSON('../ajax/get_cuis_disponibles.php', function (data) {
        let select = $('<select class="form-select form-select-sm"></select>');
        data.forEach(function (item) {
            let selected = (item.cui === currentCui) ? 'selected' : '';
            select.append(`<option value="${item.cui}" ${selected}>${item.cui}</option>`);
        });
        // Reemplazar contenido por los inputs
        $cuiCell.html(select);
        $cueanexoCell.html(`<input type="text" class="form-control form-control-sm" value="${currentCueanexo || ''}">`);
        // Botón Guardar
        $accionesCell.html(`
            <button class="btn btn-sm btn-success btnGuardar" 
                    data-idrel="${idrel}">
                Guardar
            </button>
            <button class="btn btn-sm btn-secondary btnCancelarEdicion" 
                    data-idrel="${idrel}" 
                    data-cui="${currentCui}" 
                    data-cueanexo="${currentCueanexo || ''}">
                Cancelar
            </button>
        `);
    });
});
$(document).on('click', '.btnCancelarEdicion', function () {
    var btn = $(this);
    var row = btn.closest('tr');
    var idrel = btn.data('idrel');
    var cui = btn.data('cui');
    var cueanexo = btn.data('cueanexo');
    // Celdas
    var $idCell = row.find('td').eq(0);
    var $cuiCell = row.find('td').eq(1);
    var $cueanexoCell = row.find('td').eq(2);
    var $accionesCell = row.find('td').eq(3);
    // Restaurar valores originales
    $cuiCell.text(cui || '');
    $cueanexoCell.text(cueanexo || '');
    // Restaurar botones originales
    $accionesCell.html(`
        <button class="btn btn-sm btn-primary btnEditar" 
                data-idrel="${idrel}" 
                data-cui="${cui || ''}" 
                data-cueanexo="${cueanexo || ''}">
            Editar
        </button>
        <a href="buscarcuixcodigo.php" class="btn btn-sm btn-success">
            Dar de Alta CUI
        </a>
    `);
});
$(document).on('click', '.btnGuardar', function () {
    var btn = $(this);
    var row = btn.closest('tr');
    var idrel = btn.data('idrel');
    var cui = row.find('td').eq(1).find('select').val();
    var cueanexo = row.find('td').eq(2).find('input').val();
    $.ajax({
        url: '../ajax/update_cui_cueanexo.php',
        method: 'POST',
        data: {
            idrel: idrel,
            cui: cui,
            cueanexo: cueanexo
        },
        success: function (response) {
            if (response === 'OK') {
                // Reemplazar fila con valores nuevos
                row.find('td').eq(1).text(cui);
                row.find('td').eq(2).text(cueanexo);
                row.find('td').eq(3).html(`
                    <button class="btn btn-sm btn-primary btnEditar" 
                            data-idrel="${idrel}" 
                            data-cui="${cui}" 
                            data-cueanexo="${cueanexo}">
                        Editar
                    </button>
                    <a href="buscarcuixcodigo.php" class="btn btn-sm btn-success">
                        Dar de Alta CUI
                    </a>
                `);
            } else {
                alert('Error al guardar: ' + response);
            }
        },
        error: function () {
            alert('Error en la comunicación con el servidor.');
        }
    });
});