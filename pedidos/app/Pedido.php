<?php

namespace App;

use Anik\Amqp\Exchange;
use Anik\Amqp\PublishableMessage;
use Illuminate\Support\Facades\DB;

class Pedido
{
    public function __construct(array $pedido)
    {
        $this->uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));
        $this->pedido = $pedido;
    }

    public function gravar()
    {
        $row = [];
        $row['uuid'] = $this->uuid;
        $pedido = $this->pedido;
        unset($pedido['pagamento']['cartao']);
        $row['pedido'] = json_encode($pedido);
        $row['status'] = 'pendente de pagamento';
        DB::table('pedidos')->insert($row);
        return $this->uuid;
    }

    public function solicitarPagamento()
    {
        $pedido = $this->pedido;
        $pedido['uuid'] = $this->uuid;
        $msg = new PublishableMessage(json_encode($pedido));
        $msg->setExchange(new Exchange('amq.direct', ['type' => 'direct','declare' => true]));
        app('amqp')->publish($msg, 'pagamentos');
    }
}
