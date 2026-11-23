<?php

namespace Tests\Unit;

use App\Models\Campaign;
use App\Models\Campaign_Fundraiser;
use Tests\TestCase;

class CampaignFundraiserTest extends TestCase
{
    public function test_referral_url_uses_custom_alias(): void
    {
        $campaign = new Campaign([
            'slug' => 'slug-asli',
            'custom_slug' => 'campaign-pilihan',
        ]);
        $fundraiser = new Campaign_Fundraiser([
            'referral_code' => 'REF-USER123',
            'alias' => 'andi-peduli',
        ]);
        $fundraiser->setRelation('campaign', $campaign);

        $url = $fundraiser->referral_url;
        $this->assertSame('/campaign-pilihan/andi-peduli', parse_url($url, PHP_URL_PATH));
    }

    public function test_referral_url_is_campaign_independent(): void
    {
        $campaign = new Campaign([
            'slug' => 'slug-asli',
        ]);
        $fundraiser = new Campaign_Fundraiser([
            'referral_code' => 'REF-USER456',
            'alias' => 'budi-berbagi',
        ]);
        $fundraiser->setRelation('campaign', $campaign);

        $url = $fundraiser->referral_url;
        $this->assertSame('/campaign/slug-asli/budi-berbagi', parse_url($url, PHP_URL_PATH));
    }
}
