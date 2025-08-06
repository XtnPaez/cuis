$(document).ready(function () {

    // Al hacer clic en el botón de listado
    $('#btnListado').click(function () {
        cargarPagina(1);
    });

    // Cargar una página específica
    window.cargarPagina = function (pagina) {
        $.get('../ajax/get_cui_cueanexo.php?page=' + pagina, function (data) {
            $('#resultadoListado').html(data);
        });
    };

});
