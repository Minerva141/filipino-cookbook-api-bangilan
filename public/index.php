<?php
// ============================================================
// FILIPINO COOKBOOK API — public/index.php
// Built with Slim Framework 4 + PDO + Token-based security
// ============================================================

use Psr\Http\Message\ResponseInterface as Response; // For type hinting the response object
use Psr\Http\Message\ServerRequestInterface as Request; // For type hinting the request object
use Slim\Factory\AppFactory; // For creating the Slim app

require __DIR__ . '/../vendor/autoload.php'; // Autoload dependencies installed via Composer

// 1. CREATE THE SLIM APP

$app = AppFactory::create(); // Create a new Slim app instance

// Lets Slim read JSON bodies sent in POST requests (needed for "Add New Food")
$app->addBodyParsingMiddleware(); // Enable body parsing middleware to handle JSON request bodies

// Shows readable error messages while developing
$app->addErrorMiddleware(true, true, true); // Enable error middleware for debugging (set to false in production)

// 2. DATABASE CONNECTION (PDO)

function getDB(): PDO // Returns a PDO instance for database connection
{
    $host = '127.0.0.1';
    $db   = 'filipino_cookbook_api';
    $user = 'root';       // default XAMPP username
    $pass = '';           // default XAMPP password (empty)
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset"; // Data Source Name for PDO connection
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, $user, $pass, $options);
}


// 3. TOKEN-BASED SECURITY (MIDDLEWARE)

const API_TOKEN = 'dmmmsu-cookbook-token-2026';

function requireToken(Request $request, $handler): Response // Middleware function to check for a valid API token
{
    $authHeader = $request->getHeaderLine('Authorization'); // Get the Authorization header from the request

    // Expected format: "Bearer dmmmsu-cookbook-token-2026"
    if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) { // Check if the Authorization header is missing or doesn't start with "Bearer "
        $response = new \Slim\Psr7\Response();  // Create a new response object
        return unauthorizedResponse($response); // Return a 401 Unauthorized response if the token is missing or invalid
    }

    $token = trim(str_replace('Bearer', '', $authHeader)); // Extract the token from the Authorization header

    if ($token !== API_TOKEN) { // Check if the provided token matches the expected API token
        $response = new \Slim\Psr7\Response(); 
        return unauthorizedResponse($response);
    }

    // Token is valid -> let the request continue to the actual route
    return $handler->handle($request); // Pass the request to the next middleware or route handler
}

// Helper function to return a JSON response for unauthorized access
function unauthorizedResponse(Response $response): Response // Returns a 401 Unauthorized response with a JSON error message
{
    $payload = [
        'status'  => 'error', // Indicate an error status
        'message' => 'Unauthorized access. Valid API token is required.'
    ];
    $response->getBody()->write(json_encode($payload)); // Write the JSON payload to the response body
    return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
}

function jsonResponse(Response $response, $data, int $status = 200): Response // Helper function to return a JSON response with a given status code
{
    $response->getBody()->write(json_encode($data)); // Write the JSON-encoded data to the response body
    return $response->withHeader('Content-Type', 'application/json')->withStatus($status); // Set the Content-Type header to application/json and return the response with the specified status code
}

// 4. PUBLIC ROUTE — Welcome message (NO token required)

$app->get('/', function (Request $request, Response $response) {
    $data = [
        'message' => 'Welcome to the Secured Filipino Cookbook API',
        'note'    => 'Use a valid Bearer token to access /api endpoints.'
    ];
    return jsonResponse($response, $data);
});

