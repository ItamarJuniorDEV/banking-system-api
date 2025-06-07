<?php

namespace SistemaBancario\Models;

/**
 * MODELO CONTA CORRENTE
 * 
 * Conta corrente com cheque especial e tarifas.
 */
class ContaCorrente extends Conta
{
    protected string $tipo_conta = 'corrente';
    
    /**
     * Tarifas da conta corrente
     */
    private const TARIFAS = [
        'saque' => 4.50,
        'transferencia' => 8.50,
        'ted' => 15.90,
        'doc' => 12.90,
        'pix' => 0.00,
        'deposito' => 0.00,
    ];
    
    /**
     * Criar nova conta corrente
     */
    public static function criar(int $clienteId, float $limiteChequeEspecial = 500.00): static
    {
        $conta = new static();
        $conta->fill([
            'numero_conta' => static::gerarNumeroConta(),
            'cliente_id' => $clienteId,
            'tipo_conta' => 'corrente',
            'saldo' => 0.00,
            'limite_cheque_especial' => $limiteChequeEspecial,
            'bloqueada' => 0,
            'limite_diario' => 5000.00
        ]);
        
        $conta->save();
        
        return $conta;
    }
    
    /**
     * Obter saldo disponível (saldo + limite cheque especial)
     */
    public function getSaldoDisponivel(): float
    {
        return $this->getSaldo() + $this->getLimiteChequeEspecial();
    }
    
    /**
     * Obter limite do cheque especial
     */
    public function getLimiteChequeEspecial(): float
    {
        return (float) $this->limite_cheque_especial;
    }
    
    /**
     * Verificar se está usando cheque especial
     */
    public function isUsandoChequeEspecial(): bool
    {
        return $this->getSaldo() < 0;
    }
    
    /**
     * Obter valor usado do cheque especial
     */
    public function getValorUsadoChequeEspecial(): float
    {
        $saldo = $this->getSaldo();
        return $saldo < 0 ? abs($saldo) : 0.00;
    }
    
    /**
     * Obter limite disponível do cheque especial
     */
    public function getLimiteDisponivelChequeEspecial(): float
    {
        return $this->getLimiteChequeEspecial() - $this->getValorUsadoChequeEspecial();
    }
    
    /**
     * Calcular tarifa para operação
     */
    public function calcularTarifa(string $operacao): float
    {
        return self::TARIFAS[$operacao] ?? 0.00;
    }
    
    /**
     * Alterar limite do cheque especial
     */
    public function alterarLimiteChequeEspecial(float $novoLimite): bool
    {
        if ($novoLimite < 0) {
            throw new \InvalidArgumentException('Limite não pode ser negativo');
        }
        
        // Verificar se não está usando mais que o novo limite
        $valorUsado = $this->getValorUsadoChequeEspecial();
        if ($valorUsado > $novoLimite) {
            throw new \InvalidArgumentException('Não é possível reduzir limite abaixo do valor em uso');
        }
        
        $this->limite_cheque_especial = $novoLimite;
        return $this->save();
    }
    
    /**
     * Sacar com verificação de limite diário
     */
    public function sacar(float $valor): bool
    {
        $tarifa = $this->calcularTarifa('saque');
        $valorTotal = $valor + $tarifa;
        
        // Verificar limite diário (simulado - em produção seria verificado no banco)
        if ($valor > $this->limite_diario) {
            throw new \Exception('Valor excede limite diário de saque');
        }
        
        return $this->debitar($valorTotal);
    }
    
    /**
     * Depositar (sem tarifa)
     */
    public function depositar(float $valor): bool
    {
        return $this->creditar($valor);
    }
    
    /**
     * Fazer PIX (sem tarifa)
     */
    public function pix(Conta $contaDestino, float $valor): bool
    {
        if ($this->numero_conta === $contaDestino->numero_conta) {
            throw new \InvalidArgumentException('Não é possível fazer PIX para a mesma conta');
        }
        
        $this->debitar($valor);
        $contaDestino->creditar($valor);
        
        return true;
    }
    
    /**
     * Fazer TED
     */
    public function ted(Conta $contaDestino, float $valor): bool
    {
        $tarifa = $this->calcularTarifa('ted');
        $valorTotal = $valor + $tarifa;
        
        $this->debitar($valorTotal);
        $contaDestino->creditar($valor);
        
        return true;
    }
    
    /**
     * Converter para array com dados específicos da conta corrente
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        
        $data['limite_cheque_especial'] = $this->getLimiteChequeEspecial();
        $data['limite_cheque_especial_formatado'] = 'R$ ' . number_format($this->getLimiteChequeEspecial(), 2, ',', '.');
        $data['usando_cheque_especial'] = $this->isUsandoChequeEspecial();
        $data['valor_usado_cheque_especial'] = $this->getValorUsadoChequeEspecial();
        $data['limite_disponivel_cheque_especial'] = $this->getLimiteDisponivelChequeEspecial();
        $data['tarifas'] = self::TARIFAS;
        
        return $data;
    }
}