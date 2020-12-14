<?php

namespace App\Http\Controllers;

use App\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidosController extends Controller
{
    public function __construct()
    {
        
    }

    public function listar($id = null)
    {   
        $pedidos = DB::table('pedidos')->when($id, function ($query, $id) {
                    return $query->where('id', $id);
                })->get();
        foreach ($pedidos as $key => $value) 
        {
            $pedidos[$key]->pedido = json_decode($value->pedido);
        }
        return $pedidos;
    }

    public function criar(Request $request)
    {
        try{
            $pedido = new Pedido($request->all());
            $pedido->gravar();
            $pedido->solicitarPagamento();
            return response('Pedido <strong>'.$pedido->uuid.'</strong> gravado com sucesso!', 200);
        } catch (Exception $e) {
            // logar
            return response('Erro '.$e, 500);
        }
    }    

    public function atualizar($id, $status)
    {
        try{
            //$pedido = DB::table('pedidos')->where('id', $id)->update(['status' => $status]);
            $pedido = DB::table('pedidos')->where('uuid', $id)->update(['status' => $status]);
            if($pedido)
            {
                return response('Pedido <strong>'.$id.'</strong> atualizado com sucesso!', 200);    
            }
            return response('ERRO: pedido <strong>'.$id.'</strong> não encontrado!', 404);
        } catch (Exception $e) {
            // logar
            return response('Erro '.$e, 500);
        }
    }    

}
