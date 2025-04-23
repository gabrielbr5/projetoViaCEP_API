<?php
class ViaCEP {
    public static function consultarCEP($cep) {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        
        if (strlen($cep) != 8) {
            return false;
        }
        
        $url = "https://viacep.com.br/ws/{$cep}/json/";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            error_log('Erro na consulta do CEP: ' . curl_error($ch));
            return false;
        }
        
        curl_close($ch);
        
        if ($httpCode != 200) {
            return false;
        }
        
        $dados = json_decode($response, true);
        
        return isset($dados['erro']) ? false : $dados;
    }
}

$cep = '';
$dadosCEP = null;
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cep = trim($_POST['cep'] ?? '');
    
    if (!empty($cep)) {
        $dadosCEP = ViaCEP::consultarCEP($cep);
        if ($dadosCEP === false) {
            $erro = 'CEP não encontrado ou formato inválido.';
        }
    } else {
        $erro = 'Por favor, informe um CEP.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de CEP | ViaCEP</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #34495e;
            --accent: #3498db;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --success: #27ae60;
            --error: #e74c3c;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            line-height: 1.6;
            background-color: #f5f7fa;
            color: var(--dark);
            padding: 20px;
        }
        
        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        h1 {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 2.2rem;
        }
        
        .subtitle {
            color: var(--secondary);
            font-weight: 400;
            font-size: 1.1rem;
        }
        
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--secondary);
        }
        
        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }
        
        input[type="text"]:focus {
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        button {
            background-color: var(--accent);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
        }
        
        button:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        
        .error-message {
            background-color: var(--error);
            color: white;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            text-align: center;
        }
        
        .result-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            animation: fadeIn 0.5s ease;
        }
        
        .result-title {
            color: var(--primary);
            margin-bottom: 20px;
            font-size: 1.5rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .result-list {
            list-style: none;
        }
        
        .result-list li {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
        }
        
        .result-list li:last-child {
            border-bottom: none;
        }
        
        .result-list strong {
            min-width: 120px;
            display: inline-block;
            color: var(--secondary);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        footer {
            text-align: center;
            margin-top: 40px;
            color: var(--secondary);
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 20px auto;
            }
            
            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Consulta de CEP</h1>
            <p class="subtitle">Encontre endereços em todo o Brasil</p>
        </header>
        
        <div class="card">
            <form method="post">
                <div class="form-group">
                    <label for="cep">Digite o CEP</label>
                    <input type="text" id="cep" name="cep" value="<?= htmlspecialchars($cep) ?>" 
                           placeholder="Ex: 01001000 ou 01001-000" maxlength="9">
                </div>
                
                <button type="submit">Consultar</button>
            </form>
        </div>
        
        <?php if ($erro): ?>
            <div class="error-message">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($dadosCEP): ?>
            <div class="result-card">
                <h2 class="result-title">Resultado da Consulta</h2>
                
                <ul class="result-list">
                    <li><strong>CEP:</strong> <?= htmlspecialchars($dadosCEP['cep']) ?></li>
                    <li><strong>Logradouro:</strong> <?= htmlspecialchars($dadosCEP['logradouro']) ?></li>
                    <li><strong>Complemento:</strong> <?= htmlspecialchars($dadosCEP['complemento'] ?: 'N/A') ?></li>
                    <li><strong>Bairro:</strong> <?= htmlspecialchars($dadosCEP['bairro']) ?></li>
                    <li><strong>Cidade:</strong> <?= htmlspecialchars($dadosCEP['localidade']) ?></li>
                    <li><strong>Estado:</strong> <?= htmlspecialchars($dadosCEP['uf']) ?></li>
                    <li><strong>IBGE:</strong> <?= htmlspecialchars($dadosCEP['ibge']) ?></li>
                    <li><strong>DDD:</strong> <?= htmlspecialchars($dadosCEP['ddd']) ?></li>
                </ul>
            </div>
        <?php endif; ?>
        
        <footer>
            <p>Dados fornecidos pela API ViaCEP | <?= date('Y') ?></p>
        </footer>
    </div>
</body>
</html>