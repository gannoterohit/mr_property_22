<?php

namespace App\Http\Controllers;

use App\Models\RoomDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OwnerRoomDraftController extends Controller
{
    public function index()
    {
        $drafts = RoomDraft::where('user_id', Auth::id())
            ->where('is_published', false)
            ->orderByDesc('updated_at')
            ->paginate(12);

        return view('owner.rooms.drafts', compact('drafts'));
    }

    public function save(Request $request)
    {
        $userId = Auth::id();
        $draftId = $request->input('draft_id');

        $validator = Validator::make($request->all(), [
            'draft_id'  => ['nullable', 'integer'],
            'step'      => ['required', 'integer', 'min:1', 'max:6'],
            'data'      => ['required', 'array'],
            'title'     => ['nullable', 'string', 'max:255'],
            'photos'    => ['nullable', 'array', 'max:5'],
            'photos.*'  => ['string', 'not_regex:/\.\./', 'regex:/^room_photo\/[A-Za-z0-9._-]+(?:\/[A-Za-z0-9._-]+)*$/'],
            'video_path' => ['nullable', 'string', 'not_regex:/\.\./', 'regex:/^rooms\/videos\/[A-Za-z0-9._-]+(?:\/[A-Za-z0-9._-]+)*$/'],
            'video_url' => ['nullable', 'url', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $payload = $request->input('data');

        $draft = null;
        if ($draftId) {
            $draft = RoomDraft::where('id', $draftId)
                ->where('user_id', $userId)
                ->first();

            if (!$draft) {
                return response()->json([
                    'success' => false,
                    'message' => 'Draft not found',
                ], 404);
            }
        }

        DB::beginTransaction();
        try {
            if (!$draft) {
                $draft = RoomDraft::create([
                    'user_id'      => $userId,
                    'title'        => $request->input('title') ?: ($payload['title'] ?? null),
                    'step'         => $request->input('step'),
                    'data'         => $payload,
                    'photos'       => $request->input('photos', []),
                    'video_path'   => $request->input('video_path'),
                    'video_url'    => $request->input('video_url'),
                    'is_published' => false,
                    'last_saved_at'=> now(),
                    'expires_at'   => now()->addDays(30),
                ]);
            } else {
                $existingPhotos = $draft->photos ?? [];
                $newPhotos = $request->input('photos', $existingPhotos);
                $mergedPhotos = is_array($newPhotos) ? array_values(array_unique(array_merge($existingPhotos, $newPhotos))) : $existingPhotos;

                $draft->fill([
                    'title'       => $request->input('title') ?: ($payload['title'] ?? $draft->title),
                    'step'        => max((int) $draft->step, (int) $request->input('step')),
                    'data'        => array_merge($draft->data ?? [], $payload),
                    'photos'      => $mergedPhotos,
                    'video_path'  => $request->input('video_path', $draft->video_path),
                    'video_url'   => $request->input('video_url', $draft->video_url),
                ])->save();

                $draft->touchSaved();
            }

            DB::commit();

            return response()->json([
                'success'   => true,
                'message'   => 'Draft saved successfully',
                'draft'     => [
                    'id'            => $draft->id,
                    'title'         => $draft->displayTitle(),
                    'step'          => $draft->step,
                    'step_name'     => RoomDraft::STEP_NAMES[$draft->step] ?? 'Step ' . $draft->step,
                    'progress'      => $draft->progressPercent(),
                    'last_saved_at' => $draft->last_saved_at?->toIso8601String(),
                    'photos_count'  => count($draft->photos ?? []),
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save draft: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function load($id)
    {
        $draft = RoomDraft::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        if ($draft->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'This draft has expired.',
            ], 410);
        }

        return response()->json([
            'success' => true,
            'draft'   => [
                'id'           => $draft->id,
                'title'        => $draft->displayTitle(),
                'step'         => $draft->step,
                'step_name'    => RoomDraft::STEP_NAMES[$draft->step] ?? '',
                'data'         => $draft->data,
                'photos'       => $draft->photos ?? [],
                'video_path'   => $draft->video_path,
                'video_url'    => $draft->video_url,
                'last_saved_at'=> $draft->last_saved_at?->toIso8601String(),
            ],
        ]);
    }

    public function destroy($id)
    {
        $draft = RoomDraft::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        if (!empty($draft->photos)) {
            foreach ($draft->photos as $photo) {
                if (Storage::disk('public')->exists($photo)) {
                    Storage::disk('public')->delete($photo);
                }
            }
        }
        if ($draft->video_path && Storage::disk('public')->exists($draft->video_path)) {
            Storage::disk('public')->delete($draft->video_path);
        }

        $draft->delete();

        return response()->json([
            'success' => true,
            'message' => 'Draft deleted successfully',
        ]);
    }

    public function latest()
    {
        $draft = RoomDraft::where('user_id', Auth::id())
            ->where('is_published', false)
            ->orderByDesc('updated_at')
            ->first();

        if (!$draft) {
            return response()->json(['success' => true, 'draft' => null]);
        }

        return response()->json([
            'success' => true,
            'draft'   => [
                'id'            => $draft->id,
                'title'         => $draft->displayTitle(),
                'step'          => $draft->step,
                'step_name'     => RoomDraft::STEP_NAMES[$draft->step] ?? '',
                'progress'      => $draft->progressPercent(),
                'last_saved_at' => $draft->last_saved_at?->toIso8601String(),
                'updated_at'    => $draft->updated_at->toIso8601String(),
            ],
        ]);
    }
}
