<?php
/**
 * Helper para Integração com Twilio SMS
 * @author GustavoChaconDeveloper
 * @description Classe para envio de SMS via Twilio API
 */

class TwilioSMS {
    private $accountSid;
    private $authToken;
    private $fromNumber;
    private $enabled;
    private $templates;
    
    public function __construct() {
        // Carregar configurações do conexao.php
        require_once __DIR__ . '/../conexao/conexao.php';
        
        $this->accountSid = TWILIO_ACCOUNT_SID;
        $this->authToken = TWILIO_AUTH_TOKEN;
        $this->fromNumber = TWILIO_FROM_NUMBER;
        $this->enabled = TWILIO_ENABLED;
        $this->templates = [
            'pt' => ['verification' => TWILIO_TEMPLATE_PT],
            'en' => ['verification' => TWILIO_TEMPLATE_EN]
        ];
    }
    
    /**
     * Enviar código de verificação via SMS
     * 
     * @param string $toNumber Número de destino (formato: +5511999999999)
     * @param string $code Código de verificação
     * @param string $language Idioma da mensagem (pt|en)
     * @return array Resultado do envio
     */
    public function sendVerificationCode($toNumber, $code, $language = 'pt') {
        // Se SMS está desabilitado (modo teste), enviar para número padrão
        if (!$this->enabled) {
            $testNumber = defined('TWILIO_TEST_NUMBER') ? TWILIO_TEST_NUMBER : '+554799124072';
            error_log("Modo TESTE ativado: Enviando SMS para número padrão ({$testNumber}) ao invés de {$toNumber}");
            $toNumber = $testNumber; // Substitui pelo número de teste
        }
        
        // Validar credenciais
        if ($this->accountSid === 'SEU_ACCOUNT_SID_AQUI' || 
            $this->authToken === 'SEU_AUTH_TOKEN_AQUI') {
            return [
                'success' => false,
                'error' => 'Credenciais da Twilio não configuradas. Edite config/twilio.php'
            ];
        }
        
        // Obter template da mensagem
        $template = $this->templates[$language]['verification'] ?? 
                   $this->templates['pt']['verification'];
        $message = str_replace('{code}', $code, $template);
        
        // Enviar SMS via Twilio API
        try {
            $result = $this->sendSMS($toNumber, $message);
            
            // Adicionar informação se foi enviado para número de teste
            if (!$this->enabled) {
                $result['test_mode'] = true;
                $result['test_number'] = $toNumber;
                $result['message'] = 'SMS enviado para número de TESTE: ' . $toNumber;
            }
            
            return $result;
        } catch (Exception $e) {
            error_log('Erro Twilio SMS: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Enviar SMS via Twilio REST API
        try {
            $result = $this->sendSMS($toNumber, $message);
            return $result;
        } catch (Exception $e) {
            error_log('Erro Twilio SMS: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Enviar SMS via Twilio REST API
     * 
     * @param string $to Número de destino
     * @param string $message Mensagem a enviar
     * @return array Resultado
     */
    private function sendSMS($to, $message) {
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";
        
        $data = [
            'From' => $this->fromNumber,
            'To' => $to,
            'Body' => $message
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->accountSid}:{$this->authToken}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        // FIXO: Desabilitar verificação SSL em desenvolvimento local
        // ⚠️ Em produção, remova estas linhas e configure certificados corretos
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Erro cURL: " . $error);
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'mode' => 'production',
                'sid' => $result['sid'] ?? null,
                'status' => $result['status'] ?? null,
                'message' => 'SMS enviado com sucesso'
            ];
        } else {
            $errorMessage = $result['message'] ?? 'Erro desconhecido';
            $errorCode = $result['code'] ?? 'N/A';
            
            throw new Exception("Erro Twilio ({$errorCode}): {$errorMessage}");
        }
    }
    
    /**
     * Verificar se SMS está habilitado
     */
    public function isEnabled() {
        return $this->enabled;
    }
    
    /**
     * Obter status das configurações
     */
    public function getStatus() {
        return [
            'enabled' => $this->enabled,
            'configured' => ($this->accountSid !== 'SEU_ACCOUNT_SID_AQUI'),
            'from_number' => $this->fromNumber
        ];
    }
}
