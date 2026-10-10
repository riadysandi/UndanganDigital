<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Services\InvitationPublishingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublishController extends Controller
{
    public function __construct(
        protected InvitationPublishingService $publishingService
    ) {}

    public function publish(Request $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        try {
            $version = $this->publishingService->publish($invitation, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Undangan berhasil dipublikasikan!',
                'public_url' => url('/i/' . $invitation->slug),
                'version_number' => $version->version_number,
                'published_at' => $version->published_at,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function unpublish(Request $request, Invitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        try {
            $this->publishingService->unpublish($invitation, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Undangan berhasil diubah menjadi draft (unpublish).',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
