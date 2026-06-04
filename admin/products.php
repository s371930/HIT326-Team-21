<?php
/**
 * admin/products.php
 *
 * Product management page — allows the admin to view, add, edit,
 * and soft-delete products from the store.
 *
 * Soft-delete means setting is_available = 0 rather than removing
 * the row from the database. This is a project requirement.
 *
 * Actions handled on this page:
 *   - Display all products (including unavailable ones)
 *   - Add a new product (POST action=add)
 *   - Edit an existing product (POST action=edit)
 *   - Soft-delete a product (POST action=delete)
 *   - Restore a soft-deleted product (POST action=restore)
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/models/Product.php';

// Start session and protect this page from unauthenticated access
Auth::start();
Auth::requireLogin();

$productModel = new Product();
$db           = Database::getInstance();

// Holds success or error feedback message shown after an action
$message = '';
$msgType = 'success';


// Image upload helper — used by both add and edit actions
// Returns the new filename on success, null if no file uploaded, or
// sets $message and returns false on validation failure.

function handleImageUpload(&$message, &$msgType): string|null|false
{
    // No file was selected — skip upload
    if (empty($_FILES['image']['name'])) {
        return null;
    }

    $uploadDir = __DIR__ . '/../assets/images/';
    $ext       = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed   = ['jpg', 'jpeg', 'png'];

    // Validate file type
    if (!in_array($ext, $allowed)) {
        $message = 'Only JPG and PNG images are allowed.';
        $msgType = 'danger';
        return false;
    }

    // Validate file size — max 2MB
    if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
        $message = 'Image must be under 2MB.';
        $msgType = 'danger';
        return false;
    }

    // Generate a unique filename to avoid overwriting existing images
    $filename = uniqid('product_', true) . '.' . $ext;
    move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
    return $filename;
}


// Handle form submissions (POST requests)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- ADD a new product ---
    if ($action === 'add') {
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = $_POST['price'] ?? '';
        $category    = trim($_POST['category'] ?? '');
        $color       = trim($_POST['color'] ?? '');
        $size        = trim($_POST['size'] ?? '');

        if ($name === '' || $price === '' || $description === '') {
            $message = 'Name, description and price are required.';
            $msgType = 'danger';
        } else {
            // Handle image upload — returns filename, null (no file), or false (error)
            $imageFilename = handleImageUpload($message, $msgType);

            if ($imageFilename !== false) {
                // Insert the new product including image filename
                $db->execute(
                    "INSERT INTO product (name, description, price, category, color, size, image_filename, is_available, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())",
                    [$name, $description, (float)$price, $category, $color, $size, $imageFilename]
                );
                $message = 'Product added successfully.';
            }
        }
    }

    // --- EDIT an existing product ---
    if ($action === 'edit') {
        $productId   = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = $_POST['price'] ?? '';
        $category    = trim($_POST['category'] ?? '');
        $color       = trim($_POST['color'] ?? '');
        $size        = trim($_POST['size'] ?? '');

        if (!$productId || $name === '' || $price === '') {
            $message = 'Invalid product data. Please try again.';
            $msgType = 'danger';
        } else {
            // Keep existing image by default — only replace if a new one is uploaded
            $existingImage = $_POST['existing_image'] ?? null;
            $uploadResult  = handleImageUpload($message, $msgType);
            $imageFilename = ($uploadResult !== null && $uploadResult !== false)
                ? $uploadResult
                : $existingImage;

            if ($uploadResult !== false) {
                $db->execute(
                    "UPDATE product SET name = ?, description = ?, price = ?, category = ?,
                     color = ?, size = ?, image_filename = ?
                     WHERE product_id = ?",
                    [$name, $description, (float)$price, $category, $color, $size, $imageFilename, $productId]
                );
                $message = 'Product updated successfully.';
            }
        }
    }

    // --- SOFT-DELETE a product ---
    // Sets is_available = 0 instead of deleting the row (project requirement)
    if ($action === 'delete') {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if ($productId) {
            $db->execute(
                "UPDATE product SET is_available = 0 WHERE product_id = ?",
                [$productId]
            );
            $message = 'Product removed from the store.';
        }
    }

    // --- RESTORE a soft-deleted product ---
    if ($action === 'restore') {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if ($productId) {
            $db->execute(
                "UPDATE product SET is_available = 1 WHERE product_id = ?",
                [$productId]
            );
            $message = 'Product restored to the store.';
        }
    }
}

// Fetch ALL products including soft-deleted ones so admin can restore them
$allProducts = $db->fetchAll(
    "SELECT product_id, name, description, price, category, color, size, image_filename, is_available
     FROM product
     ORDER BY is_available DESC, created_at DESC"
);

// Fetch the product being edited (if edit button was clicked via GET)
$editProduct = null;
if (isset($_GET['edit'])) {
    $editId      = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    $editProduct = $editId ? $db->fetchOne(
        "SELECT * FROM product WHERE product_id = ?", [$editId]
    ) : null;
}

// Load the shared admin navbar and opening HTML
include __DIR__ . '/admin-header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Manage Products</h2>
        <button class="btn btn-dark" data-bs-toggle="collapse" data-bs-target="#addProductForm">
            + Add Product
        </button>
    </div>

    <!-- Feedback message shown after an add/edit/delete action -->
    <?php if ($message !== ''): ?>
        <div class="alert alert-<?= $msgType ?>">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <!-- ADD PRODUCT FORM                                                     -->
  
    <div class="collapse mb-4" id="addProductForm">
        <div class="card card-body">
            <h5 class="mb-3">Add New Product</h5>
            <!-- enctype required for file uploads -->
            <form method="POST" action="products.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Price ($) *</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Colour</label>
                        <input type="text" name="color" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Size</label>
                        <input type="text" name="size" class="form-control">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png">
                        <div class="form-text">Accepted formats: JPG, PNG. Max 2MB. Leave blank for no image.</div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-dark">Save Product</button>
                        <a href="products.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    
    <!-- EDIT PRODUCT FORM                                                    -->
    
    <?php if ($editProduct): ?>
        <div class="card card-body mb-4 border-warning">
            <h5 class="mb-3">Edit Product — <?= htmlspecialchars($editProduct['name'], ENT_QUOTES, 'UTF-8') ?></h5>
            <!-- enctype required for file uploads -->
            <form method="POST" action="products.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="product_id" value="<?= (int)$editProduct['product_id'] ?>">
                <!-- Pass existing image filename so it is kept if no new image is uploaded -->
                <input type="hidden" name="existing_image" value="<?= htmlspecialchars($editProduct['image_filename'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control" required
                               value="<?= htmlspecialchars($editProduct['name'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Price ($) *</label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" required
                               value="<?= htmlspecialchars($editProduct['price'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control"
                               value="<?= htmlspecialchars($editProduct['category'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control" rows="2" required><?= htmlspecialchars($editProduct['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Colour</label>
                        <input type="text" name="color" class="form-control"
                               value="<?= htmlspecialchars($editProduct['color'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Size</label>
                        <input type="text" name="size" class="form-control"
                               value="<?= htmlspecialchars($editProduct['size'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Product Image</label>
                        <?php if (!empty($editProduct['image_filename'])): ?>
                            <!-- Show current image preview -->
                            <div class="mb-2">
                                <img src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($editProduct['image_filename'], ENT_QUOTES, 'UTF-8') ?>"
                                     alt="Current image" style="max-height: 100px; border-radius: 4px;">
                                <small class="d-block text-muted mt-1">Current: <?= htmlspecialchars($editProduct['image_filename'], ENT_QUOTES, 'UTF-8') ?></small>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png">
                        <div class="form-text">Upload a new image to replace the current one. Leave blank to keep existing.</div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-warning">Update Product</button>
                        <a href="products.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>

    
    <!-- PRODUCTS TABLE                                                       -->
    
    <?php if (empty($allProducts)): ?>
        <div class="alert alert-light border">No products found. Click "+ Add Product" to create one.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th style="width: 200px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allProducts as $product): ?>
                        <tr class="<?= $product['is_available'] ? '' : 'table-secondary text-muted' ?>">
                            <td>
                                <?php if (!empty($product['image_filename'])): ?>
                                    <!-- Show thumbnail in the table -->
                                    <img src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($product['image_filename'], ENT_QUOTES, 'UTF-8') ?>"
                                         alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?>"
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <span class="text-muted small">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($product['category'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>$<?= number_format($product['price'], 2) ?></td>
                            <td><?= htmlspecialchars($product['size'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($product['is_available']): ?>
                                    <span class="badge bg-success">Available</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Hidden</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="products.php?edit=<?= (int)$product['product_id'] ?>"
                                   class="btn btn-sm btn-outline-dark me-1">Edit</a>

                                <?php if ($product['is_available']): ?>
                                    <form method="POST" action="products.php" class="d-inline"
                                          onsubmit="return confirm('Remove this product from the store?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Remove</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="products.php" class="d-inline">
                                        <input type="hidden" name="action" value="restore">
                                        <input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success">Restore</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/admin-footer.php'; ?>