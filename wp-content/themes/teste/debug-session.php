<?php
/**
 * Debug da sessão e dados do usuário
 * Acesse: http://localhost/Poker5/wordpress-theme/debug-session.php
 */

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>Debug da Sessão</h1>";

// Mostrar dados da sessão
echo "<h2>1. Dados da Sessão (\$_SESSION)</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Verificar se está logado
echo "<h2>2. Verificação de Login</h2>";
$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$has_user_id = isset($_SESSION['user_id']);

echo "<p>logged_in definido e true: " . ($logged_in ? '<span style="color:green">SIM</span>' : '<span style="color:red">NÃO</span>') . "</p>";
echo "<p>user_id definido: " . ($has_user_id ? '<span style="color:green">SIM - ID: ' . $_SESSION['user_id'] . '</span>' : '<span style="color:red">NÃO</span>') . "</p>";

if ($logged_in && $has_user_id) {
    echo "<h2>3. Tentando conectar ao banco de dados</h2>";
    
    try {
        $dsn = 'mysql:host=localhost;dbname=poker111;charset=utf8mb4';
        $pdo = new PDO($dsn, 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        echo "<p style='color:green'>✅ Conexão com banco de dados OK!</p>";
        
        // Verificar se tabela usuarios existe
        echo "<h2>4. Verificando tabela usuarios</h2>";
        $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color:green'>✅ Tabela 'usuarios' existe!</p>";
            
            // Buscar usuário
            echo "<h2>5. Buscando usuário ID: " . $_SESSION['user_id'] . "</h2>";
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $usuario = $stmt->fetch();
            
            if ($usuario) {
                echo "<p style='color:green'>✅ Usuário encontrado!</p>";
                echo "<pre>";
                // Esconder senha
                $usuario['senha_hash'] = '***OCULTO***';
                print_r($usuario);
                echo "</pre>";
            } else {
                echo "<p style='color:red'>❌ Usuário NÃO encontrado no banco!</p>";
            }
        } else {
            echo "<p style='color:red'>❌ Tabela 'usuarios' NÃO existe!</p>";
        }
        
        // Verificar tabela perfis_usuarios
        echo "<h2>6. Verificando tabela perfis_usuarios</h2>";
        $stmt = $pdo->query("SHOW TABLES LIKE 'perfis_usuarios'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color:green'>✅ Tabela 'perfis_usuarios' existe!</p>";
            
            // Verificar colunas
            $stmt = $pdo->query("DESCRIBE perfis_usuarios");
            $columns = $stmt->fetchAll();
            echo "<p>Colunas:</p><ul>";
            foreach ($columns as $col) {
                echo "<li>{$col['Field']} ({$col['Type']})</li>";
            }
            echo "</ul>";
            
            // Buscar perfil
            $stmt = $pdo->prepare("SELECT * FROM perfis_usuarios WHERE usuario_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $perfil = $stmt->fetch();
            
            if ($perfil) {
                echo "<p style='color:green'>✅ Perfil encontrado!</p>";
                echo "<pre>";
                print_r($perfil);
                echo "</pre>";
            } else {
                echo "<p style='color:orange'>⚠️ Perfil NÃO encontrado (será usado dados padrão)</p>";
            }
        } else {
            echo "<p style='color:orange'>⚠️ Tabela 'perfis_usuarios' NÃO existe!</p>";
        }
        
        // Testar a query completa
        echo "<h2>7. Testando query completa (JOIN)</h2>";
        $stmt = $pdo->prepare("
            SELECT 
                u.id,
                u.email,
                u.primeiro_nome,
                u.sobrenome,
                u.telefone,
                u.codigo_pais,
                u.tipo_conta,
                u.status,
                p.url_avatar,
                p.nickname
            FROM usuarios u
            LEFT JOIN perfis_usuarios p ON u.id = p.usuario_id
            WHERE u.id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $resultado = $stmt->fetch();
        
        echo "<p>Resultado da query:</p>";
        echo "<pre>";
        print_r($resultado);
        echo "</pre>";
        
    } catch (PDOException $e) {
        echo "<p style='color:red'>❌ Erro de conexão: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<h2 style='color:red'>Usuário não está logado!</h2>";
    echo "<p>Para testar, faça login primeiro através do sistema.</p>";
}

echo "<hr>";
echo "<p><a href='page-desafios.php'>Voltar para página de desafios</a></p>";
