<?php

namespace App\WebServices\AI;

use App\WebServices\Service;
use Illuminate\Support\Facades\Http;

class SuggestPolicyService extends Service
{
    /**
     * @see https://drive.google.com/drive/folders/12by8OAx4-WqPDPHPcue4olcBdW7YK0xm
     * @see https://drive.google.com/file/d/1EIzTuC09mj0hsR8bdMR-n_XrI4r8qANO/view?usp=drive_link
     */
    public function runSuggestPolicyForSimulation(string $storeId, array $simulations, array $policies)
    {
        $dataRequest = [
            'store_id' => $storeId,
            'data' => $simulations,
            'policies' => $policies,
        ];

        $env = app()->environment('production') ? 'production' : 'staging';
        $url = config("ai.api_url.{$env}.module_suggest_policy_url");
        $response = Http::post($url, $dataRequest);

        return $this->toResponse($response);
    }
}
