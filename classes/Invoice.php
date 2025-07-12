

<?php
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;

class Invoice {
    public function generatePDF($order, $products, $user) {
        $dompdf = new Dompdf();
        $html = "<h1>Wemart Invoice</h1>";
        $html .= "<p><strong>Name:</strong> " . htmlspecialchars($user['name']) . "</p>";
        $html .= "<p><strong>Address:</strong> " . htmlspecialchars($user['address']) . "</p>";
        $html .= "<table border='1'><tr><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr>";
        foreach ($products as $item) {
            $html .= "<tr><td>" . htmlspecialchars($item['name']) . "</td><td>" . $item['quantity'] . "</td><td>$" . number_format($item['price'], 2) . "</td><td>$" . number_format($item['price'] * $item['quantity'], 2) . "</td></tr>";
        }
        $html .= "</table>";
        $html .= "<p><strong>Total Amount:</strong> $" . number_format($order['total_amount'], 2) . "</p>";
        $html .= "<p><strong>Order Date:</strong> " . $order['order_date'] . "</p>";
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("invoice_{$order['order_id']}.pdf", ["Attachment" => true]);
    }
}
?>

