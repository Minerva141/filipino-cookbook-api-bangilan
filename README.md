# Filipino Cookbook API

## API Description

The Filipino Cookbook API is a secured REST API that provides structured information about traditional Filipino dishes, including their categories, regional origins, ingredients, and cooking instructions.

- **Purpose:** To give developers a simple, structured way to retrieve data about Filipino foods for use in other applications (websites, mobile apps, chatbots, etc.).
- **Type of information provided:** Food names, categories, regional origins, cooking instructions, and ingredient lists.
- **Intended users:** Students and developers building client applications that need Filipino food data.
- **Main functions:** Retrieve all foods, retrieve a single food, search foods by name, retrieve categories, retrieve ingredients, and add new foods.
- **Technologies used:** PHP, Slim Framework, MySQL, Composer.

## Features

- Retrieve all Filipino foods
- Retrieve food categories
- Retrieve food origins (embedded within food records)
- Retrieve ingredients
- View the details of a specific food by ID
- Search for foods by name
- Add a new food entry
- Authenticate requests using a Bearer token
- Return information in JSON format

## Technologies Used

- PHP
- Slim Framework 4
- MySQL
- Composer
- JSON
- XAMPP (Apache + MySQL)
- Thunder Client (for testing)
- Git
- GitHub

## Installation Instructions

1. Clone the repository:

```
git clone https://github.com/Minerva141/filipino-cookbook-api-bangilan.git
```

2. Navigate into the project folder:

```
cd filipino-cookbook-api-bangilan
```

3. Install dependencies:

```
composer install
```

4. Import the database (see **Database Setup** below).
5. Start Apache and MySQL in XAMPP.
6. Run the API using PHP's built-in server:

```
php -S localhost:8000 -t public
```

7. Test the endpoints using Thunder Client or Postman (see **Endpoint Documentation** below).

## Database Setup

- **Database name:** `filipino_cookbook_api`
- **SQL file:** `filipino_cookbook_api.sql`

**Import instructions:**
1. Open phpMyAdmin (`http://localhost/phpmyadmin`)
2. Click **Import**
3. Select `filipino_cookbook_api.sql`
4. Click **Go**

**Tables:**
- `categories` — food categories (e.g. Soup, Dessert)
- `origins` — regional origins (e.g. Bicol Region, Ilocos Region)
- `foods` — the main food records
- `ingredients` — all possible ingredients
- `food_ingredients` — junction table linking foods to their ingredients (many-to-many)

**Relationships:**

```
categories -> foods <- origins
foods -> food_ingredients <- ingredients
```

## Base URL

```
http://localhost:8000
```

## Authentication Instructions

This API uses **Bearer token authentication**. All routes under `/api/...` require a valid token; only the root welcome route (`/`) is public.

**Required header:**

```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

**If the token is missing or invalid**, the API responds with:

```json
{
  "status": "error",
  "message": "Unauthorized access. Valid API token is required."
}
```

with HTTP status `401`.

## Endpoint Documentation

### 1. Welcome Route

**GET /**
No token required.

Example response:

```json
{
  "message": "Welcome to the Secured Filipino Cookbook API",
  "note": "Use a valid Bearer token to access /api endpoints."
}
```

### 2. Get All Foods

**GET /api/foods**

Required headers:

```
Authorization: Bearer dmmmsu-cookbook-token-2026
```

Example response:

```json
[
  {
    "food_id": 1,
    "food_name": "Adobo",
    "category_name": "Main Dish",
    "origin_name": "Philippines",
    "instructions": "Marinate the meat with soy sauce, vinegar, garlic, bay leaves, and peppercorn...",
    "ingredients": ["Bay leaves", "Chicken or pork", "Cooking oil", "Garlic", "Peppercorn", "Soy sauce", "Vinegar"]
  }
]
```

### 3. Get Food by ID

**GET /api/foods/{id}**

Example request:

```
GET http://localhost:8000/api/foods/1
```

If not found:

```json
{
  "status": "error",
  "message": "Food not found"
}
```

(HTTP status `404`)

### 4. Search Foods by Name

**GET /api/foods/search/{name}**

Example request:

```
GET http://localhost:8000/api/foods/search/pan
```

### 5. Get All Categories

**GET /api/categories**

### 6. Get All Ingredients

**GET /api/ingredients**

### 7. Add New Food

**POST /api/foods**

Required headers:

```
Authorization: Bearer dmmmsu-cookbook-token-2026
Content-Type: application/json
```

Example request body:

```json
{
  "food_name": "Dinengdeng",
  "category_id": 3,
  "origin_id": 4,
  "instructions": "Boil vegetables with bagoong-based broth and add grilled fish before serving.",
  "ingredient_ids": [4, 14, 1]
}
```

Example success response:

```json
{
  "status": "success",
  "message": "Food added successfully."
}
```

(HTTP status `201`)

## HTTP Status Codes

| Status Code | Meaning |
|---|---|
| 200 | Request completed successfully |
| 201 | Resource created successfully |
| 401 | Missing or invalid authentication |
| 404 | Requested resource was not found |
| 500 | Internal server error |

## Testing Evidence

## Testing Evidence

**1. Welcome route (GET /)**
![Welcome route](screenshots/01-welcome.png)

**2. Get all foods (200 OK)**
![Get all foods](screenshots/02-get-all-foods.png)

**3. Get food by ID (200 OK)**
![Get food by ID](screenshots/03-get-food-by-id.png)

**4. Search foods by name (200 OK)**
![Search foods](screenshots/04-search-foods.png)

**5. Get all categories (200 OK)**
![Get categories](screenshots/05-get-categories.png)

**6. Get all ingredients (200 OK)**
![Get ingredients](screenshots/06-get-ingredients.png)

**7. Add new food (201 Created)**
![Add new food](screenshots/07-add-food-201.png)

**8. Missing token (401 Unauthorized)**
![Unauthorized](screenshots/08-unauthorized-401.png)

**9. Food not found (404 Not Found)**
![Not found](screenshots/09-not-found-404.png)

## Developer Information

- **Name:** Athena Bangilan
- **Course & Section:** BSIT - 4A
- **GitHub username:** Minerva141
- **Repository:** https://github.com/Minerva141/filipino-cookbook-api-bangilan
- **Date completed:** July 24, 2026
