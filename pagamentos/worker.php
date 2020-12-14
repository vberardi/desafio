<?php

require_once __DIR__.'/vendor/autoload.php';
define('AMQP_DEBUG', false);

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;

include 'functions.php';

$exchange = 'direct.exchange';
$queue = 'pagamentos';
$consumerTag = 'consumer';

$connection = new AMQPStreamConnection('10.5.0.20', 5672, 'guest', 'guest', '/');
$channel = $connection->channel();

$channel->queue_declare($queue, false, true, false, false);
$channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);
$channel->queue_bind($queue, $exchange);

function process_message($message)
{
    $m = json_decode($message->body);
    echo date('Y-m-d H:i:s').' -> '.$m->uuid." |\n";

    $payload = montarPayload($m);
    $pagamento = efetuarPagamento($payload);
    echo 'pagamento: "'.$pagamento['id'].'" ('.$pagamento['status'].')'."\n";

    $gravacao = gravarPagamento($pagamento);
    echo 'gravação pagamento: '.(($gravacao==1)?'ok':'erro')."\n";
    
    $pedido = atualizarPedido($pagamento);
    echo 'atualização pedido: '.(($pedido==200)?'ok':'erro')."\n";
    echo '-------------------------------------------------------------'."\n";
  
    $message->ack();

    if ($message->body === 'quit') {
        $message->getChannel()->basic_cancel($message->getConsumerTag());
    }
}

$channel->basic_consume($queue, $consumerTag, false, false, false, false, 'process_message');

function shutdown($channel, $connection)
{
    $channel->close();
    $connection->close();
}

register_shutdown_function('shutdown', $channel, $connection);

echo ' worker de pagamento rodando...'."\n";

while ($channel->is_consuming()) {
    $channel->wait();
}
