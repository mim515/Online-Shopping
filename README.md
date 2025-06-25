# 🛍️ Shop Database Project

This project is a SQL-based backend for an e-commerce platform. It manages users, products, carts, orders, wishlists, and admin data. The database is built in **MariaDB** and designed to be used in conjunction with a web application (such as PHP-based platforms).

---

## 📁 Database Name
**`shop_db`**

---

## 🧱 Tables and Structure

### 1. `admins`
Stores admin credentials.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| name | VARCHAR(20) | Admin username |
| password | VARCHAR(50) | Hashed password |

---

### 2. `users`
Stores registered user data.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| name | VARCHAR(20) | User's name |
| email | VARCHAR(50) | User's email |
| password | VARCHAR(50) | Hashed password |

---

### 3. `products`
Details of products listed for sale.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| name | VARCHAR(100) | Product name |
| details | VARCHAR(500) | Description |
| price | INT | Product price |
| image_01 / image_02 / image_03 | VARCHAR(100) | Product images |

---

### 4. `cart`
Contains items added to a user's shopping cart.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| user_id | INT | Linked user ID |
| pid | INT | Product ID |
| name | VARCHAR(100) | Product name |
| price | INT | Price |
| quantity | INT | Quantity selected |
| image | VARCHAR(100) | Product image |

---

### 5. `wishlist`
Stores products a user marked as favorite.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| user_id | INT | Linked user ID |
| pid | INT | Product ID |
| name | VARCHAR(100) | Product name |
| price | INT | Product price |
| image | VARCHAR(100) | Product image |

---

### 6. `orders`
Stores order information placed by users.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| user_id | INT | Linked user ID |
| name | VARCHAR(20) | Customer name |
| number | VARCHAR(10) | Contact number |
| email | VARCHAR(50) | Email |
| method | VARCHAR(50) | Payment method |
| address | VARCHAR(500) | Delivery address |
| total_products | VARCHAR(1000) | List of items |
| total_price | INT | Total amount |
| placed_on | DATE | Order date |
| payment_status | VARCHAR(20) | `pending` / `paid` |

---

### 7. `messages`
Contact messages submitted by users.

| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary Key |
| user_id | INT | Linked user ID (if any) |
| name | VARCHAR(100) | Sender name |
| email | VARCHAR(100) | Sender email |
| number | VARCHAR(14) | Phone number |
| message | VARCHAR(500) | Message content |

---

## 🔐 Notes

- **Password fields** are stored as hashed values for security.
- Images are referenced as filenames, implying integration with a file system or image hosting.
- Admin and user login is separated to maintain different access levels.
- All tables use `utf8mb4` encoding for better multilingual support.

---

## 🛠️ Getting Started

1. **Database Import**
   - Import the `shop_db.sql` file into your MariaDB or MySQL database.
   - Example using command line:
     ```bash
     mysql -u username -p shop_db < shop_db.sql
     ```

2. **Set up your application** (e.g. PHP frontend) to connect with this database structure.

---

## 💡 Example Use Cases

- Admin panel for product & order management.
- User panel for shopping, cart, and checkout.
- Messaging system for customer inquiries.
- Wishlist functionality for user convenience.

---

## 📌 Dependencies

- MariaDB 10.4+
- Compatible with PHPMyAdmin 5.2+
- PHP 8.2+ (for frontend/backend integration)

---


## 📜 License

This project is for academic and personal use. Contact the author for commercial licensing.

