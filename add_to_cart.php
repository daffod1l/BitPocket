<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$showModal = false;

if(isset($_POST['add_to_cart'])) {
    $id = $_GET['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $class_name = $_POST['class_name'];

    $item = array(
        'id' => $id,
        'name' => $name,
        'price' => $price,
        'quantity' => $quantity,
        'class_name' => $class_name
    );

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    $ids = array_column($_SESSION['cart'], 'id');

    if (!in_array($id, $ids)) {
        $_SESSION['cart'][] = $item;
    } else {
        // Optional: update quantity if item already exists
        foreach ($_SESSION['cart'] as &$cart_item) {
            if ($cart_item['id'] == $id) {
                $cart_item['quantity'] += $quantity;
                break;
            }
        }
    }

    $showModal = true;
}
?>

<!-- Modal HTML (include this at the bottom of your page) -->
<?php if ($showModal): ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Item Added</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body>
        <!-- Trigger the modal -->
        <script>
            window.onload = function () {
                var addModal = new bootstrap.Modal(document.getElementById('addedModal'));
                addModal.show();
            };
        </script>

        <!-- Modal structure -->
        <div class="modal fade" id="addedModal" tabindex="-1" aria-labelledby="addedModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addedModalLabel">Item Added</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        The item "<strong><?= htmlspecialchars($name) ?></strong>" has been added to your cart!
                    </div>
                    <div class="modal-footer">
                        <a href="cart.php" class="btn btn-primary">Go to Cart</a>
                        <a href="javascript:history.back()" class="btn btn-secondary">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
<?php endif; ?>
