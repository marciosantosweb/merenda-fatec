<?php

namespace App\Services;

class SerperService {
    private $apiKey;

    public function __construct() {
        require_once __DIR__ . '/../../config/config.php';
        $this->apiKey = SERPER_API_KEY;
    }

    /**
     * Busca feriados usando a API do Serper (Google Search)
     */
    public function searchHolidays($year) {
        $query = "feriados nacionais e municipais em São Sebastião SP em $year";
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://google.serper.dev/search",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                "q" => $query, 
                "gl" => "br", 
                "hl" => "pt-br",
                "num" => 10
            ]),
            CURLOPT_HTTPHEADER => [
                "X-API-KEY: " . $this->apiKey,
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return ["error" => "Erro na chamada Serper: " . $err];
        }

        return json_decode($response, true);
    }

    /**
     * Processa o resultado do Serper para extrair datas
     * Nota: Como o Serper retorna resultados de busca, o ideal é usar uma IA 
     * para extrair as datas puras desses resultados. 
     * Mas faremos um parser básico aqui ou integração direta com IA.
     */
    public function parseHolidays($results) {
        // Objeto snippet costuma conter as datas.
        $blockedDays = [];
        
        if (isset($results['organic'])) {
            foreach ($results['organic'] as $result) {
                // Aqui entraria a lógica de Regex ou IA para identificar as datas 
                // Ex: "01/01/2026 - Confraternização Universal"
            }
        }
        
        return $blockedDays;
    }
}
