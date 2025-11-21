# LBAO – Custom Pen E-Commerce

This project is a small e-commerce application built as part of a training project at Cinnk.

It covers:

- A **front-end shop** where customers can browse products, configure a custom pen and place an order.
- A **back-end API** exposing products and receiving orders.
- An **admin back-office** for managing the catalog, stocks and orders.

The main client use case is “La Boîte à Objets”, a shop that sells everyday items (mugs, t-shirts, tote bags, pens) and wants to launch a **customizable pen** while keeping stock management simple.

---

## 1. Features

### Front (customer side)

- Product catalog (mugs, t-shirts, tote bags, custom pen).
- Product detail page with **pen customization**:
    - cap color (cap),
    - body color (body),
    - tip type (mine).
- Cart with:
    - add / remove items,
    - quantity update,
    - total recalculation.
- Checkout:
    - customer information form (name, email, address),
    - order submission to the API,
    - success / error messages.

### Back (admin / API side)

- REST-like API:
    - `GET /api` → health check.
    - `GET /api/products` → list products.
    - `GET /api/products/{id}` → product detail.
    - `POST /api/orders` → create an order from front payload.
- Admin back-office:
    - **Login / logout** with PHP sessions.
    - **Dashboard** with key indicators.
    - **Products management**:
        - create / edit / delete products,
        - manage stock,
        - mark a product as **customizable**,
        - define customization options (cap/body/tip).
    - **Bulk stock update** page.
    - **Orders management**:
        - list orders,
        - view order detail,
        - see customization options per order line,
        - update order status (new, in preparation, shipped, canceled).

---

## 2. Tech stack

- **Language**: PHP 8.x
- **Database**: MySQL / MariaDB
- **Server**: Apache (Plesk hosting)
- **Front**:
    - HTML5 / CSS3 (responsive, grid for catalog)
    - Vanilla JavaScript (ES6) for:
        - catalog fetching,
        - cart in `localStorage`,
        - order submission.
- **Back-end**:
    - PHP with PDO for database access
    - Simple MVC-like structure:
        - `config/` for DB config
        - `src/models/` for data access
        - `public/` for HTTP entrypoints

No heavy framework is used, on purpose, to focus on fundamentals.

---

## 3. Project structure

The project is split into two main parts: **back** (API + admin) and **front** (shop).

```txt
back/
  config/
    database.php         # PDO connection helper
  public/
    index.php            # root redirect
    login.php            # admin login
    logout.php           # admin logout
    api/
      index.php          # API router (/api/...)
    admin/
      dashboard.php      # admin dashboard
      products.php       # product listing
      product-edit.php   # product create/edit
      stocks.php         # bulk stock management
      orders.php         # orders list
      order-view.php     # order detail
    partials/
      admin-header.php   # admin layout header
      admin-footer.php   # admin layout footer

  src/
    Auth.php             # admin authentication & session
    models/
      ProductModel.php
      CategoryModel.php
      UserModel.php
      OrderModel.php

  sql/
    01_schema.sql        # database schema (tables, constraints)
    02_seed.sql          # initial data (categories, products, admin)

front/
  public/
    index.php            # home / catalog
    product.php          # product detail
    cart.php             # cart view
    checkout.php         # checkout form
    api-proxy.php        # PHP proxy to call back-end API
  assets/
    css/
      styles.css         # global styles
    js/
      app.js             # front logic (catalog, cart, checkout)
    img/
      ...                # product images, placeholders, logo, etc.
```
---
## 4. Database

### 4.1. Schema

The database schema is defined in `back/sql/01_schema.sql`.  
Main tables:

- `users`:
    - `id`, `nom`, `prenom`, `email`, `password`, `role`
- `categories`:
    - `id`, `nom`
- `products`:
    - `id`, `nom`, `description`, `prix`, `stock`, `image`, `category_id`
    - `is_customizable` (TINYINT(1)) → whether the product is customizable
    - `custom_config` (TEXT, JSON) → customization options (cap/body/mine)
- `orders`:
    - `id`, `date`, `total`, `statut`, `user_id`
- `order_items`:
    - `id`, `order_id`, `product_id`, `quantite`, `prix_achat`
    - `options` (TEXT, JSON) → selected options for customizable products

