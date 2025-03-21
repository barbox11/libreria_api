<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

include "bd.php";

// Manejo de los  errores
function responderError($mensaje, $codigo = 400) {
    http_response_code($codigo);
    echo json_encode(["error" => $mensaje]);
    exit;
}

// llamar todos los libros
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        $sql = "SELECT * FROM libros";
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception("Error al obtener los libros");
        }

        $libros = [];
        while ($row = $result->fetch_assoc()) {
            $libros[] = $row;
        }

        echo json_encode($libros);
    } catch (Exception $e) {
        responderError($e->getMessage(), 500);
    }
}

// Agregar un libro
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Validar datos requeridos
        if (!isset($data["titulo"]) || !isset($data["autor"]) || 
            !isset($data["genero"]) || !isset($data["precio"]) || 
            !isset($data["stock"])) {
            throw new Exception("Faltan datos requeridos");
        }

        // Preparar la consulta para prevenir SQL Injection
        $stmt = $conn->prepare("INSERT INTO libros (titulo, autor, genero, precio, stock) 
                            VALUES (?, ?, ?, ?, ?)");
        
        $stmt->bind_param("sssdi", 
            $data["titulo"],
            $data["autor"],
            $data["genero"],
            $data["precio"],
            $data["stock"]
        );

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Libro agregado correctamente"]);
        } else {
            throw new Exception("Error al agregar el libro");
        }
        
        $stmt->close();
    } catch (Exception $e) {
        responderError($e->getMessage(), 500);
    }
}

$conn->close();
?>