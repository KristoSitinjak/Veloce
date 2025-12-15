<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

/**
 * Generate unique order number
 */
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Save order to database
 * @param array $orderData - Order header data
 * @param array $items - Cart items
 * @return int|false - Order ID or false on failure
 */
function saveOrder($orderData, $items) {
    global $conn;
    
    if (empty($items)) {
        return false;
    }
    
    $userId = getUserId();
    if (!$userId) {
        return false;
    }
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Generate order number
        $orderNumber = generateOrderNumber();
        
        // Prepare payment details JSON
        $paymentDetails = null;
        if ($orderData['payment_method'] === 'bank_transfer' && !empty($orderData['bank_account'])) {
            $paymentDetails = json_encode(['bank_account' => $orderData['bank_account']]);
        } elseif ($orderData['payment_method'] === 'ewallet' && !empty($orderData['ewallet_number'])) {
            $paymentDetails = json_encode(['ewallet_number' => $orderData['ewallet_number']]);
        }
        
        // Insert order header
        $stmt = mysqli_prepare($conn, "
            INSERT INTO orders (
                user_id, order_number, full_name, phone, address, city, postal_code, 
                notes, delivery_type, payment_method, payment_details, subtotal, shipping_cost, total, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        mysqli_stmt_bind_param($stmt, 'issssssssssddd',
            $userId,
            $orderNumber,
            $orderData['full_name'],
            $orderData['phone'],
            $orderData['address'],
            $orderData['city'],
            $orderData['postal_code'],
            $orderData['notes'],
            $orderData['delivery_type'],
            $orderData['payment_method'],
            $paymentDetails,
            $orderData['subtotal'],
            $orderData['shipping_cost'],
            $orderData['total']
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Failed to insert order');
        }
        
        $orderId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        
        // Insert order items
        $stmt = mysqli_prepare($conn, "
            INSERT INTO order_items (
                order_id, product_category, product_id, product_name, 
                product_image, quantity, price, subtotal
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($items as $item) {
            $itemSubtotal = $item['qty'] * $item['harga'];
            
            mysqli_stmt_bind_param($stmt, 'isissidd',
                $orderId,
                $item['kategori'],
                $item['id'],
                $item['nama'],
                $item['gambar'],
                $item['qty'],
                $item['harga'],
                $itemSubtotal
            );
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Failed to insert order item');
            }
        }
        
        mysqli_stmt_close($stmt);
        
        // Commit transaction
        mysqli_commit($conn);
        
        return $orderId;
        
    } catch (Exception $e) {
        // Rollback on error
        mysqli_rollback($conn);
        error_log('Order save error: ' . $e->getMessage());
        // Temporary: display error for debugging
        $_SESSION['cart_flash'] = [
            'type' => 'error',
            'message' => 'Error: ' . $e->getMessage()
        ];
        return false;
    }
}

/**
 * Get user's order history
 * @param int $userId - User ID
 * @param int $limit - Number of orders to retrieve
 * @return array - Array of orders
 */
function getUserOrders($userId = null, $limit = 1000) {
    global $conn;
    
    if ($userId === null) {
        $userId = getUserId();
    }
    
    if (!$userId) {
        return [];
    }
    
    $stmt = mysqli_prepare($conn, "
        SELECT 
            id, order_number, full_name, phone, address, city, postal_code,
            notes, payment_method, payment_details, subtotal, shipping_cost, 
            total, status, created_at, updated_at
        FROM orders
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ");
    
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $limit);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $orders = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        // Decode payment details
        if ($row['payment_details']) {
            $row['payment_details'] = json_decode($row['payment_details'], true);
        }
        $orders[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    
    return $orders;
}

/**
 * Get order details by ID
 * @param int $orderId - Order ID
 * @return array|null - Order data with items or null if not found
 */
function getOrderById($orderId) {
    global $conn;
    
    $userId = getUserId();
    if (!$userId) {
        return null;
    }
    
    // Get order header
    $stmt = mysqli_prepare($conn, "
        SELECT 
            id, order_number, full_name, phone, address, city, postal_code,
            notes, payment_method, payment_details, subtotal, shipping_cost, 
            total, status, cancellation_requested, cancellation_reason, created_at, updated_at
        FROM orders
        WHERE id = ? AND user_id = ?
    ");
    
    mysqli_stmt_bind_param($stmt, 'ii', $orderId, $userId);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$order) {
        return null;
    }
    
    // Decode payment details
    if ($order['payment_details']) {
        $order['payment_details'] = json_decode($order['payment_details'], true);
    }
    
    // Get order items
    $stmt = mysqli_prepare($conn, "
        SELECT 
            product_category, product_id, product_name, product_image,
            quantity, price, subtotal
        FROM order_items
        WHERE order_id = ?
    ");
    
    mysqli_stmt_bind_param($stmt, 'i', $orderId);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $items = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    
    $order['items'] = $items;
    
    return $order;
}

/**
 * Get order status label in Indonesian
 */
function getOrderStatusLabel($status) {
    $labels = [
        'pending' => 'Menunggu Verifikasi',
        'verified' => 'Terverifikasi',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'delivered' => 'Selesai',
        'cancelled' => 'Dibatalkan'
    ];
    
    return $labels[$status] ?? $status;
}

/**
 * Get payment method label in Indonesian
 */
function getPaymentMethodLabel($method) {
    $labels = [
        'cod' => 'COD (Bayar di Tempat)',
        'bank_transfer' => 'Transfer Bank',
        'ewallet' => 'E-Wallet'
    ];
    
    return $labels[$method] ?? $method;
}

/**
 * Get all orders for admin (with optional filters)
 * @param string $statusFilter - Filter by status
 * @param int $limit - Number of orders to retrieve
 * @return array - Array of orders with user info
 */
function getAllOrders($statusFilter = null, $limit = 100) {
    global $conn;
    
    $query = "
        SELECT 
            o.id, o.order_number, o.full_name, o.phone, o.address, o.city, o.postal_code,
            o.notes, o.payment_method, o.payment_details, o.subtotal, o.shipping_cost, 
            o.total, o.status, o.cancellation_requested, o.cancellation_reason, o.created_at, o.updated_at, o.user_id,
            a.username
        FROM orders o
        LEFT JOIN akun a ON o.user_id = a.id
    ";
    
    if ($statusFilter && $statusFilter !== 'all') {
        $query .= " WHERE o.status = ?";
    }
    
    $query .= " ORDER BY o.created_at DESC LIMIT ?";
    
    $stmt = mysqli_prepare($conn, $query);
    
    if ($statusFilter && $statusFilter !== 'all') {
        mysqli_stmt_bind_param($stmt, 'si', $statusFilter, $limit);
    } else {
        mysqli_stmt_bind_param($stmt, 'i', $limit);
    }
    
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $orders = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        // Decode payment details
        if ($row['payment_details']) {
            $row['payment_details'] = json_decode($row['payment_details'], true);
        }
        $orders[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    
    return $orders;
}

/**
 * Get order details by ID for admin (no user restriction)
 * @param int $orderId - Order ID
 * @return array|null - Order data with items or null if not found
 */
function getOrderByIdAdmin($orderId) {
    global $conn;
    
    // Get order header with user info
    $stmt = mysqli_prepare($conn, "
        SELECT 
            o.id, o.order_number, o.full_name, o.phone, o.address, o.city, o.postal_code,
            o.notes, o.payment_method, o.payment_details, o.subtotal, o.shipping_cost, 
            o.total, o.status, o.cancellation_requested, o.cancellation_reason, o.created_at, o.updated_at, o.user_id,
            a.username
        FROM orders o
        LEFT JOIN akun a ON o.user_id = a.id
        WHERE o.id = ?
    ");
    
    mysqli_stmt_bind_param($stmt, 'i', $orderId);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$order) {
        return null;
    }
    
    // Decode payment details
    if ($order['payment_details']) {
        $order['payment_details'] = json_decode($order['payment_details'], true);
    }
    
    // Get order items
    $stmt = mysqli_prepare($conn, "
        SELECT 
            product_category, product_id, product_name, product_image,
            quantity, price, subtotal
        FROM order_items
        WHERE order_id = ?
    ");
    
    mysqli_stmt_bind_param($stmt, 'i', $orderId);
    mysqli_stmt_execute($stmt);
    
    $result = mysqli_stmt_get_result($stmt);
    $items = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    
    $order['items'] = $items;
    
    return $order;
}

/**
 * Update order status
 * @param int $orderId - Order ID
 * @param string $newStatus - New status
 * @return bool - Success or failure
 */
function updateOrderStatus($orderId, $newStatus) {
    global $conn;
    
    $allowedStatuses = ['pending', 'verified', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (!in_array($newStatus, $allowedStatuses)) {
        return false;
    }
    
    $stmt = mysqli_prepare($conn, "
        UPDATE orders 
        SET status = ?, updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    
    mysqli_stmt_bind_param($stmt, 'si', $newStatus, $orderId);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $success;
}

/**
 * Request order cancellation by user
 * @param int $orderId - Order ID
 * @param string $reason - Cancellation reason
 * @return bool - Success or failure
 */
function requestCancellation($orderId, $reason) {
    global $conn;
    
    $userId = getUserId();
    if (!$userId) {
        return false;
    }
    
    // Check if order belongs to user
    $stmt = mysqli_prepare($conn, "SELECT id FROM orders WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $orderId, $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        mysqli_stmt_close($stmt);
        return false;
    }
    mysqli_stmt_close($stmt);
    
    // Update order with cancellation request
    $stmt = mysqli_prepare($conn, "
        UPDATE orders 
        SET cancellation_requested = TRUE, 
            cancellation_reason = ?,
            updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    
    mysqli_stmt_bind_param($stmt, 'si', $reason, $orderId);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $success;
}

/**
 * Approve cancellation request by admin
 * @param int $orderId - Order ID
 * @return bool - Success or failure
 */
function approveCancellation($orderId) {
    global $conn;
    
    // Update order status to cancelled
    $stmt = mysqli_prepare($conn, "
        UPDATE orders 
        SET status = 'cancelled',
            cancellation_requested = FALSE,
            updated_at = CURRENT_TIMESTAMP 
        WHERE id = ? AND cancellation_requested = TRUE
    ");
    
    mysqli_stmt_bind_param($stmt, 'i', $orderId);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $success;
}

/**
 * Reject cancellation request by admin
 * @param int $orderId - Order ID
 * @return bool - Success or failure
 */
function rejectCancellation($orderId) {
    global $conn;
    
    // Reset cancellation request
    $stmt = mysqli_prepare($conn, "
        UPDATE orders 
        SET cancellation_requested = FALSE,
            cancellation_reason = NULL,
            updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    
    mysqli_stmt_bind_param($stmt, 'i', $orderId);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    return $success;
}
