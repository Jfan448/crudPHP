<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion
  </title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
  <!-- Bootstrap Icons CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include_once '../componentes/navbar.php' ?>
  <div>
    <h1 class="text-center">GESTION DE PROYECTOS</h1>
    <div class="row">
      <div class="col-2 offset-10">
        <div class="text-center">
          <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalProyecto" id="botonCrear">
            <i class="bi bi-plus-circle-fill"></i> Crear
          </button>
        </div>
      </div>        
    </div>
    <br>
    <br>
    <div class="table-responsive">
      <table id="datos_proyecto" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>Codigo</th>
            <th>Descripcion</th>
            <th>Fecha de inicio</th>
            <th>Estado</th>
            <th>Miembro del equipo</th>
            <th>Editar</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
  <!-- Modal -->
  <div class="modal fade" id="modalProyecto" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="exampleModalLabel">Crear Proyecto</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" id="formulario" enctype="multipart/form-data">
          <div class="modal-content">
            <div class="modal-body">
              <input type="hidden" name="codigo_proyecto" id="codigo_proyecto">
              <label for="descripcion">Descripcion</label>
              <input type="text" name="descripcion" id="descripcion" class="form-control">
              <br>
              <label for="fecha_inicio">Fecha de inicio</label>
              <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control">
              <br>
              <label for="estado">Estado</label>
              <select name="estado" id="estado" class="form-control">
                <option value="2">Seleccione una de las siguientes opciones...</option>
                <option value="Completado">Completado</option>
                <option value="En curso">En curso</option>
                <option value="Pausado">Pausado</option>
                <option value="En espera">En espera</option>
              </select>
              <br>
              <label for="id_miembro">Miembro del equipo</label>
              <select name="id_miembro" id="id_miembro" class="form-control">
                <!-- Miembros del equipo se llenarán dinámicamente -->
              </select>
              <br>
            </div>
            <div class="modal-footer">
              <input type="hidden" name="operacion" id="operacion">
              <input type="submit" name="action" id="action" class="btn btn-primary" value="Crear">
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
  <!-- DataTables JavaScript -->
  <script src="//cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
  <!-- Bootstrap JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  <script type="text/javascript">
    $(document).ready(function() {
      function cargarMiembros() {
        $.ajax({
          url: "proyects.controller.php",
          method: "POST",
          data: {operacion: 'obtener_miembros'},
          dataType: "json",
          success: function(data) {
            $('#id_miembro').empty();
            data.forEach(function(miembro) {
              $('#id_miembro').append('<option value="' + miembro.id_miembro + '">' + miembro.nombre + '</option>');
            });
          }
        });
      }

      $("#botonCrear").click(function() {
        $("#formulario")[0].reset();
        $(".modal-title").text("Crear Proyecto");
        $("#action").val("Crear").removeClass('btn-success').addClass('btn-primary');
        $("#operacion").val("crear");
        cargarMiembros();
      });

      var dataTable = $('#datos_proyecto').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
          url: "proyects.controller.php",
          type: "POST"
        },
        "columnDefs": [
          { "targets": "_all", "className": "text-center" },
          {
            "targets": [5],
            "orderable": false,
          }
        ]
      });

      $(document).on('submit', '#formulario', function(event) {
        event.preventDefault();
        var descripcion = $("#descripcion").val();
        var fecha_inicio = $("#fecha_inicio").val();
        var estado = $("#estado").val();
        var id_miembro = $("#id_miembro").val();

        if (descripcion != '' && fecha_inicio != '' && estado != '' && id_miembro != '') {
          $.ajax({
            url: "proyects.controller.php",
            method: 'POST',
            data: new FormData(this),
            contentType: false,
            processData: false,
            success: function(data) {
              alert(data);
              $('#formulario')[0].reset();
              $('#modalProyecto').modal('hide');
              dataTable.ajax.reload();
            }
          });
        } else {
          alert("Todos los campos son obligatorios");
        }
      });

      // Funcionalidad de editar
      $(document).on('click', '.editar', function() {
        var codigo_proyecto = $(this).attr("id");
        $.ajax({
          url: "proyects.controller.php",
          method: "POST",
          data: {codigo_proyecto: codigo_proyecto, operacion: 'obtener_registro'},
          dataType: "json",
          success: function(data) {
            $('#modalProyecto').modal('show');
            $("#codigo_proyecto").val(data.codigo_proyecto);
            $("#descripcion").val(data.descripcion);
            $("#fecha_inicio").val(data.fecha_inicio);
            $("#estado").val(data.estado);
            cargarMiembros(); // Cargar miembros y luego seleccionar el miembro asignado
            setTimeout(function() {
              $("#id_miembro").val(data.id_miembro);
            }, 500); // Esperar medio segundo para asegurar que los miembros están cargados
            $('.modal-title').text("Editar Proyecto");
            $("#action").val("Editar").removeClass('btn-primary').addClass('btn-success');
            $("#operacion").val("editar");
          }
        });
      });
    });
  </script>
</body>
</html>
