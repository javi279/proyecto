<?php
require_once "../modelos/Actividad.php";
if (strlen(session_id()) < 1) 
    session_start();
$actividad = new Actividad();

// Recibir y limpiar los datos de entrada
$id_actividad = isset($_POST["id_actividad"]) ? limpiarCadena($_POST["id_actividad"]) : "";
$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";
$block_id = isset($_POST["idcurso"]) ? intval(limpiarCadena($_POST["idcurso"])) : 0;
$fecha_limite = isset($_POST["fecha_limite"]) ? limpiarCadena($_POST["fecha_limite"]) : ""; 
$team_id = isset($_POST["idgrupo"]) ? limpiarCadena($_POST["idgrupo"]) : ""; // Agregar team_id para filtrar estudiantes

switch ($_GET["op"]) {
    case 'guardaryeditar':
        if (empty($id_actividad)) {
            $rspta = $actividad->insertar($nombre, $descripcion, $block_id, $fecha_limite); 
            echo $rspta ? "Actividad registrada correctamente" : "No se pudo registrar la actividad";
        } else {
            $rspta = $actividad->editar($id_actividad, $nombre, $descripcion, $block_id, $fecha_limite); 
            echo $rspta ? "Actividad actualizada correctamente" : "No se pudo actualizar la actividad";
        }
        break;

    case 'listar':
        $block_id = isset($_POST['idcurso']) ? limpiarCadena($_POST['idcurso']) : "";
        if (!empty($block_id)) {
            $rspta = $actividad->listar($block_id);
            $data = array();
            while ($reg = $rspta->fetch_object()) {
                $data[] = array(
                    "0" => ($reg->is_active == 1)
                        ? "<button class='btn btn-warning btn-xs' onclick='mostrar($reg->id_actividad)'><i class='fa fa-pencil'></i></button>"
                        . " <button class='btn btn-danger btn-xs' onclick='desactivar($reg->id_actividad)'><i class='fa fa-close'></i></button>"
                        . " <button class='btn btn-primary btn-xs' onclick='abrirBeneficiarios($reg->id_actividad)'><i class='fa fa-user-plus'></i> Beneficiarios</button>"
                        : "<button class='btn btn-primary btn-xs' onclick='activar($reg->id_actividad)'><i class='fa fa-check'></i></button>",
                    "1" => $reg->nombre,
                    "2" => $reg->descripcion,
                    "3" => $reg->fecha_limite,
                    "4" => ($reg->is_active == 1) ? 'Activa' : 'Inactiva',
                );
            }
            $results = array(
                "sEcho" => 1,
                "iTotalRecords" => count($data),
                "iTotalDisplayRecords" => count($data),
                "aaData" => $data
            );
            echo json_encode($results);
        } else {
            echo json_encode(array(
                "sEcho" => 1,
                "iTotalRecords" => 0,
                "iTotalDisplayRecords" => 0,
                "aaData" => array()
            ));
        }
        break;

    case 'mostrar':
        $rspta = $actividad->mostrar($id_actividad);
        echo json_encode($rspta);
        break;

    case 'desactivar':
        $rspta = $actividad->desactivar($id_actividad);
        echo $rspta ? "Actividad desactivada" : "No se pudo desactivar la actividad";
        break;

    case 'activar':
        $rspta = $actividad->activar($id_actividad);
        echo $rspta ? "Actividad activada" : "No se pudo activar la actividad";
        break;

    case 'selectBeneficiarios':
        // Obtener lista de beneficiarios por grupo
        $rspta = $actividad->listarAlumnosPorGrupo($team_id);
        $options = '<option value="">Seleccione un alumno</option>';
        while ($reg = $rspta->fetch_object()) {
            $options .= '<option value="' . $reg->alumn_id . '">' . $reg->nombre . '</option>';
        }
        echo $options;
        break;

    case 'listarBeneficiariosDisponibles':
        // Obtener beneficiarios no asignados aún a la actividad
        $rspta = $actividad->listarAlumnosDisponibles($team_id, $id_actividad);
        $options = '';
        while ($reg = $rspta->fetch_object()) {
            $options .= '<option value="' . $reg->id_alumn . '">' . $reg->nombre . '</option>';
        }
        echo $options;
        break;

    case 'guardarBeneficiariosAsignados':
        // Guardar las asignaciones de beneficiarios a una actividad
        $beneficiarios = isset($_POST["beneficiarios"]) ? $_POST["beneficiarios"] : array();
        $rspta = $actividad->guardarAsignacionesBeneficiarios($id_actividad, $beneficiarios);
        echo $rspta ? "Beneficiarios asignados correctamente" : "Error al asignar beneficiarios";
        break;
}
?>