Relations are enforced with foreign keys (`orders.user_id`, `order_items.order_id`, `order_items.product_id`)
and constraints (`NOT NULL`, `UNIQUE`, etc.).

### 4.2. Seed data

`back/sql/02_seed.sql` contains initial data:

- sample categories (mugs, t-shirts, tote bags, pens),
- sample products (including the customizable pen),
- an admin user,
- and possibly a few example orders (optional).
---
## 5. Installation & local setup

### 5.1. Requirements

- PHP 8.x
- MySQL / MariaDB
- Apache (or Nginx) with virtual hosts
- phpMyAdmin or CLI access to MySQL
- (Optional) Composer, if you want to extend the project later

### 5.2. Steps

1. **Clone the repository**

   ```bash
   git clone <repo-url> awesome-ride
   cd awesome-ride
    ```
2. **Set up the database**
   Create a database (for example awesome_ride) and a MySQL user with permissions on it.
3. Run schema and seed scripts:

   ```bash
   SOURCE back/sql/01_schema.sql
   SOURCE -u your_user -p awesome_ride < back/sql/02_seed.sql
   ```
4. **Configure database connection**
Edit `back/config/database.php` to match your environment:
    ```php 
   class Database
   {
   public static function getConnection(): PDO
   {
   $dsn = 'mysql:host=localhost;dbname=awesome_ride;charset=utf8mb4';
   $user = 'your_user';
   $password = 'your_password';
   
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return $pdo;
    }
   }
    ```
5. **Configure virtual hosts / document roots**
- Point a host (e.g., `ecom.awesome-ride.local`) to `front/public/` for the front shop.
- Point another host (e.g., `awesome-ride.local`) to `back/public/` for the admin back-office.
Update your `/etc/hosts` file if needed.
6. **Test the installation**
- Back/API:
  - http://awesome-ride.local/api → should return JSON health status.
  - http://awesome-ride.local/login.php → admin login page.
- Front:
  - http://ecom.awesome-ride.local/ → catalog home page.
  - http://ecom.awesome-ride.local/product.php?id=1 → product detail page.
---
## 6. API

The API is accessible under `/api` on the back domain.

### 6.1. Endpoints

- `GET /api`  
  Returns basic health info:

  ```json
  { "status": "ok", "message": "API Awesome Ride" }
  ```
- `GET /api/products`  
  Returns a list of products:
```
[
  {
    "id": 1,
    "nom": "Custom pen",
    "description": "A customizable pen",
    "prix": 9.90,
    "stock": 100,
    "image": "/images/pen.png",
    "category_name": "Pens",
    "is_customizable": 1,
    "custom_config": "{\"cap\":[\"blue\",\"black\"],\"body\":[\"white\",\"black\"],\"mine\":[\"fine\",\"medium\"]}"
  }
]
```
- `GET /api/products/{id}`  
  Returns details of a specific product, or 404 with:
    ```json
    { "error": "Product not found" }
    ```
- `POST /api/orders`
Expects a JSON payload:
```json
{
  "nom": "Doe",
  "prenom": "John",
  "email": "john.doe@example.com",
  "adresse": "1 rue de la Paix, 75000 Paris",
  "items": [
    {
      "id": 1,
      "nom": "Custom pen (blue)",
      "prix": 9.9,
      "quantite": 2,
      "options": {
        "cap": "noir",
        "body": "blanc",
        "mine": "fine"
      }
    }
  ]
}
```
Server-side:
- validates payload (mandatory fields, valid items),
- recomputes totals from DB prices,
- creates or reuses the user (based on email),
- inserts an order + order_items,
- updates product stock.
Response on success:
```json
{ "status": "success", "order_id": 123 }
```
---
## 7. Front-end behavior

### 7.1. Catalog & product page

- `front/public/index.php`:
  - calls the API (via `api-proxy.php`) to fetch products,
  - renders each product as a card (image, name, price, actions).
- `front/public/product.php`:
  - fetches a single product by ID,
  - if `is_customizable` is true:
    - reads `custom_config` (JSON) for available options,
    - builds `<select>` lists for cap/body/mine,
  - adds the configured product to the cart.

### 7.2. Cart

Cart logic is handled in `front/assets/js/app.js`:

- Cart is stored in `localStorage` under a dedicated key.
- Each cart line includes:
  - `id`, `nom`, `prix`, `quantite`, `options`.
