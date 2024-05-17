<?php

include("../conexion.php");

@$action = $_POST["operacion"];
main($action, $conexion);

function main($action, $conexion) {
    switch ($action) {
        case 'crear':
            crear($conexion);
            break;
        case 'editar':
            editar($conexion);
            break;
        case 'borrar':
            borrar($conexion);
            break;
        case 'obtener_registro':
            obtener_registro($conexion);
            break;
        case 'obtener_miembros':
            obtener_miembros($conexion);
            break;
        default:
            obtener_registros($conexion);
            break;
    }
}

function borrar($conexion) {
    if (isset($_POST["codigo_proyecto"])) {
        $stmt = $conexion->prepare("DELETE FROM proyectos WHERE codigo_proyecto = :codigo_proyecto");
        $resultado = $stmt->execute(array(':codigo_proyecto' => $_POST["codigo_proyecto"]));
        if (!empty($resultado)) {
            echo 'Registro borrado';
        }
    }
}

function crear($conexion) {
    $stmt = $conexion->prepare("INSERT INTO proyectos (descripcion, fecha_inicio, estado, id_miembro) VALUES (:descripcion, :fecha_inicio, :estado, :id_miembro)");
    $resultado = $stmt->execute(array(
        ':descripcion' => $_POST["descripcion"],
        ':fecha_inicio' => $_POST["fecha_inicio"],
        ':estado' => $_POST["estado"],
        ':id_miembro' => $_POST["id_miembro"]
    ));
    if (!empty($resultado)) {
        echo 'Registro creado';
    }
}

function editar($conexion) {
    $stmt = $conexion->prepare("UPDATE proyectos SET descripcion=:descripcion, fecha_inicio=:fecha_inicio, estado=:estado, id_miembro=:id_miembro WHERE codigo_proyecto = :codigo_proyecto");
    $resultado = $stmt->execute(array(
        ':descripcion' => $_POST["descripcion"],
        ':fecha_inicio' => $_POST["fecha_inicio"],
        ':estado' => $_POST["estado"],
        ':id_miembro' => $_POST["id_miembro"],
        ':codigo_proyecto' => $_POST["codigo_proyecto"]
    ));
    if (!empty($resultado)) {
        echo 'Registro actualizado';
    } else {
        echo "No se pudo actualizar el registro";
    }
}

function obtener_registro($conexion) {
    $salida = array();
    try {
        $stmt = $conexion->prepare("SELECT * FROM proyectos WHERE codigo_proyecto = :codigo_proyecto LIMIT 1");
        $stmt->bindParam(':codigo_proyecto', $_POST['codigo_proyecto'], PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            $salida = $resultado;
        } else {
            $salida["error"] = "No se encontraron resultados";
        }
    } catch (PDOException $e) {
        $salida["error"] = "Error en la ejecución de la consulta: " . $e->getMessage();
    }
    echo json_encode($salida);
}

function obtener_miembros($conexion) {
    $query = "SELECT * FROM miembros_equipo";
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($resultado);
}

function obtener_registros($conexion) {
    $query = "";
    $salida = array();
    $query = "SELECT p.*, m.nombre AS nombre_miembro FROM proyectos p LEFT JOIN miembros_equipo m ON p.id_miembro = m.id_miembro ";

    if (isset($_POST["search"]["value"])) {
        $query .= 'WHERE p.descripcion LIKE "%' . $_POST["search"]["value"] . '%" ';
        $query .= 'OR p.fecha_inicio LIKE "%' . $_POST["search"]["value"] . '%" ';
        $query .= 'OR m.nombre LIKE "%' . $_POST["search"]["value"] . '%" ';
    }

    if (isset($_POST["order"])) {
        $query .= 'ORDER BY ' . $_POST['order']['0']['column'] . ' ' . $_POST["order"][0]['dir'] . ' ';
    } else {
        $query .= 'ORDER BY p.codigo_proyecto DESC ';
    }

    if (isset($_POST["length"]) && isset($_POST["start"])) {
        $query .= 'LIMIT ' . $_POST["start"] . ', ' . $_POST["length"];
    }

    $stmt = $conexion->prepare($query);

    try {
        $stmt->execute();
        $resultado = $stmt->fetchAll();
        $datos = array();
        $filtered_rows = $stmt->rowCount();

        $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;

        foreach ($resultado as $fila) {
            $sub_array = array();
            $sub_array[] = $fila["codigo_proyecto"];
            $sub_array[] = $fila["descripcion"];
            $sub_array[] = $fila["fecha_inicio"];
            $sub_array[] = $fila["estado"];
            $sub_array[] = $fila["nombre_miembro"];
            $sub_array[] = '<div class="text-center"><button type="button" name="editar" id="' . $fila["codigo_proyecto"] . '" class="btn btn-success btn-xs editar"><i class="bi bi-pencil-fill"></i></button></div>';
            $sub_array[] = '<div class="text-center"><button type="button" name="detalle" id="' . $fila["codigo_proyecto"] . '" class="btn btn-info btn-xs detalle"><i class="bi bi-info-circle-fill"></i></button></div>';
            $datos[] = $sub_array;
        }
        
        }

        $salida = array(
            "draw" => $draw,
            "recordsTotal" => $filtered_rows,
            "recordsFiltered" => obtener_todos_registros($conexion),
            'data' => $datos
        );

        echo json_encode($salida);

    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
    }
}

function obtener_todos_registros($conexion) {
    $stmt = $conexion->prepare("SELECT * FROM proyectos");
    $stmt->execute();
    return $stmt->rowCount();
}

?>

