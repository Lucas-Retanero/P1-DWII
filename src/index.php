<?php
namespace Api\P1Dwii;
use Api\P1Dwii\Controller\controller;
use Api\P1Dwii\Model\Database;

require_once '../vendor/autoload.php';
require 'src/Model/db.php';
require 'src/Controller/controller.php';

$db = (new Database())->getConnection();
$controller = new controller($db);

// Definir a rota com base na URL e no método HTTP
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Roteamento manual
if ($path == '/produtos' && $method == 'GET') {
    $controller->getProdutos();
} elseif (preg_match('/\/produtos\/(\d+)/', $path, $matches) && $method == 'GET') {
    $id = $matches[1];
    $controller->getProduto($id);
} elseif ($path == '/produtos' && $method == 'POST') {
    $controller->createProduto();
} elseif (preg_match('/\/produtos\/(\d+)/', $path, $matches) && $method == 'PUT') {
    $id = $matches[1];
    $controller->updateProduto($id);
} elseif (preg_match('/\/produtos\/(\d+)/', $path, $matches) && $method == 'DELETE') {
    $id = $matches[1];
    $controller->deleteProduto($id);
} elseif ($path == '/logs' && $method == 'GET') {
    $controller->getLogs();
} elseif ($path == '/logs' && $method == 'DELETE') {
    $controller->clearLogs();
} else {
    // Resposta para rota não encontrada
    http_response_code(404);
    echo json_encode(["message" => "Rota não encontrada"]);
}
