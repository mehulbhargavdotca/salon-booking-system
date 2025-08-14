<?php
$message = '';

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add') {
        $name = sanitize($_POST['name']);
        $note = sanitize($_POST['note']);
        
        $stmt = $pdo->prepare("INSERT INTO categories (name, note) VALUES (?, ?)");
        if ($stmt->execute([$name, $note])) {
            $message = '<div class="alert alert-success">Category added successfully!</div>';
        }
    }
    
    if ($action == 'edit') {
        $id = (int)$_POST['id'];
        $name = sanitize($_POST['name']);
        $note = sanitize($_POST['note']);
        
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, note = ? WHERE id = ?");
        if ($stmt->execute([$name, $note, $id])) {
            $message = '<div class="alert alert-success">Category updated successfully!</div>';
        }
    }
    
    if ($action == 'delete') {
        $id = (int)$_POST['id'];
        
        // Check if category has services
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM services WHERE category_id = ?");
        $stmt->execute([$id]);
        $serviceCount = $stmt->fetch()['count'];
        
        if ($serviceCount > 0) {
            $message = '<div class="alert alert-danger">Cannot delete category with existing services!</div>';
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            if ($stmt->execute([$id])) {
                $message = '<div class="alert alert-success">Category deleted successfully!</div>';
            }
        }
    }
}

// Get all categories
$stmt = $pdo->query("SELECT c.*, COUNT(s.id) as service_count FROM categories c LEFT JOIN services s ON c.id = s.category_id GROUP BY c.id ORDER BY c.name");
$categories = $stmt->fetchAll();
?>

<?php echo $message; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5>Add New Category</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="note" class="form-label">Note</label>
                        <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5>Existing Categories</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Note</th>
                                <th>Services</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?php echo $category['id']; ?></td>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><?php echo htmlspecialchars($category['note']); ?></td>
                                    <td><span class="badge bg-primary"><?php echo $category['service_count']; ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">Edit</button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" <?php echo $category['service_count'] > 0 ? 'disabled' : ''; ?>>Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_note" class="form-label">Note</label>
                        <textarea class="form-control" id="edit_note" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(category) {
    document.getElementById('edit_id').value = category.id;
    document.getElementById('edit_name').value = category.name;
    document.getElementById('edit_note').value = category.note || '';
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}
</script>