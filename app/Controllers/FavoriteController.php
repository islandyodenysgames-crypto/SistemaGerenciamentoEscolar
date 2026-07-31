<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\FavoriteService;

class FavoriteController extends Controller
{
    public function __construct(private FavoriteService $service) {}

    public function toggle(): void
    {
        $user = (array) Session::get('user', []);
        if (empty($user['id'])) Response::json(['success'=>false,'message'=>'Sessão expirada.']);
        try {
            $active = $this->service->toggle((int)$user['id'], (string)Request::post('type',''), (int)Request::post('id',0));
            Response::json(['success'=>true,'active'=>$active,'message'=>$active?'Adicionado aos favoritos.':'Removido dos favoritos.']);
        } catch (\Throwable $e) {
            Response::json(['success'=>false,'message'=>$e->getMessage()]);
        }
    }
}
