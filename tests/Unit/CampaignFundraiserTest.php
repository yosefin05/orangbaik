<?php

namespace Tests\Unit;

use App\Models\Campaign;
use App\Models\Campaign_Fundraiser;
use Tests\TestCase;

class CampaignFundraiserTest extends TestCase
{
    public function test_referral_url_uses_campaign_custom_slug_and_fundraiser_code(): void
    {
        $campaign = new Campaign([
            'slug' => 'slug-asli',
            'custom_slug' => 'campaign-pilihan',
        ]);
        $fundraiser = new Campaign_Fundraiser([
            'referral_code' => 'REF-USER123',
        ]);
        $fundraiser->setRelation('campaign', $campaign);

        $url = $fundraiser->referral_url;
        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        $this->assertSame('/campaign-pilihan', parse_url($url, PHP_URL_PATH));
        $this->assertSame('REF-USER123', $query['ref']);
    }

    public function test_referral_url_falls_back_to_campaign_slug(): void
    {
        $campaign = new Campaign([
            'slug' => 'slug-asli',
        ]);
        $fundraiser = new Campaign_Fundraiser([
            'referral_code' => 'REF-USER456',
        ]);
        $fundraiser->setRelation('campaign', $campaign);

        $url = $fundraiser->referral_url;
        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        $this->assertSame('/slug-asli', parse_url($url, PHP_URL_PATH));
        $this->assertSame('REF-USER456', $query['ref']);
    }
}
