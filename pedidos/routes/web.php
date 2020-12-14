<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
	return redirect('/desafio.html');
});

$router->get('/info', function () use ($router) {
	ob_start();
	phpinfo();
	$info = trim(ob_get_clean());
    return $router->app->version().'<br/>'.date('Y-m-d H:i:s').'<hr>'.$info;
});

$router->get('/pedidos', ['uses' => 'PedidosController@listar']);

$router->get('/pedidos/{id}', ['uses' => 'PedidosController@listar']);

$router->post('/pedidos', ['uses' => 'PedidosController@criar']);

$router->put('/pedidos/{id}/{status}', ['uses' => 'PedidosController@atualizar']);

$router->get('/info_filas', function () use ($router) {
	$ch = curl_init('http://10.5.0.20:15672/api/queues/%2F/pagamentos');
	curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json','Authorization: Basic '. base64_encode("guest:guest")]);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$return = curl_exec($ch);
	curl_close($ch);
	$return = json_decode($return, true);
	if(!isset($return['message_stats']))
	{
		$return['message_stats'] = ['json.publish'=>0, 'json.deliver_get'=>0];
	}
    return $return['message_stats'];
});