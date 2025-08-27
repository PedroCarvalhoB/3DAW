<?php

// GerenciadorDisciplinas.php

require_once 'Disciplina.php';

class GerenciadorDisciplinas {
    private string $caminhoArquivo;
    private array $disciplinas;

    public function __construct(string $caminhoArquivo = 'disciplinas.txt') {
        $this->caminhoArquivo = $caminhoArquivo;
        $this->disciplinas = $this->carregarDisciplinas();
    }

    private function carregarDisciplinas(): array {
        $disciplinas = [];
        if (file_exists($this->caminhoArquivo)) {
            $linhas = file($this->caminhoArquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($linhas as $linha) {
                $dados = json_decode($linha, true);
                if ($dados) {
                    $disciplinas[] = Disciplina::deArray($dados);
                }
            }
        }
        return $disciplinas;
    }

    private function salvarDisciplinas(): void {
        $dados = array_map(function($disciplina) {
            return json_encode($disciplina->paraArray());
        }, $this->disciplinas);
        file_put_contents($this->caminhoArquivo, implode(PHP_EOL, $dados));
    }

    public function criarDisciplina(string $nome, string $codigo, int $cargaHoraria): Disciplina {
        $novaDisciplina = new Disciplina($nome, $codigo, $cargaHoraria);
        $this->disciplinas[] = $novaDisciplina;
        $this->salvarDisciplinas();
        return $novaDisciplina;
    }

    public function obterDisciplinas(): array {
        return $this->disciplinas;
    }

    public function obterDisciplinaPorId(string $id): ?Disciplina {
        foreach ($this->disciplinas as $disciplina) {
            if ($disciplina->id === $id) {
                return $disciplina;
            }
        }
        return null;
    }

    public function atualizarDisciplina(string $id, string $nome, string $codigo, int $cargaHoraria): bool {
        foreach ($this->disciplinas as $chave => $disciplina) {
            if ($disciplina->id === $id) {
                $this->disciplinas[$chave]->nome = $nome;
                $this->disciplinas[$chave]->codigo = $codigo;
                $this->disciplinas[$chave]->cargaHoraria = $cargaHoraria;
                $this->salvarDisciplinas();
                return true;
            }
        }
        return false;
    }

    public function excluirDisciplina(string $id): bool {
        foreach ($this->disciplinas as $chave => $disciplina) {
            if ($disciplina->id === $id) {
                unset($this->disciplinas[$chave]);
                $this->disciplinas = array_values($this->disciplinas);
                $this->salvarDisciplinas();
                return true;
            }
        }
        return false;
    }
}