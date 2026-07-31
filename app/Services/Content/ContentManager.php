<?php

declare(strict_types=1);

namespace App\Services\Content;

use App\Services\Content\Contracts\ContentProviderInterface;
use App\Services\Content\Providers\RuleBasedProvider;
use App\Services\Content\Repositories\GeneratedContentRepository;

final class ContentManager
{
    public function __construct(
        private GeneratedContentRepository $repository,
        private RuleBasedProvider $provider
    ) {}

    /** @param array<string,mixed> $context */
    public function get(
        string $type,
        array $context,
        array $config,
        string $referenceDate,
        string $manualFallback = ''
    ): string {
        $enabled = !empty($config['intelligentContentEnabled']);
        $frequency = (string)($config['contentFrequency'] ?? 'daily');
        if ($frequency === 'manual') return $manualFallback;
        if ($frequency === 'weekly') {
            $referenceDate = date('Y-m-d', strtotime('monday this week', strtotime($referenceDate)));
        }
        $automaticKey = $this->automaticKey($type);
        $automatic = $automaticKey === null || !empty($config[$automaticKey]);
        if (!$enabled || !$automatic) return $manualFallback;

        $cached = $this->repository->findUsable($type, $referenceDate);
        if ($cached) return (string)$cached['text_content'];

        $existing = $this->repository->findAny($type, $referenceDate);
        $approvalMode = (string)($config['contentApprovalMode'] ?? 'automatic');
        if ($existing) {
            if ($approvalMode === 'review') return $manualFallback;
            return (string)$existing['text_content'];
        }

        $style = (string)($config['contentStyle'] ?? 'institutional');
        $text = $this->provider->generate($type, $context, $style, $referenceDate);
        if ($text === '') return $manualFallback;

        $status = $approvalMode === 'review' ? 'pending' : 'published';
        $this->repository->create([
            'content_type'=>$type,
            'reference_date'=>$referenceDate,
            'style'=>$style,
            'text_content'=>$text,
            'data_snapshot'=>json_encode($context, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
            'source_provider'=>$this->provider->name(),
            'status'=>$status,
            'published_at'=>$status === 'published' ? date('Y-m-d H:i:s') : null,
        ]);

        return $status === 'published' ? $text : $manualFallback;
    }

    private function automaticKey(string $type): ?string
    {
        return [
            'automatic_message'=>'autoAutomaticMessages',
            'daily_tip'=>'autoDailyTip',
            'support_text'=>'autoSupportText',
            'motivation'=>'autoMotivation',
            'institutional_slogan'=>'autoInstitutionalSlogan',
            'footer_slogan'=>'autoFooterSlogan',
            'highlight_message'=>'autoHighlightMessage',
            'did_you_know'=>'autoDidYouKnow',
            'hall_of_fame'=>'autoHallOfFame',
            'calendar_message'=>'autoCalendarMessage',
        ][$type] ?? null;
    }
}