// 5. PROTECTED ROUTES — everything under /api requires the token
$app->group('/api', function ($group) {

    // 5.1 GET all foods 
    $group->get('/foods', function (Request $request, Response $response) {
        $db = getDB();

        $sql = "SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions
                FROM foods f
                JOIN categories c ON f.category_id = c.category_id
                JOIN origins o ON f.origin_id = o.origin_id
                ORDER BY f.food_id"; // SQL query to fetch all food items along with their category and origin names, ordered by food_id
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $foods = $stmt->fetchAll();

        foreach ($foods as &$food) {
            $ingStmt = $db->prepare(
                "SELECT i.ingredient_name
                 FROM food_ingredients fi
                 JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
                 WHERE fi.food_id = :food_id" // SQL query to fetch the ingredients for each food item based on its food_id 
            );
            $ingStmt->execute(['food_id' => $food['food_id']]);
            $food['ingredients'] = array_column($ingStmt->fetchAll(), 'ingredient_name'); // Add the list of ingredients to each food item
        }

        return jsonResponse($response, $foods);
    });

    // 5.2 GET one food by ID 
    $group->get('/foods/{id}', function (Request $request, Response $response, $args) { // Get a specific food item by its ID
        $db = getDB();
        $id = $args['id'];

        $stmt = $db->prepare(
            "SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions
             FROM foods f
             JOIN categories c ON f.category_id = c.category_id
             JOIN origins o ON f.origin_id = o.origin_id
             WHERE f.food_id = :id"
        );
        $stmt->execute(['id' => $id]);
        $food = $stmt->fetch();

        if (!$food) {
            return jsonResponse($response, [
                'status'  => 'error',
                'message' => 'Food not found'
            ], 404);
        }

        $ingStmt = $db->prepare(
            "SELECT i.ingredient_name
             FROM food_ingredients fi
             JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
             WHERE fi.food_id = :food_id"
        );
        $ingStmt->execute(['food_id' => $food['food_id']]);
        $food['ingredients'] = array_column($ingStmt->fetchAll(), 'ingredient_name');

        return jsonResponse($response, $food);
    });

    // 5.3 Search food by name
    $group->get('/foods/search/{name}', function (Request $request, Response $response, $args) {
        $db = getDB();
        $name = $args['name'];

        $stmt = $db->prepare(
            "SELECT f.food_id, f.food_name, c.category_name, o.origin_name, f.instructions
             FROM foods f
             JOIN categories c ON f.category_id = c.category_id
             JOIN origins o ON f.origin_id = o.origin_id
             WHERE f.food_name LIKE :name"
        );
        $stmt->execute(['name' => '%' . $name . '%']); // Execute the prepared statement with the search term
        $foods = $stmt->fetchAll();

        foreach ($foods as &$food) { // Loop through each food item to fetch its ingredients
            $ingStmt = $db->prepare(
                "SELECT i.ingredient_name
                 FROM food_ingredients fi
                 JOIN ingredients i ON fi.ingredient_id = i.ingredient_id
                 WHERE fi.food_id = :food_id"
            );
            $ingStmt->execute(['food_id' => $food['food_id']]);
            $food['ingredients'] = array_column($ingStmt->fetchAll(), 'ingredient_name');
        }

        return jsonResponse($response, $foods);
    });

    // 5.4 GET all categories 
    $group->get('/categories', function (Request $request, Response $response) {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM categories");
        return jsonResponse($response, $stmt->fetchAll());
    });

    // 5.5 GET all ingredients
    $group->get('/ingredients', function (Request $request, Response $response) {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM ingredients");
        return jsonResponse($response, $stmt->fetchAll());
    });

    // 5.6 POST — Add a new food 
    $group->post('/foods', function (Request $request, Response $response) {
        $db = getDB();
        $body = $request->getParsedBody();

        $foodName     = $body['food_name'] ?? null;
        $categoryId   = $body['category_id'] ?? null;
        $originId     = $body['origin_id'] ?? null;
        $instructions = $body['instructions'] ?? null;
        $ingredientIds = $body['ingredient_ids'] ?? [];

 
        $maxIdStmt = $db->query("SELECT MAX(food_id) AS max_id FROM foods");
        $maxId = $maxIdStmt->fetch()['max_id'] ?? 0;
        $newFoodId = $maxId + 1;

        $stmt = $db->prepare(
            "INSERT INTO foods (food_id, food_name, category_id, origin_id, instructions)
             VALUES (:food_id, :food_name, :category_id, :origin_id, :instructions)"
        );
        $stmt->execute([
            'food_id'      => $newFoodId,
            'food_name'    => $foodName,
            'category_id'  => $categoryId,
            'origin_id'    => $originId,
            'instructions' => $instructions,
        ]);

        $linkStmt = $db->prepare( // Prepare a statement to link the new food with its ingredients
            "INSERT INTO food_ingredients (food_id, ingredient_id) VALUES (:food_id, :ingredient_id)"
        );
        foreach ($ingredientIds as $ingredientId) {
            $linkStmt->execute([
                'food_id'       => $newFoodId,
                'ingredient_id' => $ingredientId,
            ]);
        }

        return jsonResponse($response, [
            'status'  => 'success',
            'message' => 'Food added successfully.'
        ], 201);
    });

})->add('requireToken');

$app->run();