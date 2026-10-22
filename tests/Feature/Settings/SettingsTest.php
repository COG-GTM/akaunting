<?php

namespace Tests\Feature\Settings;

use Tests\Feature\FeatureTestCase;

class SettingsTest extends FeatureTestCase
{
    public function testItShouldSeeSettingsPages()
    {
        $this->loginAs();

        foreach (['company', 'localisation', 'invoice', 'default', 'email', 'schedule'] as $page) {
            $this->get(route('settings.' . $page . '.edit'))
                ->assertOk();
        }
    }

    public function testItShouldUpdateCompanySettings()
    {
        $this->loginAs()
            ->patch(route('settings.company.update'), [
                'name' => 'Patched Runtime Ltd',
                'email' => 'billing@gmail.com',
                'country' => 'US',
                '_prefix' => 'company',
            ])
            ->assertOk();

        $this->assertFlashLevel('success');

        $this->assertSame('Patched Runtime Ltd', setting('company.name'));
    }
}
