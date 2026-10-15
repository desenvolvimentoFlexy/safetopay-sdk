<?php

namespace Safe2Pay\Test;

require_once __DIR__ . '/../lib/Models/Payment/Pix.php';
require_once __DIR__ . '/../lib/Models/Transactions/Transaction.php';

use Safe2Pay\Models\Payment\Pix;
use Safe2Pay\Models\Transactions\Transaction;

/**
 * Class PixExpirationTest
 *
 * Standalone assertion-based test (no PHPUnit runner available for this
 * environment/PHP version combination). Follows the SDK's existing
 * convention of static-method test classes, but asserts instead of
 * only echoing, so it can produce genuine RED/GREEN evidence without
 * hitting the network.
 *
 * @package Safe2Pay\Test
 */
class PixExpirationTest
{
    private static $failures = 0;
    private static $assertions = 0;

    private static function assertTrue($condition, $message)
    {
        self::$assertions++;
        if (!$condition) {
            self::$failures++;
            echo "FAIL: $message\n";
        } else {
            echo "PASS: $message\n";
        }
    }

    private static function assertEquals($expected, $actual, $message)
    {
        self::assertTrue($expected === $actual, $message . " (expected: " . var_export($expected, true) . ", actual: " . var_export($actual, true) . ")");
    }

    public static function IntegerExpirationIsSerialized()
    {
        $pix = new Pix();
        $pix->setExpiration(14400);

        self::assertEquals(
            '{"Expiration":14400}',
            json_encode($pix),
            "IntegerExpirationIsSerialized: Expiration must be sent to Safe2Pay"
        );
    }

    public static function StringExpirationIsSerializedAsInteger()
    {
        $pix = new Pix();
        $pix->setExpiration('14400');

        self::assertEquals(
            '{"Expiration":14400}',
            json_encode($pix),
            "StringExpirationIsSerializedAsInteger: numeric string must be cast to integer"
        );
    }

    public static function AbsentExpirationKeepsEmptyObject()
    {
        $pix = new Pix();

        self::assertEquals(
            '{}',
            json_encode($pix),
            "AbsentExpirationKeepsEmptyObject: payload must remain an empty JSON object (not '[]')"
        );
    }

    public static function TransactionPaymentObjectCarriesExpiration()
    {
        $pix = new Pix();
        $pix->setExpiration(14397);

        $transaction = new Transaction();
        $transaction->setPaymentObject($pix);

        $payload = json_decode(json_encode($transaction), true);

        self::assertEquals(
            14397,
            isset($payload['PaymentObject']['Expiration']) ? $payload['PaymentObject']['Expiration'] : null,
            "TransactionPaymentObjectCarriesExpiration: PaymentObject.Expiration must reach the request body"
        );
    }

    public static function RunAll()
    {
        self::IntegerExpirationIsSerialized();
        self::StringExpirationIsSerializedAsInteger();
        self::AbsentExpirationKeepsEmptyObject();
        self::TransactionPaymentObjectCarriesExpiration();

        echo "\n" . self::$assertions . " assertions, " . self::$failures . " failures.\n";

        if (self::$failures > 0) {
            exit(1);
        }

        exit(0);
    }
}

//PixExpirationTest::RunAll();
