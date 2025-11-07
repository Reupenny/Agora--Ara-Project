# Project Improvement TODO List

This document outlines a series of tasks to improve the consistency, maintainability, and architecture of the Agora website codebase.

---

## 1. Architectural & Structural Improvements

### 1.1. Standardize Naming Conventions
- **Task:** Enforce a consistent naming convention for all files and classes.
- **Issue:** There are multiple naming conventions in use. For example, in `controllers`, we have `productController.php` (camelCase) and `businessmanageController.php` (lowercase).
- **Recommendation:** Choose one convention and apply it everywhere. **PascalCase** for class names and filenames is a common PHP standard (e.g., `ProductController.php`, `BusinessManageController.php`).
- **Files to Rename (Examples):**
  - `adminpanelController.php` -> `AdminPanelController.php`
  - `businessmanageController.php` -> `BusinessManageController.php`
  - `sellerorderController.php` -> `SellerOrderController.php`
  - `productaddController.php` -> `ProductAddController.php`
  - All other lowercase controllers and associated model/view files.

### 1.2. Refine MVC Implementation
- **Task:** Strengthen the separation of concerns between Model, View, and Controller.
- **Issue:** Views currently read directly from the filesystem (`file_get_contents('html/...')`). This tightly couples the View logic to the file structure and mixes presentation logic with file I/O.
- **Recommendation:** Introduce a dedicated "Template Engine" or "Renderer" class, or move rendering logic into the `AbstractView`. The `AbstractView` should be solely responsible for all `file_get_contents()` calls and template rendering, as demonstrated in `example 1`. Child views should only be responsible for setting template variables.

### 1.3. Implement Dependency Injection (DI)
- **Task:** Refactor core classes to use Dependency Injection for managing dependencies like the database.
- **Issue:** Controllers and Models fetch the database connection via `$this->getDB()`, which likely retrieves a global or inherited instance. This makes testing difficult and hides class dependencies.
- **Recommendation:**
  1.  "Inject" the database object into the constructor of classes that need it (e.g., `new ProductModel($db)`).
  2.  Update the front controller (`index.php`) to create the database instance once and pass it down to the controllers and models.

### 1.4. Streamline Front Controller
- **Task:** Refactor `index.php` to be a clean, single-responsibility entry point.
- **Issue:** The current `index.php` handles initialization, routing, and execution. It could be more streamlined.
- **Recommendation:** Model `index.php` after `example 1/website.php`. Its sole responsibilities should be:
  1.  Initialize the environment (constants, error handling, session).
  2.  Instantiate the database and context.
  3.  Run the router to get the correct controller.
  4.  Execute the controller and render the view.
  5.  Include a global `try...catch` block for robust error handling.

---

## 2. Code Quality & Consistency

### 2.1. Centralize and Explicitly Define Routing
- **Task:** Consolidate URI parsing and controller mapping into a dedicated, explicit Router.
- **Issue:** The main `index.php` file handles routing logic implicitly by converting the URI to a class name. This is fragile and not easily discoverable.
- **Recommendation:** Create a `Router` class or implement a routing map within `index.php` as seen in `example 1/website.php`. Use an explicit `switch` statement or an array to map URL patterns to controller classes. This makes routes easy to find and manage.

### 2.2. Improve User Session Management
- **Task:** Refactor the `User` object handling.
- **Issue:** The `User` object is created and managed within the `Context` class, and its state (logged-in or not) is determined inside the `User` constructor.
- **Recommendation:** Create a dedicated `Auth` or `AuthenticationService` class. This class would be responsible for checking session data, logging users in/out, and providing the current `User` object. This separates authentication logic from the general application context.
- **Files to Rename (Examples):**
  - `adminpanelController.php` -> `AdminPanelController.php`
  - `businessmanageController.php` -> `BusinessManageController.php`
  - `sellerorderController.php` -> `SellerOrderController.php`
  - `productaddController.php` -> `ProductAddController.php`
  - All other lowercase controllers and associated model/view files.

### 1.2. Refine MVC Implementation
- **Task:** Strengthen the separation of concerns between Model, View, and Controller.
- **Issue:** Views currently read directly from the filesystem (`file_get_contents('html/...')`). This tightly couples the View logic to the file structure and mixes presentation logic with file I/O.
- **Recommendation:** Introduce a dedicated "Template Engine" or "Renderer" class, or move rendering logic into the `AbstractView`. The `AbstractView` should be solely responsible for all `file_get_contents()` calls and template rendering, as demonstrated in `example 1`. Child views should only be responsible for setting template variables.

### 1.3. Implement Dependency Injection (DI)
- **Task:** Refactor core classes to use Dependency Injection for managing dependencies like the database.
- **Issue:** Controllers and Models fetch the database connection via `$this->getDB()`, which likely retrieves a global or inherited instance. This makes testing difficult and hides class dependencies.
- **Recommendation:**
  1.  "Inject" the database object into the constructor of classes that need it (e.g., `new ProductModel($db)`).
  2.  Update the front controller (`index.php`) to create the database instance once and pass it down to the controllers and models.

### 1.4. Streamline Front Controller
- **Task:** Refactor `index.php` to be a clean, single-responsibility entry point.
- **Issue:** The current `index.php` handles initialization, routing, and execution. It could be more streamlined.
- **Recommendation:** Model `index.php` after `example 1/website.php`. Its sole responsibilities should be:
  1.  Initialize the environment (constants, error handling, session).
  2.  Instantiate the database and context.
  3.  Run the router to get the correct controller.
  4.  Execute the controller and render the view.
  5.  Include a global `try...catch` block for robust error handling.

---

## 2. Code Quality & Consistency

