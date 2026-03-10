<?php
/**
 * Gerenciador de Tickets
 * @author GustavoChaconDeveloper
 * @description Sistema de gerenciamento de tickets vinculados aos usuários
 */

require_once __DIR__ . '/../conexao/conexao.php';

class TicketManager {
    
    /**
     * Garantir que a tabela de tickets existe
     */
    public static function garantirTabelaTickets() {
        try {
            $conn = getConnection();
            
            $sql = "
                CREATE TABLE IF NOT EXISTS tickets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    usuario_id INT NOT NULL,
                    tipo VARCHAR(50) NOT NULL DEFAULT 'challenge_entry',
                    descricao TEXT NULL,
                    origem VARCHAR(100) NULL COMMENT 'De onde veio o ticket',
                    status VARCHAR(20) NOT NULL DEFAULT 'disponivel',
                    data_obtencao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    data_uso DATETIME NULL,
                    data_expiracao DATETIME NULL,
                    usado_em VARCHAR(100) NULL,
                    INDEX idx_usuario_id (usuario_id),
                    INDEX idx_status (status),
                    INDEX idx_data_expiracao (data_expiracao),
                    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ";
            
            $conn->exec($sql);
            return true;
            
        } catch (PDOException $e) {
            error_log("Erro ao criar tabela tickets: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Contar tickets disponíveis de um usuário
     * 
     * @param int $userId ID do usuário
     * @return int Quantidade de tickets disponíveis
     */
    public static function contarTicketsDisponiveis($userId) {
        try {
            $conn = getConnection();
            
            $stmt = $conn->prepare("
                SELECT COUNT(*) 
                FROM tickets 
                WHERE usuario_id = ? 
                AND status = 'disponivel' 
                AND (data_expiracao IS NULL OR data_expiracao > NOW())
            ");
            $stmt->execute([$userId]);
            
            return (int) $stmt->fetchColumn();
            
        } catch (PDOException $e) {
            error_log("Erro ao contar tickets: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Adicionar ticket(s) a um usuário
     * 
     * @param int $userId ID do usuário
     * @param int $quantidade Quantidade de tickets (padrão: 1)
     * @param string $tipo Tipo do ticket (padrão: 'challenge_entry')
     * @param string $origem De onde veio o ticket
     * @param string $descricao Descrição opcional
     * @param DateTime $dataExpiracao Data de expiração (opcional)
     * @return array [success, message, quantidade_adicionada]
     */
    public static function adicionarTickets($userId, $quantidade = 1, $tipo = 'challenge_entry', $origem = null, $descricao = null, $dataExpiracao = null) {
        try {
            self::garantirTabelaTickets();
            $conn = getConnection();
            
            // Validar quantidade
            if ($quantidade < 1) {
                return [
                    'success' => false,
                    'message' => 'Quantidade inválida'
                ];
            }
            
            // Preparar data de expiração
            $dataExpiracaoStr = null;
            if ($dataExpiracao !== null) {
                $dataExpiracaoStr = $dataExpiracao->format('Y-m-d H:i:s');
            }
            
            // Inserir tickets
            $stmt = $conn->prepare("
                INSERT INTO tickets 
                    (usuario_id, tipo, descricao, origem, status, data_expiracao) 
                VALUES 
                    (?, ?, ?, ?, 'disponivel', ?)
            ");
            
            $adicionados = 0;
            for ($i = 0; $i < $quantidade; $i++) {
                $stmt->execute([
                    $userId,
                    $tipo,
                    $descricao,
                    $origem,
                    $dataExpiracaoStr
                ]);
                $adicionados++;
            }
            
            // Log da atividade
            $stmtLog = $conn->prepare("
                INSERT INTO logs_atividades 
                    (usuario_id, acao, descricao, endereco_ip, user_agent) 
                VALUES 
                    (?, 'add_tickets', ?, ?, ?)
            ");
            $stmtLog->execute([
                $userId,
                "Adicionados {$adicionados} ticket(s) - Origem: {$origem}",
                $_SERVER['REMOTE_ADDR'] ?? 'system',
                $_SERVER['HTTP_USER_AGENT'] ?? 'system'
            ]);
            
            return [
                'success' => true,
                'message' => "Tickets adicionados com sucesso!",
                'quantidade_adicionada' => $adicionados
            ];
            
        } catch (PDOException $e) {
            error_log("Erro ao adicionar tickets: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao adicionar tickets'
            ];
        }
    }
    
    /**
     * Usar um ticket
     * 
     * @param int $userId ID do usuário
     * @param string $usadoEm Onde foi usado (desafio/torneio)
     * @return array [success, message, ticket_id]
     */
    public static function usarTicket($userId, $usadoEm = null) {
        try {
            $conn = getConnection();
            
            // Buscar primeiro ticket disponível
            $stmt = $conn->prepare("
                SELECT id 
                FROM tickets 
                WHERE usuario_id = ? 
                AND status = 'disponivel' 
                AND (data_expiracao IS NULL OR data_expiracao > NOW())
                ORDER BY data_obtencao ASC
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$ticket) {
                return [
                    'success' => false,
                    'message' => 'Nenhum ticket disponível'
                ];
            }
            
            // Marcar ticket como usado
            $stmtUpdate = $conn->prepare("
                UPDATE tickets 
                SET status = 'usado', 
                    data_uso = NOW(), 
                    usado_em = ? 
                WHERE id = ?
            ");
            $stmtUpdate->execute([$usadoEm, $ticket['id']]);
            
            // Log da atividade
            $stmtLog = $conn->prepare("
                INSERT INTO logs_atividades 
                    (usuario_id, acao, descricao, endereco_ip, user_agent) 
                VALUES 
                    (?, 'use_ticket', ?, ?, ?)
            ");
            $stmtLog->execute([
                $userId,
                "Ticket usado - ID: {$ticket['id']} - Em: {$usadoEm}",
                $_SERVER['REMOTE_ADDR'] ?? 'system',
                $_SERVER['HTTP_USER_AGENT'] ?? 'system'
            ]);
            
            return [
                'success' => true,
                'message' => 'Ticket usado com sucesso!',
                'ticket_id' => $ticket['id']
            ];
            
        } catch (PDOException $e) {
            error_log("Erro ao usar ticket: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro ao usar ticket'
            ];
        }
    }
    
    /**
     * Listar tickets de um usuário
     * 
     * @param int $userId ID do usuário
     * @param string $status Filtrar por status (opcional)
     * @return array Lista de tickets
     */
    public static function listarTickets($userId, $status = null) {
        try {
            $conn = getConnection();
            
            $sql = "
                SELECT * 
                FROM tickets 
                WHERE usuario_id = ?
            ";
            
            $params = [$userId];
            
            if ($status !== null) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY data_obtencao DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erro ao listar tickets: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Remover tickets expirados
     * 
     * @return int Quantidade de tickets removidos
     */
    public static function limparTicketsExpirados() {
        try {
            $conn = getConnection();
            
            $stmt = $conn->prepare("
                UPDATE tickets 
                SET status = 'expirado' 
                WHERE status = 'disponivel' 
                AND data_expiracao IS NOT NULL 
                AND data_expiracao < NOW()
            ");
            $stmt->execute();
            
            return $stmt->rowCount();
            
        } catch (PDOException $e) {
            error_log("Erro ao limpar tickets expirados: " . $e->getMessage());
            return 0;
        }
    }
}
