<?php

use App\Services\OpenSearchService;

it('points the index at the given synonyms file', function () {
    $this->mock(OpenSearchService::class)
        ->shouldReceive('updatePaliSynonymsPath')
        ->once()
        ->with('aaa.txt')
        ->andReturn(['path' => '/srv/opensearch/synonyms/aaa.txt', 'settings' => ['acknowledged' => true]]);

    $this->artisan('export:pali.synonyms.set-path', ['--name' => 'aaa.txt'])
        ->expectsOutputToContain('/srv/opensearch/synonyms/aaa.txt')
        ->assertSuccessful();
});

it('fails when OpenSearch rejects the update', function () {
    $this->mock(OpenSearchService::class)
        ->shouldReceive('updatePaliSynonymsPath')
        ->once()
        ->andThrow(new Exception('Index [wikipali] does not exist.'));

    $this->artisan('export:pali.synonyms.set-path', ['--name' => 'aaa.txt'])
        ->assertFailed();
});

it('rejects a name that is missing or contains a path', function (?string $name) {
    $this->mock(OpenSearchService::class)->shouldNotReceive('updatePaliSynonymsPath');

    $this->artisan('export:pali.synonyms.set-path', $name === null ? [] : ['--name' => $name])
        ->assertFailed();
})->with([null, '', '../etc/passwd', 'dir/aaa.txt', '.hidden']);
