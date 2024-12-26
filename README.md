## Eshk\Crud: A PDO-based CRUD Class

This class provides a simple and robust way to perform Create, Read, Update, and Delete (CRUD) operations on your database tables using PHP Data Objects (PDO).

**Installation**

There's no specific installation required for this class. You can simply copy the `CRUD.php` file into your project directory.

**Requirements**

- PHP 7.4 or later
- A database connection established using PDO

**Usage**

1. **Initialization:**

   - **Direct PDO Connection:**

   ```php
   require_once 'CRUD.php';

   $pdo = new PDO('mysql:host=localhost;dbname=your_database', 'username', 'password');

   $crud = new Eshk\Crud('your_table_name', 'id', $pdo);
   ```

   - **Separate Connection Configuration:**

   ```php
   require_once 'CRUD.php';

   // Connection configuration (in a separate file, e.g., config.php)
   $dbConfig = [
       'host' => 'localhost',
       'dbname' => 'your_database',
       'username' => 'username',
       'password' => 'password',
   ];

   // Connection establishment (in your code)
   $pdo = new PDO(
       "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}",
       $dbConfig['username'],
       $dbConfig['password']
   );

   CRUD::init($pdo);

   $crud = new Eshk\Crud('your_table_name', 'id');
   ```

2. **CRUD Operations:**

   **Create:**

   ```php
   $data = [
       'name' => 'John Doe',
       'email' => 'john.doe@example.com',
   ];

   $result = $crud->create($data); // result will hold the lastInsertId or false

   if ($result) {
       echo "Record created successfully with ID: $lastInsertId";
   } else {
       echo "Error creating record: " . $crud->getLastError();
   }
   ```

   **Read:**

   ```php
   // Read all records
   $records = $crud->read();
   if ($records) {
       print_r($records);
   } else {
       echo "No records found";
   }

   // Read specific record by ID
   $id = 1;
   $record = $crud->read($id);
   if ($record) {
       print_r($record);
   } else {
       echo "Record not found";
   }
   ```

   **Update:**

   ```php
   $id = 2;
   $data = [
       'name' => 'Jane Smith',
       'email' => 'jane.smith@example.com',
   ];

   $result = $crud->update($id, $data);

   if ($result) {
       echo "Record updated successfully";
   } else {
       echo "Error updating record: " . $crud->getLastError();
   }
   ```

   **Delete:** (**Caution:** Use with care!)

   ```php
   $id = 3;

   $result = $crud->delete($id);

   if ($result) {
       echo "Record deleted successfully";
   } else {
       echo "Error deleting record: " . $crud->getLastError();
   }
   ```

   **Refer (Related Records):**

   ```php
   $relatedCrud = $crud->refer('related_table', 'foreign_key_field');

   // Use the relatedCrud object to perform operations on the related table
   ```

**Error Handling**

The `CRUD` class stores the last error message in the `$lastError` property. You can access it using `$crud->getLastError()` after any operation that might fail.

**Additional Notes**

- Consider security measures when working with user input to prevent SQL injection vulnerabilities. Use prepared statements with parameter binding.
- The `refer` method provides a basic example for fetching related records. You can extend it to handle more complex relationships.
- Error handling can be further enhanced with custom exception classes or comprehensive logging mechanisms.

**Example Usage: Complete Script**

```php
// require_once 'CRUD.php';
use Eshk\CRUD;

// Configuração da conexão PDO (substitua com suas informações)
$pdo = new PDO('mysql:host=localhost;dbname=minha_base', 'usuario', 'senha');

CRUD::init($pdo);

// Criando instâncias para as tabelas 'usuarios' e 'roles'
$crudUsuario = new CRUD('usuarios');
$crudRole = new CRUD('roles');

// Criando um novo usuário e atribuindo uma role
$novoUsuarioId = $crudUsuario->create([
    'nome' => 'Maria',
    'email' => 'maria@example.com',
    'senha' => password_hash('senha123', PASSWORD_DEFAULT)
]);

$novaRoleId = $crudRole->create(['nome' => 'admin']);

$crudUsuario->refer('usuarios_roles', 'usuario_id')->create([
    'usuario_id' => $novoUsuarioId,
    'role_id' => $novaRoleId
]);

$mariaRole = $crudUsuario->refer('user_role', 'id')->get($novoUsuario);


```