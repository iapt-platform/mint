<?php

use App\Services\OpenSearchService;

beforeEach(function () {
    config(['mint.app.ops_token' => 'secret-ops-token']);
});

function updateSynonyms(?string $token, string $version = '20260731')
{
    $headers = $token === null ? [] : ['Authorization' => 'Bearer '.$token];

    return test()->putJson("/api/ops/update-pali-synonyms/{$version}", [], $headers);
}

it('rejects a request without a token', function () {
    updateSynonyms(null)->assertForbidden();
});

it('rejects a request with a wrong token', function () {
    updateSynonyms('wrong-token')->assertForbidden();
});

it('rejects any token when APP_OPS_TOKEN is not configured', function () {
    config(['mint.app.ops_token' => '']);

    updateSynonyms('')->assertForbidden();
});

it('updates the synonyms path with a valid token', function () {
    $this->mock(OpenSearchService::class)
        ->shouldReceive('updatePaliSynonymsPath')
        ->once()
        ->with('20260731')
        ->andReturn(['path' => 'analysis/pali-synonyms-20260731.txt', 'settings' => ['acknowledged' => true]]);

    updateSynonyms('secret-ops-token')
        ->assertOk()
        ->assertJson([
            'ok' => true,
            'data' => [
                'version' => '20260731',
                'synonyms_path' => 'analysis/pali-synonyms-20260731.txt',
            ],
        ]);
});

it('returns 500 when OpenSearch rejects the update', function () {
    $this->mock(OpenSearchService::class)
        ->shouldReceive('updatePaliSynonymsPath')
        ->once()
        ->andThrow(new Exception('Index [wikipali] does not exist.'));

    updateSynonyms('secret-ops-token')
        ->assertStatus(500)
        ->assertJson(['ok' => false]);
});

it('does not match a version containing path separators', function () {
    updateSynonyms('secret-ops-token', '..%2Fetc')->assertNotFound();
});
