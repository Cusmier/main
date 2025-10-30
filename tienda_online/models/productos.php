<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
require 'config.php';

// --- PAGINACIÓN ---
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// --- FILTROS ---
$nombre = isset($_GET['nombre']) ? "%{$_GET['nombre']}%" : null;
$minPrecio = isset($_GET['min']) ? (float)$_GET['min'] : null;
$maxPrecio = isset($_GET['max']) ? (float)$_GET['max'] : null;

$whereClauses = [];
$params = [];

if ($nombre) $whereClauses[] = "nombre LIKE :nombre";
if ($minPrecio !== null) $whereClauses[] = "precio >= :minPrecio";
if ($maxPrecio !== null) $whereClauses[] = "precio <= :maxPrecio";

$whereSQL = count($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

$sql = "SELECT id, nombre, descripcion, precio, imagen AS imagen_url 
        FROM productos $whereSQL LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);

if ($nombre) $stmt->bindValue(':nombre', $nombre);
if ($minPrecio !== null) $stmt->bindValue(':minPrecio', $minPrecio);
if ($maxPrecio !== null) $stmt->bindValue(':maxPrecio', $maxPrecio);

$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($productos) {
    echo json_encode([
        "status" => "success",
        "total" => count($productos),
        "page" => $page,
        "limit" => $limit,
        "data" => $productos
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No hay productos disponibles."
    ]);
}
?>
