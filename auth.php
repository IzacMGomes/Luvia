<?php
// auth.php - Inclua este arquivo no topo de páginas que precisam de login
// Uso:
//   require_once 'auth.php';
//   exigir_login();                     // qualquer usuário logado
//   exigir_login('administrador');      // apenas administrador
//   exigir_login('aluno');              // apenas aluno

session_start();

function exigir_login(?string $tipo_exigido = null): void
{
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }

    if ($tipo_exigido !== null && $_SESSION['usuario_tipo'] !== $tipo_exigido) {
        // Usuário logado mas sem permissão para acessar esta página
        header('Location: acesso_negado.php');
        exit;
    }
}
