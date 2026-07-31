<?php

declare(strict_types=1);

namespace App\Services\Content;

final class InstitutionalContentService
{
    public function __construct(
        private ContentManager $manager,
        private ContentContextService $contexts,
        private StudentRecognitionService $recognition
    ) {}

    public function build(array $dashboard,array $hallOfFame,array $config,?string $referenceDate=null): array
    {
        $date=$referenceDate?:date('Y-m-d');$context=$this->contexts->build($dashboard,$hallOfFame,$date);
        $manual=[
            'automatic_message'=>(string)($config['supportText']??'Cada presença fortalece a aprendizagem.'),
            'daily_tip'=>(string)($config['dailyTipText']??'Pequenas atitudes constroem grandes resultados.'),
            'support_text'=>(string)($config['supportText']??'Sua presença transforma o hoje e constrói o amanhã.'),
            'motivation'=>(string)($config['motivationText']??'Cada presença representa uma nova oportunidade de aprender.'),
            'institutional_slogan'=>(string)($config['institutionalSlogan']??'Educação, presença e futuro.'),
            'footer_slogan'=>(string)($config['footerSlogan']??'Cada presença conta. Cada aluno importa.'),
            'highlight_message'=>(string)($config['highlightMessage']??'Parabéns pelo resultado de hoje!'),
            'did_you_know'=>'A presença diária fortalece a aprendizagem.',
            'hall_of_fame'=>'Reconhecemos quem transforma presença em aprendizagem.',
            'calendar_message'=>'Acompanhe os próximos compromissos da escola.',
        ];
        $get=fn(string $type):string=>$this->manager->get($type,$context,$config,$date,$manual[$type]??'');
        return [
            'generatedAt'=>date(DATE_ATOM),'referenceDate'=>$date,
            'automaticMessage'=>$get('automatic_message'),
            'didYouKnow'=>$get('did_you_know'),
            'motivationText'=>$get('motivation'),
            'motivationSubtitle'=>$get('support_text'),
            'dailyTipTitle'=>'Dica do dia','dailyTipText'=>$get('daily_tip'),
            'supportTitle'=>(($context['change_raw']??0)>0?'Estamos avançando!':'Contamos com você!'),
            'supportText'=>$get('support_text'),
            'institutionalSlogan'=>$get('institutional_slogan'),
            'footerSlogan'=>$get('footer_slogan'),
            'highlightMessage'=>$get('highlight_message'),
            'hallOfFameMessage'=>$get('hall_of_fame'),
            'calendarMessage'=>$get('calendar_message'),
            'studentRecognition'=>$this->recognition->weekly($date),
            'comparison'=>$this->contexts->comparison($date),
        ];
    }
}
