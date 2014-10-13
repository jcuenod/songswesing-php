<?php
include ("controller.php");

$task = empty($_POST["task"]) ? false : $_POST["task"];

switch ($task) {

	case 'store':
        $service = new service_object(
            $_POST['date'],
            $_POST['service_type'],
            $_POST['leader'],
            $_POST['songs']
        );
        $controller->storeService($service);
        echo $controller->getService($service);
		break;

    case 'getSongs':
        header('Content-Type: application/json');
        echo json_encode($controller->getSongs());
        break;


	case 'update':
		break;


    case 'create':
        switch ($_POST['action'])
        {
            case "dialog":
                echo $controller->getCreateForm($_POST["type"]);
                break;
            case "submit":
                $var["object_type"] = $_POST["type"];
                $var["values"] = $_POST["values"];
                header('Content-Type: application/json');
                echo $controller->insertNew($var);
                break;
        }
        break;


    case 'songusage':
        echo $controller->getSongUsageTable();
        break;


    case 'songdata':
        header('Content-Type: application/json');
        echo $controller->getSongDataJSON($_POST["song_name"]);
        break;


    case 'leaderdata':
        header('Content-Type: application/json');
        echo $controller->getLeaderDataJSON($_POST["leader_name"]);
        break;


	default:
        $drawindex = true;
        include("./drawindex.php");
}

?>