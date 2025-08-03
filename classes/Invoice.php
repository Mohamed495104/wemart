<?php
require_once '../vendor/autoload.php';

use \Mpdf\Mpdf;

class Invoice
{
    public function generatePDF($order, $products, $user)
    {
        $html = "<!DOCTYPE html><html><head><style>";
        $html .= "body { font-family: Arial, sans-serif; margin: 20px; }";
        $html .= "h1 { color: #333; text-align: center; }";
        $html .= "table { width: 100%; border-collapse: collapse; margin: 20px 0; }";
        $html .= "th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }";
        $html .= "th { background-color: #f2f2f2; }";
        $html .= ".total { font-weight: bold; font-size: 18px; }";
        $html .= "</style></head><body>";

        $html .= "<h1>Wemart Invoice</h1>";
        $html .= "<p><strong>Order ID:</strong> #" . htmlspecialchars($order['order_id']) . "</p>";
        $html .= "<p><strong>Order Date:</strong> " . htmlspecialchars($order['order_date']) . "</p>";
        $html .= "<p><strong>Customer Name:</strong> " . htmlspecialchars($user['name']) . "</p>";

        if (!empty($user['address'])) {
            $html .= "<p><strong>Address:</strong> " . htmlspecialchars($user['address']) . "</p>";
        }

        $html .= "<h3>Order Details:</h3>";
        $html .= "<table>";
        $html .= "<tr><th>Product</th><th>Quantity</th><th>Price</th><th>Total</th></tr>";

        foreach ($products as $item) {
            $html .= "<tr>";
            $html .= "<td>" . htmlspecialchars($item['name']) . "</td>";
            $html .= "<td>" . $item['quantity'] . "</td>";
            $html .= "<td>$" . number_format($item['price'], 2) . "</td>";
            $html .= "<td>$" . number_format($item['price'] * $item['quantity'], 2) . "</td>";
            $html .= "</tr>";
        }

        $html .= "</table>";
        $html .= "<p class='total'>Total Amount: $" . number_format($order['total_amount'], 2) . "</p>";
        $html .= "<p><em>Thank you for shopping with Wemart!</em></p>";
        $html .= "</body></html>";

        // Generate PDF output
        $mpdf = new Mpdf();
        $mpdf->WriteHTML($html);
        $mpdf->Output('invoice_' . $order['order_id'] . '.pdf', 'D');
    }
}
