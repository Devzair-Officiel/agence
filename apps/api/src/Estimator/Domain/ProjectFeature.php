<?php

declare(strict_types=1);

namespace App\Estimator\Domain;

use App\Estimator\Domain\Exception\EstimatorInvariantViolation;

enum ProjectFeature: string
{
    case ContactForm        = 'contact_form';
    case BookingForm        = 'booking_form';
    case Multilingual       = 'multilingual';
    case Payment            = 'payment';
    case Shipping           = 'shipping';
    case CustomerAccounts   = 'customer_accounts';
    case Authentication     = 'authentication';
    case RolesAndPermissions = 'roles_and_permissions';
    case ContentManagement  = 'content_management';
    case DocumentGeneration = 'document_generation';
    case Notifications      = 'notifications';
    case ImportExport       = 'import_export';
    case ApiIntegration     = 'api_integration';
    case StockManagement    = 'stock_management';
    case Promotions         = 'promotions';

    public static function fromString(string $value): self
    {
        $case = self::tryFrom($value);
        if ($case === null) {
            throw EstimatorInvariantViolation::invalidEnum('ProjectFeature', $value);
        }

        return $case;
    }

    /** @param list<string> $values
     *  @return list<self>
     */
    public static function fromList(array $values): array
    {
        $result = [];
        $seen   = [];

        foreach ($values as $value) {
            $case = self::tryFrom($value);
            if ($case === null) {
                throw EstimatorInvariantViolation::invalidEnum('ProjectFeature', $value);
            }
            if (!isset($seen[$case->value])) {
                $seen[$case->value] = true;
                $result[]           = $case;
            }
        }

        return $result;
    }

    /** @param list<self> $features
     *  @return list<string>
     */
    public static function toList(array $features): array
    {
        return array_map(static fn(self $f): string => $f->value, $features);
    }
}