- Options are displayed in a human-readable format, for example:

  ```text
  Cap: Black, Body: White, Tip: Fine
  ```
### 7.3. Checkout
- front/public/checkout.php displays a form for customer data.
- app.js collects:
  - customer fields (name, email, address),
  - cart content.
- It posts to api-proxy.php?endpoint=orders, which forwards to POST /api/orders.
On success:
- clears the cart,
- shows a confirmation message to the user.

---

## 8. Back-office

### 8.1. Login & Auth

- `back/public/login.php`:
  - simple login form (email, password),
  - checks credentials against the `users` table,
  - stores admin user ID in session.
- `back/src/Auth.php`:
  - `Auth::requireAdmin()` is used on all `/admin/...` pages
    to restrict access to authenticated admins.

### 8.2. Products

- `admin/products.php`:
  - lists all products (name, category, price, stock, customizable flag).
- `admin/product-edit.php`:
  - create or update a product:
    - fields: name, description, price, stock, image, category,
    - checkbox **“Customizable product (pen)”**,
    - text fields for cap/body/mine values (comma-separated),
    - data stored into:
      - `is_customizable` (0/1),
      - `custom_config` (JSON).

### 8.3. Stocks

- `admin/stocks.php`:
  - lists products with current stock and an input for the new stock value,
  - a single form allows updating several stocks at once.

### 8.4. Orders

- `admin/orders.php`:
  - lists all orders with ID, date, customer name, total, status.
- `admin/order-view.php`:
  - shows order details:
    - customer info,
    - lines with:
      - product name,
      - quantity,
      - unit price,
      - line total,
      - **customization options** for pens (Cap / Body / Tip),
    - status change form (new, in preparation, shipped, canceled).
---
## 9. Testing

Testing has been mostly manual, focusing on the main user flows:

- catalog loading,
- cart operations,
- checkout submission,
- order creation in the database,
- back-office listing and detail views,
- order status changes.

Main test cases include:

- **Front / Catalog**
    - Home with and without products (empty state message vs product grid).
    - Handling of API errors (wrong URL, server not responding).

- **Front / Cart & Checkout**
    - Add, update quantity and remove items in the cart.
    - Checkout validation (empty fields, invalid email).
    - Checkout with an empty cart (form hidden, message shown).

- **API**
    - `GET /api`, `GET /api/products`, `GET /api/products/{id}` with valid and invalid IDs.
    - `POST /api/orders` with:
        - invalid JSON (missing fields) → error,
        - valid JSON → order created with correct totals and stock update.

- **Back-office**
    - Invalid/valid login.
    - Product creation/editing (including customizable products).
    - Bulk stock updates.
    - Order display and status changes.

A possible next step would be:

- adding PHPUnit tests for models and order creation logic,
- adding automated API tests with Postman / Newman (or similar).
---
## 10. Security & quality

Key points:

- **SQL injection**
    - all queries use PDO prepared statements.
- **Data validation**
    - order payload is validated server-side,
    - totals are recomputed from database prices rather than trusting client values.
- **Authentication**
    - admin area protected by login and PHP sessions.
- **XSS**
    - HTML output is escaped with `htmlspecialchars()` in admin views
      and in key front views.
- **CORS**
    - solved using a **PHP proxy** (`api-proxy.php`) on the front domain:
        - front only calls its own domain,
        - the proxy calls the back API via cURL.

Code is structured to be maintainable and extensible, with clear separation between:

- routing / views in `public/`,
- business logic in `src/`,
- database structure in `sql/`.

---
## 11. Possible improvements

Some ideas for future versions:

- **Product attributes & combinations**
    - generic attribute/value system,
    - stock and price per combination (similar to PrestaShop).
- **Customer account area**
    - order history,
    - saved addresses.
- **Automated testing**
    - PHPUnit unit tests for models and business rules,
    - automated API tests (Postman / Newman, etc.).
- **Accessibility**
    - full WCAG/RGAA audit,
    - improvements for keyboard navigation, color contrast,
      and screen reader announcements.
- **DevOps**
    - CI/CD pipeline with automatic tests before deployment,
    - better logging and monitoring on production.

---
## 12. License

This project has been developed as part of a training course.

It can be reused and adapted for educational purposes or as a base
for similar e-commerce / back-office exercises.
