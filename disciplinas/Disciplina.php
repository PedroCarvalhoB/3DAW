<?php

// Disciplina.php

class Disciplina {
    public string $id;
    public string $nome;
    public string $codigo;
    public int $cargaHoraria;

    public function __construct(string $nome, string $codigo, int $cargaHoraria, ?string $id = null) {
        $this->id = $id ?? uniqid();
        $this->nome = $nome;
        $this->codigo = $codigo;
        $this->cargaHoraria = $cargaHoraria;
    }

    public function paraArray(): array {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'codigo' => $this->codigo,
            'cargaHoraria' => $this->cargaHoraria,
        ];
    }
    
    public static function deArray(array $dados): self {
        return new self($dados['nome'], $dados['codigo'], $dados['cargaHoraria'], $dados['id']);
    }
}