### 2.1. Centralize and Explicitly Define Routing
- **Task:** Consolidate URI parsing and controller mapping into a dedicated, explicit Router.
- **Issue:** The main `index.php` file handles routing logic implicitly by converting the URI to a class name. This is fragile and not easily discoverable.
- **Recommendation:** Create a `Router` class or implement a routing map within `index.php` as seen in `example 1/website.php`. Use an explicit `switch` statement or an array to map URL patterns to controller classes. This makes routes easy to find and manage.

### 2.2. Improve User Session Management
- **Task:** Refactor the `User` object handling.
- **Issue:** The `User` object is created and managed within the `Context` class, and its state (logged-in or not) is determined inside the `User` constructor.
- **Recommendation:** Create a dedicated `Auth` or `AuthenticationService` class. This class would be responsible for checking session data, logging users in/out, and providing the current `User` object. This separates authentication logic from the general application context.

### 2.3. Consolidate Model Logic
- **Task:** Clarify the roles of different model types.
- **Issue:** The `models/` directory contains data-centric models (`product.php`), manager classes (`productManager.php`), and models that seem tied to specific pages (`home.php`, `login.php`).
- **Recommendation:**
  1.  **Entity Models:** Pure data objects that represent a database table (e.g., `Product`, `Business`). They should only contain getters and setters.
  2.  **Manager/Repository/Service Classes:** Handle all database interactions and business logic (e.g., `ProductManager`, `OrderManager`). They should return Entity Models.
  3.  **Page/View Models:** Models that are created specifically for a view and contain the data needed for that page (`HomeViewModel`, `ProfileViewModel`). These are often composed of data from multiple Entity models.

---

## 3. Security & Error Handling

### 3.1. Centralize Error Handling
- **Task:** Create a global error and exception handler.
- **Issue:** Error handling appears to be done on a case-by-case basis within controllers. There is no single point to catch uncaught exceptions or errors, which could lead to inconsistent error pages or expose sensitive information.
- **Recommendation:** Use `set_exception_handler()` and `set_error_handler()` in `index.php` to catch all errors and exceptions. This handler should log the error and display a user-friendly error page (e.g., from `html/error/`).

### 3.2. Review and Secure File Paths
- **Task:** Define a secure base path constant for file includes.
- **Issue:** `include` and `file_get_contents` use relative paths. This can be fragile and potentially insecure if the script's working directory changes.
- **Recommendation:** In `index.php`, define a global constant like `define('BASE_PATH', __DIR__);` and use it for all file includes and requires (e.g., `include BASE_PATH . '/lib/database.php';`).

---

## 4. Testing & Validation

### 4.1. Implement Unit Testing
- **Task:** Create and run unit tests for core library components.
- **Issue:** The `unitTests/` directory is empty. Core functionality like database connections, session handling, and URI parsing is untested.
- **Recommendation:** Create unit tests for `database.php`, `session.php`, and `uri.php`. Follow the structure in `example 1/unitTests` and use the `runtests.php` script to execute them. This will improve code quality and prevent regressions.


---

## 2. Code Quality & Consistency

### 2.1. Centralize Routing Logic
- **Task:** Consolidate the URI parsing and controller/action mapping into a dedicated Router class.
- **Issue:** The main `index.php` file handles routing logic directly. As the application grows, this file will become bloated and hard to manage.
- **Recommendation:** Create a `Router` class in `lib/` that takes the URI, matches it against a defined set of routes (e.g., an array mapping paths to controllers), and returns the appropriate controller class name. `index.php` would then simply instantiate and run this router.

### 2.2. Improve User Session Management
- **Task:** Refactor the `User` object handling.
- **Issue:** The `User` object is created and managed within the `Context` class, and its state (logged-in or not) is determined inside the `User` constructor.
- **Recommendation:** Create a dedicated `Auth` or `AuthenticationService` class. This class would be responsible for checking session data, logging users in/out, and providing the current `User` object. This separates authentication logic from the general application context.

### 2.3. Consolidate Model Logic
- **Task:** Clarify the roles of different model types.
- **Issue:** The `models/` directory contains data-centric models (`product.php`), manager classes (`productManager.php`), and models that seem tied to specific pages (`home.php`, `login.php`).
- **Recommendation:**
  1.  **Entity Models:** Pure data objects that represent a database table (e.g., `Product`, `Business`). They should only contain getters and setters.
  2.  **Manager/Repository/Service Classes:** Handle all database interactions and business logic (e.g., `ProductManager`, `OrderManager`). They should return Entity Models.
  3.  **Page/View Models:** Models that are created specifically for a view and contain the data needed for that page (`HomeViewModel`, `ProfileViewModel`). These are often composed of data from multiple Entity models.

---

## 3. Security & Error Handling

### 3.1. Centralize Error Handling
- **Task:** Create a global error and exception handler.
- **Issue:** Error handling appears to be done on a case-by-case basis within controllers. There is no single point to catch uncaught exceptions or errors, which could lead to inconsistent error pages or expose sensitive information.
- **Recommendation:** Use `set_exception_handler()` and `set_error_handler()` in `index.php` to catch all errors and exceptions. This handler should log the error and display a user-friendly error page (e.g., from `html/error/`).

### 3.2. Review and Secure File Paths
- **Task:** Define a secure base path constant for file includes.
- **Issue:** `include` and `file_get_contents` use relative paths. This can be fragile and potentially insecure if the script's working directory changes.
- **Recommendation:** In `index.php`, define a global constant like `define('BASE_PATH', __DIR__);` and use it for all file includes and requires (e.g., `include BASE_PATH . '/lib/database.php';`).
