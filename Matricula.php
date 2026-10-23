<?php
require_once 'Database.php';

class Matricula {
    public function matricularAluno(int $alunoId, int $cursoId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('INSERT IGNORE INTO matriculas (aluno_id, curso_id) VALUES (?, ?)');
        return $stmt->execute([$alunoId, $cursoId]);
    }

    public function listarCursosDoAluno(int $alunoId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT c.* FROM cursos c 
            INNER JOIN matriculas m ON m.curso_id = c.id 
            WHERE m.aluno_id = ?
        ');
        $stmt->execute([$alunoId]);
        return $stmt->fetchAll();
    }
}
?>