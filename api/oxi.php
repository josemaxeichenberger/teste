<?php
/**
 * Teste de Debug para Change Password API
 */

// Habilitar exibição de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Debug - Change Password API</h1>";

// 1. Verificar sessão
session_start();
echo "<h2>1. Verificação de Sessão</h2>";
echo "<pre>";
echo "Sessão iniciada: " . (session_status() === PHP_SESSION_ACTIVE ? 'SIM' : 'NÃO') . "\n";
echo "Usuário logado: " . (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] ? 'SIM' : 'NÃO') . "\n";
echo "User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NÃO DEFINIDO') . "\n";
echo "User Name: " . (isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'NÃO DEFINIDO') . "\n";
echo "</pre>";

// 2. Verificar arquivo de conexão
echo "<h2>2. Verificação do Arquivo de Conexão</h2>";
$caminhos_conexao = [
    __DIR__ . '/../conexao/conexao.php',
    __DIR__ . '/../../conexao/conexao.php',
    $_SERVER['DOCUMENT_ROOT'] . '/conexao/conexao.php',
    dirname(__DIR__) . '/conexao/conexao.php',
];

$conexao_encontrada = false;
echo "<pre>";
foreach ($caminhos_conexao as $caminho) {
    $existe = file_exists($caminho);
    echo "Caminho: $caminho - " . ($existe ? 'ENCONTRADO ✓' : 'NÃO ENCONTRADO ✗') . "\n";
    
    if ($existe && !$conexao_encontrada) {
        try {
            require_once $caminho;
            $conexao_encontrada = true;
            echo "→ Arquivo incluído com sucesso!\n";
        } catch (Exception $e) {
            echo "→ ERRO ao incluir: " . $e->getMessage() . "\n";
        }
    }
}
echo "</pre>";

// 3. Verificar função getConnection
echo "<h2>3. Verificação da Função getConnection()</h2>";
echo "<pre>";
if (function_exists('getConnection')) {
    echo "Função getConnection() existe: SIM ✓\n";
    
    try {
        $pdo = getConnection();
        
        if ($pdo instanceof PDO) {
            echo "PDO instanciado com sucesso: SIM ✓\n";
            
            // Testar query simples
            $stmt = $pdo->query("SELECT 1 as test");
            $result = $stmt->fetch();
            echo "Conexão com banco funcionando: " . ($result['test'] === 1 ? 'SIM ✓' : 'NÃO ✗') . "\n";
            
            // Verificar tabela usuarios
            $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
            $table_exists = $stmt->fetch();
            echo "Tabela 'usuarios' existe: " . ($table_exists ? 'SIM ✓' : 'NÃO ✗') . "\n";
            
            // Verificar colunas
            if ($table_exists) {
                $stmt = $pdo->query("DESCRIBE usuarios");
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo "\nColunas da tabela 'usuarios':\n";
                foreach ($columns as $col) {
                    echo "  - $col\n";
                }
                
                // Verificar se coluna 'senha' existe
                $coluna_senha = in_array('senha', $columns);
                echo "\nColuna 'senha' existe: " . ($coluna_senha ? 'SIM ✓' : 'NÃO ✗') . "\n";
                
                // Verificar se coluna 'data_atualizacao' existe
                $coluna_data = in_array('data_atualizacao', $columns);
                echo "Coluna 'data_atualizacao' existe: " . ($coluna_data ? 'SIM ✓' : 'NÃO ✗') . "\n";
                
                if (!$coluna_data) {
                    echo "\n⚠️ AVISO: Coluna 'data_atualizacao' não existe!\n";
                    echo "Execute: ALTER TABLE usuarios ADD data_atualizacao TIMESTAMP NULL;\n";
                }
            }
            
        } else {
            echo "PDO instanciado com sucesso: NÃO ✗\n";
            echo "Tipo retornado: " . gettype($pdo) . "\n";
        }
        
    } catch (PDOException $e) {
        echo "ERRO PDO: " . $e->getMessage() . "\n";
    } catch (Exception $e) {
        echo "ERRO: " . $e->getMessage() . "\n";
    }
} else {
    echo "Função getConnection() existe: NÃO ✗\n";
}
echo "</pre>";

// 4. Informações do servidor
echo "<h2>4. Informações do Servidor</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Current File: " . __FILE__ . "\n";
echo "Current Dir: " . __DIR__ . "\n";
echo "</pre>";

echo "<hr>";
echo "<p><strong>Teste concluído!</strong></p>";
echo "<p><a href='/api/change-password.php'>Testar API change-password.php</a></p>";
?>
