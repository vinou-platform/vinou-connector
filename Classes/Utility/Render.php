<?php
namespace Vinou\VinouConnector\Utility;


/**
* Render
*/
class Render {

	public static function sendJSON($data, $type = 'regular') {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        switch ($type) {
            case 'error':
                header('HTTP/1.0 400 Bad Request');
                break;
            default:
                header('HTTP/1.1 200 OK');
        }
        echo json_encode($data);
        exit();
    }

}