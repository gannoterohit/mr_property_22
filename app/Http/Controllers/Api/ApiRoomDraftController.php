<?php

namespace App\Http\Controllers\Api;

use App\Models\RoomDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ApiRoomDraftController extends BaseApiController
{
    public function index(Request $request)
    {
        $drafts = RoomDraft::where('user_id', $request->user()->id)
            ->where('is_published', false)
            ->latest('updated_at')
            ->paginate(max(1, min(50, $request->integer('limit', 15))));

        return $this->sendSuccess($drafts, 'Drafts fetched successfully.');
    }

    public function latest(Request $request)
    {
        $draft = RoomDraft::where('user_id', $request->user()->id)
            ->where('is_published', false)
            ->latest('updated_at')
            ->first();

        return $this->sendSuccess($draft ? $this->summary($draft, true) : null, 'Latest draft fetched successfully.');
    }

    public function show(Request $request, int $id)
    {
        $draft = $this->draft($request, $id);
        if ($draft->isExpired()) {
            return $this->sendError('This draft has expired.', [], 410);
        }

        return $this->sendSuccess($this->summary($draft, false), 'Draft fetched successfully.');
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'draft_id' => ['nullable', 'integer'],
            'step' => ['required', 'integer', 'min:1', 'max:6'],
            'data' => ['required', 'array'],
            'title' => ['nullable', 'string', 'max:255'],
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['string', 'not_regex:/\.\./', 'regex:/^room_photo\/[A-Za-z0-9._-]+(?:\/[A-Za-z0-9._-]+)*$/'],
            'video_path' => ['nullable', 'string', 'not_regex:/\.\./', 'regex:/^rooms\/videos\/[A-Za-z0-9._-]+(?:\/[A-Za-z0-9._-]+)*$/'],
            'video_url' => ['nullable', 'url', 'max:255'],
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation failed.', $validator->errors(), 422);
        }

        $draft = $request->filled('draft_id') ? RoomDraft::where('user_id', $request->user()->id)->find($request->draft_id) : null;
        if ($request->filled('draft_id') && !$draft) {
            return $this->sendError('Draft not found.', [], 404);
        }

        $data = $request->input('data');
        DB::transaction(function () use ($request, &$draft, $data): void {
            if (!$draft) {
                $draft = RoomDraft::create([
                    'user_id' => $request->user()->id,
                    'title' => $request->input('title') ?: ($data['title'] ?? null),
                    'step' => $request->integer('step'),
                    'data' => $data,
                    'photos' => $request->input('photos', []),
                    'video_path' => $request->input('video_path'),
                    'video_url' => $request->input('video_url'),
                    'is_published' => false,
                    'last_saved_at' => now(),
                    'expires_at' => now()->addDays(30),
                ]);
                return;
            }

            $photos = array_values(array_unique(array_merge($draft->photos ?? [], $request->input('photos', $draft->photos ?? []))));
            $draft->fill([
                'title' => $request->input('title') ?: ($data['title'] ?? $draft->title),
                'step' => max((int) $draft->step, $request->integer('step')),
                'data' => array_merge($draft->data ?? [], $data),
                'photos' => $photos,
                'video_path' => $request->input('video_path', $draft->video_path),
                'video_url' => $request->input('video_url', $draft->video_url),
            ])->save();
            $draft->touchSaved();
        });

        return $this->sendSuccess($this->summary($draft, true), 'Draft saved successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $draft = $this->draft($request, $id);
        foreach (array_filter(array_merge($draft->photos ?? [], [$draft->video_path])) as $path) {
            Storage::disk('public')->delete($path);
        }
        $draft->delete();

        return $this->sendSuccess([], 'Draft deleted successfully.');
    }

    private function draft(Request $request, int $id): RoomDraft
    {
        return RoomDraft::where('user_id', $request->user()->id)->findOrFail($id);
    }

    private function summary(RoomDraft $draft, bool $short): array
    {
        $result = [
            'id' => $draft->id,
            'title' => $draft->displayTitle(),
            'step' => $draft->step,
            'step_name' => RoomDraft::STEP_NAMES[$draft->step] ?? 'Step '.$draft->step,
            'last_saved_at' => $draft->last_saved_at?->toIso8601String(),
        ];
        if ($short) {
            return $result + ['progress' => $draft->progressPercent(), 'photos_count' => count($draft->photos ?? [])];
        }

        return $result + [
            'data' => $draft->data,
            'photos' => $draft->photos ?? [],
            'video_path' => $draft->video_path,
            'video_url' => $draft->video_url,
        ];
    }
}
