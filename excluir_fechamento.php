<?php
include('trava.php');
include('config.php');

if (isset($_GET['id']) && isset($_GET['confirmado'])) {
    $id = $conn->real_escape_string($_GET['id']);
    
    $sql = "DELETE FROM fechamentos WHERE id = '$id'";
    
    if ($conn->query($sql)) {
        // Pega a URL de onde o usuário veio e remove parâmetros antigos de busca
        $referer = $_SERVER['HTTP_REFERER'];
        $url_limpa = strtok($referer, '?'); 
        
        // Se a origem tinha datas (como detalhes_semana), mantém elas para não perder o filtro
        $query_string = parse_url($referer, PHP_URL_QUERY);
        parse_str($query_string, $params);
        unset($params['excluido']); // Limpa duplicados
        $params['excluido'] = '1';
        
        $nova_url = $url_limpa . '?' . http_build_query($params);
        
        header("Location: " . $nova_url);
        exit();
    } else {
        echo "Erro ao excluir: " . $conn->error;
    }
} else {
    header("Location: relatorios.php");
    exit();
}
?>