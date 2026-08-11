<?php

namespace Safe2Pay\Test;

require_once __DIR__ . '/../lib/Models/Payment/CreditCard.php';

use Safe2Pay\Models\Payment\CreditCard;

/**
 * Class CreditCardExternalAuthenticationTest
 *
 * Standalone assertion-based test (no PHPUnit runner available for this
 * environment/PHP version combination). Follows the SDK's existing
 * convention of static-method test classes, but asserts instead of
 * only echoing, so it can produce genuine RED/GREEN evidence without
 * hitting the network.
 *
 * @package Safe2Pay\Test
 */
class CreditCardExternalAuthenticationTest
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

    private static function newCard()
    {
        return new CreditCard("João da Silva", "4024007153763191", "12/2030", "241");
    }

    public static function AbsentExternalAuthenticationIsOmitted()
    {
        $card = self::newCard();

        $json = $card->JsonSerialize();

        self::assertTrue(
            !array_key_exists('ExternalAuthentication', $json),
            "AbsentExternalAuthenticationIsOmitted: key must be absent when ExternalAuthentication was never set"
        );
    }

    public static function NullExternalAuthenticationIsOmitted()
    {
        $card = self::newCard();
        $card->setExternalAuthentication(null);

        $json = $card->JsonSerialize();

        self::assertTrue(
            !array_key_exists('ExternalAuthentication', $json),
            "NullExternalAuthenticationIsOmitted: key must be absent when ExternalAuthentication is explicitly null"
        );
    }

    public static function CompleteExternalAuthenticationIsSerializedExactly()
    {
        $externalAuthentication = array(
            'Cavv' => 'AAABBEg0VhI0VniQEjRWAAAAAAA=',
            'Xid' => '0IMIkDx3BE1UjyxeoBM4MzMwMjE=',
            'Eci' => '05',
            'Version' => '2.2.0',
            'ReferenceId' => 'REF-123456'
        );

        $card = self::newCard();
        $card->setExternalAuthentication($externalAuthentication);

        $json = $card->JsonSerialize();

        self::assertTrue(
            array_key_exists('ExternalAuthentication', $json),
            "CompleteExternalAuthenticationIsSerializedExactly: key must be present when ExternalAuthentication has a value"
        );

        self::assertEquals(
            $externalAuthentication,
            isset($json['ExternalAuthentication']) ? $json['ExternalAuthentication'] : null,
            "CompleteExternalAuthenticationIsSerializedExactly: nested payload must match exactly (Cavv/Xid/Eci/Version/ReferenceId)"
        );
    }

    public static function ExistingPayloadRemainsUnchangedWhenAbsent()
    {
        $card = self::newCard();

        $json = $card->JsonSerialize();

        $expected = array(
            'Holder' => 'João da Silva',
            'CardNumber' => '4024007153763191',
            'ExpirationDate' => '12/2030',
            'SecurityCode' => '241',
            'Token' => null,
            'InstallmentQuantity' => 1,
            'IsPreAuthorization' => false,
            'IsApplyInterest' => false,
            'InterestRate' => 0.0,
            'SoftDescriptor' => null
        );

        self::assertEquals(
            $expected,
            $json,
            "ExistingPayloadRemainsUnchangedWhenAbsent: full payload must stay byte-for-byte compatible with current behavior"
        );
    }

    public static function RunAll()
    {
        self::AbsentExternalAuthenticationIsOmitted();
        self::NullExternalAuthenticationIsOmitted();
        self::CompleteExternalAuthenticationIsSerializedExactly();
        self::ExistingPayloadRemainsUnchangedWhenAbsent();

        echo "\n" . self::$assertions . " assertions, " . self::$failures . " failures.\n";

        if (self::$failures > 0) {
            exit(1);
        }

        exit(0);
    }
}

//CreditCardExternalAuthenticationTest::RunAll();
