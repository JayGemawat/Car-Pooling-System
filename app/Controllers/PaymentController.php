<?php

class PaymentController
{
    public function createOrder(): void
    {
        AuthMiddleware::require();
        AuthMiddleware::verifyCsrf();
        header('Content-Type: application/json');

        $cid   = (int) ($_POST['cid'] ?? 0);
        $offer = Offer::findById($cid);
        if (!$offer) {
            echo json_encode(['error' => 'Ride not found']);
            return;
        }

        // Prevent the ride owner from paying themselves
        if ((int)$offer['uid'] === AuthMiddleware::userId()) {
            echo json_encode(['error' => 'You cannot pay for your own ride']);
            return;
        }

        $amount    = (int) $offer['price'] * 100; // paise
        $currency  = $_ENV['RAZORPAY_CURRENCY'] ?? 'INR';
        $keyId     = $_ENV['RAZORPAY_KEY_ID']     ?? '';
        $keySecret = $_ENV['RAZORPAY_KEY_SECRET']  ?? '';

        $data = [
            'amount'   => $amount,
            'currency' => $currency,
            'receipt'  => 'ride_' . $cid . '_' . time(),
        ];

        $ch = curl_init('https://api.razorpay.com/v1/orders');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_USERPWD        => "$keyId:$keySecret",
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $order = json_decode($response, true);
        echo json_encode([
            'order_id' => $order['id'] ?? '',
            'amount'   => $amount,
            'currency' => $currency,
            'key_id'   => $keyId,
        ]);
    }

    public function verifyPayment(): void
    {
        AuthMiddleware::require();
        AuthMiddleware::verifyCsrf();
        header('Content-Type: application/json');

        $paymentId = $_POST['razorpay_payment_id'] ?? '';
        $orderId   = $_POST['razorpay_order_id']   ?? '';
        $signature = $_POST['razorpay_signature']  ?? '';
        $cid       = (int) ($_POST['cid'] ?? 0);

        $expectedSig = hash_hmac('sha256', $orderId . '|' . $paymentId, $_ENV['RAZORPAY_KEY_SECRET'] ?? '');

        if (!hash_equals($expectedSig, $signature)) {
            http_response_code(400);
            echo json_encode(['error' => 'Payment verification failed']);
            return;
        }

        $offer = Offer::findById($cid);
        if ($offer) {
            Offer::update($cid, array_merge($offer, ['status' => 'confirmed']));
        }

        echo json_encode(['success' => true, 'payment_id' => $paymentId]);
    }
}
