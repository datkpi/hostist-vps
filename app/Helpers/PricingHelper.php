<?php

namespace App\Helpers;

class PricingHelper
{
    /**
     * Tính giá dựa trên thời hạn
     *
     * @param float $basePrice Giá cơ bản cho 1 năm
     * @param int $period Số năm
     * @return float Giá đã tính theo thời hạn
     */
    public static function calculatePriceByPeriod($basePrice, $period)
    {
        // Hệ số giảm giá theo thời hạn
        $discountRates = [
            1 => 1,      // 1 năm: giá gốc
            2 => 0.9,    // 2 năm: giảm 10% (so với giá 2 năm không giảm)
            3 => 0.85,   // 3 năm: giảm 15% (so với giá 3 năm không giảm)
            5 => 0.8     // 5 năm: giảm 20% (so với giá 5 năm không giảm)
        ];

        // Lấy hệ số giảm giá
        $discountRate = $discountRates[$period] ?? 1;

        // Tính giá mới: giá cơ bản * số năm * hệ số giảm giá
        return $basePrice * $period * $discountRate;
    }
}
