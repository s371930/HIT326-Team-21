// 1. Get the instance — anywhere in the codebase
require_once __DIR__ . '/../core/Database.php';
$db = Database::getInstance();

// 2. SELECT many
$products = $db->fetchAll(
    "SELECT * FROM product WHERE is_available = ?",
    [1]
);

// 3. SELECT one
$admin = $db->fetchOne(
    "SELECT * FROM admin WHERE username = ?",
    [$username]
);
if ($admin === null) {
    // not found
}

// 4. INSERT
$db->execute(
    "INSERT INTO customer (email, first_name, last_name)
     VALUES (?, ?, ?)",
    [$email, $first, $last]
);
$newCustomerId = (int) $db->lastInsertId();

// 5. Transaction for multi-step writes (Romik will need this)
$db->beginTransaction();
try {
    $db->execute("INSERT INTO purchase ...", [...]);
    $purchaseId = (int) $db->lastInsertId();
    foreach ($items as $item) {
        $db->execute("INSERT INTO purchase_item ...", [...]);
    }
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    throw $e;
}