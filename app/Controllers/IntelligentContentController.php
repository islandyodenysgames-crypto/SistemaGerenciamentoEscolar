<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Core\Response;
use App\Core\Session;
use App\Services\Content\Repositories\GeneratedContentRepository;

final class IntelligentContentController extends BaseController
{
    public function __construct(private GeneratedContentRepository $contents) {}

    public function index(): void
    {
        if(!$this->guard()||!$this->authorize(Permissions::SETTINGS_MANAGE))return;
        $status=trim((string)($_GET['status']??''));
        if(!in_array($status,['','pending','approved','published','discarded'],true))$status='';
        $this->view('pages/intelligent-content/index',[
            'title'=>'Conteúdo Inteligente - '.app_name(),
            'items'=>$this->contents->paginate($status?:null),
            'status'=>$status,
            'success'=>Session::get('intelligent_content_success'),
            'error'=>Session::get('intelligent_content_error'),
        ]);
        Session::remove('intelligent_content_success');Session::remove('intelligent_content_error');
    }

    public function approve(): void { $this->change('approved'); }
    public function publish(): void { $this->change('published'); }
    public function discard(): void { $this->change('discarded'); }

    public function edit(): void
    {
        if(!$this->guard()||!$this->authorize(Permissions::SETTINGS_MANAGE))return;
        $id=(int)($_POST['id']??0);$text=trim((string)($_POST['text_content']??''));
        if($id<1||$text===''){Session::set('intelligent_content_error','Informe um texto válido.');Response::redirect(base_url('configuracoes/conteudo-inteligente'));return;}
        $this->contents->updateStatus($id,'published',$this->userId(),mb_substr($text,0,1000));
        Session::set('intelligent_content_success','Texto editado e publicado com sucesso.');
        Response::redirect(base_url('configuracoes/conteudo-inteligente'));
    }

    public function clearCache(): void
    {
        if(!$this->guard()||!$this->authorize(Permissions::SETTINGS_MANAGE))return;
        $this->contents->clearFrom(date('Y-m-d'));
        Session::set('intelligent_content_success','Cache de hoje removido. Os textos serão gerados novamente no próximo acesso.');
        Response::redirect(base_url('configuracoes/conteudo-inteligente'));
    }

    private function change(string $status): void
    {
        if(!$this->guard()||!$this->authorize(Permissions::SETTINGS_MANAGE))return;
        $id=(int)($_POST['id']??0);
        if($id<1){Session::set('intelligent_content_error','Conteúdo inválido.');Response::redirect(base_url('configuracoes/conteudo-inteligente'));return;}
        $this->contents->updateStatus($id,$status,$this->userId());
        $labels=['approved'=>'aprovado','published'=>'publicado','discarded'=>'descartado'];
        Session::set('intelligent_content_success','Conteúdo '.$labels[$status].' com sucesso.');
        Response::redirect(base_url('configuracoes/conteudo-inteligente'));
    }

    private function userId(): ?int
    {
        $user=Session::get('user');$id=(int)($user['id']??0);return $id>0?$id:null;
    }
}
