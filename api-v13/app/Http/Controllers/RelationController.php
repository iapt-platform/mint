<?php

namespace App\Http\Controllers;

use App\Http\Resources\RelationResource;
use App\Models\Relation;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RelationController extends Controller
{
    /**
     * Vocabulary cache key — shared across requests.
     */
    private const VOCABULARY_CACHE_KEY = 'relation-vocabulary';

    /**
     * Display a listing of the resource.
     *
     * Supports optional filters: case, search, name, from, to, match, category.
     * When the `vocabulary` parameter is present, returns the full unfiltered
     * list from cache (suitable for populating UI dropdowns / autocomplete).
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('vocabulary')) {
            return $this->ok(
                Cache::remember(
                    self::VOCABULARY_CACHE_KEY,
                    config('mint.cache.expire'),
                    fn () => $this->buildVocabularyPayload()
                )
            );
        }

        return $this->ok($this->buildFilteredPayload($request));
    }

    /**
     * Store a newly created resource in storage.
     *
     * Requires authentication. Invalidates the vocabulary cache on success.
     */
    public function store(Request $request): JsonResponse
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), [], 401);
        }

        $validated = $request->validate([
            'name' => 'required',
        ]);

        $relation = new Relation;
        $relation->name = $validated['name'];
        $relation->case = $request->input('case');
        $relation->category = $request->input('category');
        $relation->from = $this->encodeJsonField($request, 'from');
        $relation->to = $this->encodeJsonField($request, 'to');
        $relation->match = $this->encodeJsonField($request, 'match');
        $relation->editor_id = $user['user_uid'];
        $relation->save();

        Cache::forget(self::VOCABULARY_CACHE_KEY);

        return $this->ok(new RelationResource($relation));
    }

    /**
     * Display the specified resource.
     */
    public function show(Relation $relation): JsonResponse
    {
        return $this->ok(new RelationResource($relation));
    }

    /**
     * Update the specified resource in storage.
     *
     * Requires authentication. Invalidates the vocabulary cache on success.
     */
    public function update(Request $request, Relation $relation): JsonResponse
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), [], 401);
        }

        $relation->name = $request->input('name');
        $relation->case = $request->input('case');
        $relation->category = $request->input('category');
        $relation->from = $this->encodeJsonField($request, 'from');
        $relation->to = $this->encodeJsonField($request, 'to');
        $relation->match = $this->encodeJsonField($request, 'match');
        $relation->editor_id = $user['user_uid'];
        $relation->save();

        Cache::forget(self::VOCABULARY_CACHE_KEY);

        return $this->ok(new RelationResource($relation));
    }

    /**
     * Remove the specified resource from storage.
     *
     * Requires authentication. Invalidates the vocabulary cache on success.
     */
    public function destroy(Request $request, Relation $relation): JsonResponse
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), [], 401);
        }

        $deleted = $relation->delete();

        Cache::forget(self::VOCABULARY_CACHE_KEY);

        return $this->ok($deleted);
    }

    /**
     * Export all relations as an XLSX file download.
     *
     * Streams the spreadsheet directly to the browser via php://output.
     * Columns: id, name, from, to, match, category.
     */
    public function export(): void
    {
        $spreadsheet = new Spreadsheet;
        $activeWorksheet = $spreadsheet->getActiveSheet();

        $activeWorksheet->fromArray(['id', 'name', 'from', 'to', 'match', 'category'], null, 'A1');

        $currLine = 2;
        foreach (Relation::cursor() as $row) {
            $activeWorksheet->fromArray(
                [$row->id, $row->name, $row->from, $row->to, $row->match, $row->category],
                null,
                "A{$currLine}"
            );
            $currLine++;
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="relation.xlsx"');
        $writer->save('php://output');
    }

    /**
     * Import relations from an uploaded XLSX file.
     *
     * Requires authentication. Reads the file path from the `filename` request
     * parameter. Existing records are matched by id and updated in place;
     * rows without a matching id are inserted as new records.
     * Invalidates the vocabulary cache on completion.
     */
    public function import(Request $request): JsonResponse
    {
        $user = AuthService::current($request);
        if (! $user) {
            return $this->error(__('auth.failed'), [], 401);
        }

        $filename = $request->input('filename');
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx;
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filename);
        $activeWorksheet = $spreadsheet->getActiveSheet();

        $currLine = 2;
        $countFail = 0;
        $error = '';

        while (true) {
            $name = $activeWorksheet->getCell("B{$currLine}")->getValue();
            if (empty($name)) {
                break;
            }

            $id = $activeWorksheet->getCell("A{$currLine}")->getValue();
            $from = $activeWorksheet->getCell("C{$currLine}")->getValue();
            $to = $activeWorksheet->getCell("D{$currLine}")->getValue();
            $match = $activeWorksheet->getCell("E{$currLine}")->getValue();
            $category = $activeWorksheet->getCell("F{$currLine}")->getValue();

            $row = (! empty($id) ? Relation::find($id) : null) ?? new Relation;

            $row->name = $name;
            $row->from = empty($from) ? null : $from;
            $row->to = $to;
            $row->match = $match;
            $row->category = $category;
            $row->editor_id = $user['user_uid'];
            $row->save();

            $currLine++;
        }

        Cache::forget(self::VOCABULARY_CACHE_KEY);

        $success = $currLine - 2 - $countFail;

        return $this->ok(['success' => $success, 'fail' => $countFail], $error);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Build the full vocabulary payload for caching.
     *
     * ResourceCollection is resolved to a plain PHP array via resolve() before
     * being stored, preventing __PHP_Incomplete_Class_Name deserialization
     * errors when using Redis or file-based cache drivers. RelationResource is
     * responsible for keeping the nested values object-free.
     *
     * @return array{rows: array<int, array<string, mixed>>, count: int}
     */
    private function buildVocabularyPayload(): array
    {
        $rows = Relation::select([
            'id',
            'name',
            'case',
            'from',
            'to',
            'category',
            'editor_id',
            'match',
            'updated_at',
            'created_at',
        ])->orderBy('updated_at', 'desc')->get();

        return [
            'rows' => RelationResource::collection($rows)->resolve(),
            'count' => $rows->count(),
        ];
    }

    /**
     * Build a filtered and paginated payload for standard index requests.
     *
     * Supported query parameters:
     *   - case      (comma-separated)  filter by case values
     *   - search    (string)           prefix match on name
     *   - name      (string)           exact match on name
     *   - from      (string)           JSON contains on from->case
     *   - to        (string)           JSON contains on to
     *   - match     (string)           JSON contains on match
     *   - category  (string)           exact match on category
     *   - order     (string)           column to sort by  (default: updated_at)
     *   - dir       (asc|desc)         sort direction     (default: desc)
     *   - offset    (int)              skip N rows        (default: 0)
     *   - limit     (int)              max rows returned  (default: 1000)
     *
     * @return array{rows: AnonymousResourceCollection, count: int}
     */
    private function buildFilteredPayload(Request $request): array
    {
        $query = Relation::select([
            'id',
            'name',
            'case',
            'from',
            'to',
            'category',
            'editor_id',
            'match',
            'updated_at',
            'created_at',
        ]);

        if ($request->filled('case')) {
            $query->whereIn('case', explode(',', $request->input('case')));
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', $request->input('search').'%');
        }
        if ($request->filled('name')) {
            $query->where('name', $request->input('name'));
        }
        if ($request->filled('from')) {
            $query->whereJsonContains('from->case', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereJsonContains('to', $request->input('to'));
        }
        if ($request->filled('match')) {
            $query->whereJsonContains('match', $request->input('match'));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $query->orderBy($request->input('order', 'updated_at'), $request->input('dir', 'desc'));

        $count = $query->count();
        $rows = $query->skip($request->input('offset', 0))
            ->take($request->input('limit', 1000))
            ->get();

        return [
            'rows' => RelationResource::collection($rows),
            'count' => $count,
        ];
    }

    /**
     * Encode a request field as a JSON string.
     *
     * Returns null when the field is absent from the request, preserving the
     * semantic distinction between "not provided" and an explicit empty value.
     */
    private function encodeJsonField(Request $request, string $field): ?string
    {
        return $request->has($field)
            ? json_encode($request->input($field), JSON_UNESCAPED_UNICODE)
            : null;
    }
}
