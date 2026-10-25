<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Core\Authorization;
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
        if (empty($user['id'])) { http_response_code(401); Response::json(['success'=>false,'message'=>'Sessão expirada.']); }
        try {
            $type=(string)Request::post('type','');
            $permission=match($type){'student','class'=>Permissions::STUDENTS_VIEW,'monitoring'=>Permissions::STUDENT_MONITORING_VIEW,default=>null};
            if($permission===null||!Authorization::can($permission)){http_response_code(403);Response::json(['success'=>false,'message'=>'Você não possui permissão para este recurso.']);}
            $active = $this->service->toggle((int)$user['id'], $type, (int)Request::post('id',0), (string)($user['role']??''));
            Response::json(['success'=>true,'active'=>$active,'message'=>$active?'Adicionado aos favoritos.':'Removido dos favoritos.']);
        } catch (\Throwable $e) {
            http_response_code(404);
            Response::json(['success'=>false,'message'=>'O item não foi encontrado ou não está disponível para você.']);
        }
    }
}
