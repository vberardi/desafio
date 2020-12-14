<?php 

function montarPayload($msg) 
{
	$payload = '{
	"code": "'.$msg->uuid.'",
	"customer": {
		"name": "João Silva",
		"type": "individual",
		"birthdate": "1991-05-20T00:00:00",
		"document": "12345678900",
		"email": "​fulano@gmail.com​",
		"phones": {
			"home_phone": {
				"country_code": "55",
				"area_code": "21",
				"number": "22225555"
			},
			"mobile_phone": {
				"country_code": "55",
				"area_code": "21",
				"number": "999995555"
			}
		},
		"address": {
			"city": "Rio de Janeiro",
			"country": "BR",
			"state": "RJ",
			"line_1": "123, Rua Souza, Centro",
			"line_2": "Apto 1011",
			"zip_code": "23021130"
		}
	},
	"payments": [{
		"amount": '.str_replace(',', '', substr($msg->pagamento->valor, 3)).',
		"payment_method": "credit_card",
		"credit_card": {
			"installments": '.$msg->pagamento->parcelas.',
			"card": {
				"brand": "visa",
				"number": "'.$msg->pagamento->cartao->numero_cartao.'",
				"holder_name": "JOAO SILVA",
				"exp_month": 10,
				"exp_year": 2022,
				"cvv": "321",
				"address": {
					"city": "Rio de Janeiro",
					"country": "BR",
					"state": "RJ",
					"line_1": "123, Rua Souza, Centro",
					"line_2": "Apto 1011",
					"zip_code": "23021130"
				}
			}
		}
	}],
	"items": [{
			"description": "Discos Vinil",
			"quantity": '.$msg->carrinho->items[0]->quantidade.',
			"amount": '.($msg->carrinho->items[0]->valor_unit*100).'
		},
		{
			"description": "Toca Vinil",
			"quantity": '.$msg->carrinho->items[1]->quantidade.',
			"amount": '.($msg->carrinho->items[1]->valor_unit*100).'
		}
	]
}';
	return $payload;
}

function efetuarPagamento($payload)
{
	$ch = curl_init('https://api.mundipagg.com/core/v1/orders');
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json','Authorization: Basic '. base64_encode("sk_test_WBOl1NijJI3VvJeM:")]);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$return = curl_exec($ch);
	curl_close($ch);
	$return = json_decode($return, true);
	return $return;
}

function gravarPagamento($pagamento)
{
	$mongo = 'mongodb://teste:1234@10.5.0.30/admin';
    $manager = new MongoDB\Driver\Manager($mongo);
    $bulkWriteManager = new MongoDB\Driver\BulkWrite;
    $campos = array_flip(['id', 'code', 'status']);
    $dadosPagamento = array_intersect_key($pagamento, $campos);
    $bulkWriteManager->insert($dadosPagamento);
    $insert = $manager->executeBulkWrite('desafio.pagamentos', $bulkWriteManager); 
	return $insert->getInsertedCount();
}

function atualizarPedido($pagamento)
{
	$status = ['paid'=>'pago', 'failed'=>'cancelado'];
	$url = 'http://10.5.0.10:8080/pedidos/'.$pagamento['code'].'/'.$status[$pagamento['status']];
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_HEADER, true);
	$return = curl_exec($ch);
	$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	return $http;
}



