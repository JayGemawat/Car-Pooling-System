<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/Exception.php';
require_once __DIR__ . '/PHPMailer.php';
require_once __DIR__ . '/SMTP.php';

class Mailer
{
    private static function make(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USER'];
        $mail->Password   = $_ENV['MAIL_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int) $_ENV['MAIL_PORT'];
        $mail->setFrom($_ENV['MAIL_USER'], $_ENV['MAIL_FROM_NAME']);
        $mail->isHTML(true);
        return $mail;
    }

    public static function sendWelcome(string $toEmail, string $toName): bool
    {
        try {
            $mail = self::make();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = "Welcome to JaanaHai, $toName!";
            $mail->Body    = self::welcomeTemplate($toName);
            $mail->AltBody = "Welcome to JaanaHai, $toName! Start sharing rides and saving money.";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }

    public static function sendRideConfirmation(
        string $toEmail,
        string $toName,
        array $ride,
    ): bool {
        try {
            $mail = self::make();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = 'Your ride request was sent — JaanaHai';
            $mail->Body    = self::rideConfirmationTemplate($toName, $ride);
            $mail->AltBody = "Hi $toName, your ride request from {$ride['from']} to {$ride['to']} was sent to the driver.";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }

    public static function sendRideRequest(
        string $toEmail,
        string $toName,
        string $riderName,
        array $ride,
    ): bool {
        try {
            $mail = self::make();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = "$riderName wants to join your ride — JaanaHai";
            $mail->Body    = self::rideRequestTemplate($toName, $riderName, $ride);
            $mail->AltBody = "Hi $toName, $riderName wants to join your ride from {$ride['from']} to {$ride['to']}.";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }

    public static function sendRideApproved(
        string $toEmail,
        string $toName,
        array $ride,
    ): bool {
        try {
            $mail = self::make();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = 'Your ride request was approved! 🎉 — JaanaHai';
            $mail->Body    = self::rideApprovedTemplate($toName, $ride);
            $mail->AltBody = "Hi $toName, your ride request from {$ride['from']} to {$ride['to']} was approved!";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }

    public static function sendRideDeclined(
        string $toEmail,
        string $toName,
        array $ride,
    ): bool {
        try {
            $mail = self::make();
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = 'Your ride request was declined — JaanaHai';
            $mail->Body    = self::rideDeclinedTemplate($toName, $ride);
            $mail->AltBody = "Hi $toName, unfortunately your ride request from {$ride['from']} to {$ride['to']} was declined.";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }

    // ── Templates ────────────────────────────────────────────────────────────

    private static function welcomeTemplate(string $name): string
    {
        return "
        <div style='font-family:sans-serif;max-width:520px;margin:auto;padding:24px'>
            <h2 style='color:#1a1a2e'>Welcome to JaanaHai 👋</h2>
            <p>Hi <strong>$name</strong>,</p>
            <p>You're all set! Start sharing rides, save money, and travel smarter.</p>
            <ul>
                <li>Post a ride you're already taking</li>
                <li>Find someone going your way</li>
                <li>Split fuel costs automatically</li>
            </ul>
            <p style='color:#888;font-size:13px'>JaanaHai — Travel together, save together.</p>
        </div>";
    }

    private static function rideConfirmationTemplate(string $name, array $ride): string
    {
        return "
        <div style='font-family:sans-serif;max-width:520px;margin:auto;padding:24px'>
            <h2 style='color:#1a1a2e'>Ride Request Sent ✅</h2>
            <p>Hi <strong>$name</strong>,</p>
            <p>Your ride request has been sent to the driver. They will confirm shortly.</p>
            <div style='background:#f4f4f4;border-radius:8px;padding:16px;margin:16px 0'>
                <p style='margin:4px 0'><strong>From:</strong> {$ride['from']}</p>
                <p style='margin:4px 0'><strong>To:</strong> {$ride['to']}</p>
                <p style='margin:4px 0'><strong>Time:</strong> {$ride['uptime']}</p>
                <p style='margin:4px 0'><strong>Vehicle:</strong> {$ride['vehicle']}</p>
            </div>
            <p style='color:#888;font-size:13px'>JaanaHai — Travel together, save together.</p>
        </div>";
    }

    private static function rideRequestTemplate(
        string $driverName,
        string $riderName,
        array $ride,
    ): string {
        return "
        <div style='font-family:sans-serif;max-width:520px;margin:auto;padding:24px'>
            <h2 style='color:#1a1a2e'>New Ride Request 🚗</h2>
            <p>Hi <strong>$driverName</strong>,</p>
            <p><strong>$riderName</strong> wants to join your ride.</p>
            <div style='background:#f4f4f4;border-radius:8px;padding:16px;margin:16px 0'>
                <p style='margin:4px 0'><strong>From:</strong> {$ride['from']}</p>
                <p style='margin:4px 0'><strong>To:</strong> {$ride['to']}</p>
                <p style='margin:4px 0'><strong>Time:</strong> {$ride['uptime']}</p>
            </div>
            <p>Log in to JaanaHai to accept or reject this request.</p>
            <p style='color:#888;font-size:13px'>JaanaHai — Travel together, save together.</p>
        </div>";
    }

    private static function rideApprovedTemplate(string $name, array $ride): string
    {
        return "
        <div style='font-family:sans-serif;max-width:520px;margin:auto;padding:24px'>
            <h2 style='color:#16a34a'>Ride Approved! 🎉</h2>
            <p>Hi <strong>$name</strong>,</p>
            <p>Great news — your ride request has been <strong style='color:#16a34a'>approved</strong>!</p>
            <div style='background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:16px;margin:16px 0'>
                <p style='margin:4px 0'><strong>From:</strong> {$ride['from']}</p>
                <p style='margin:4px 0'><strong>To:</strong> {$ride['to']}</p>
                <p style='margin:4px 0'><strong>Time:</strong> {$ride['uptime']}</p>
                <p style='margin:4px 0'><strong>Vehicle:</strong> {$ride['vehicle']}</p>
            </div>
            <p>Have a safe and comfortable journey!</p>
            <p style='color:#888;font-size:13px'>JaanaHai — Travel together, save together.</p>
        </div>";
    }

    private static function rideDeclinedTemplate(string $name, array $ride): string
    {
        return "
        <div style='font-family:sans-serif;max-width:520px;margin:auto;padding:24px'>
            <h2 style='color:#dc2626'>Ride Request Declined</h2>
            <p>Hi <strong>$name</strong>,</p>
            <p>Unfortunately, your ride request has been <strong style='color:#dc2626'>declined</strong> by the driver.</p>
            <div style='background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:16px;margin:16px 0'>
                <p style='margin:4px 0'><strong>From:</strong> {$ride['from']}</p>
                <p style='margin:4px 0'><strong>To:</strong> {$ride['to']}</p>
                <p style='margin:4px 0'><strong>Time:</strong> {$ride['uptime']}</p>
            </div>
            <p>Don't worry — there are other rides available. <a href='" . ($_ENV['APP_URL'] ?? '') . "/search'>Search for another ride</a>.</p>
            <p style='color:#888;font-size:13px'>JaanaHai — Travel together, save together.</p>
        </div>";
    }
}
