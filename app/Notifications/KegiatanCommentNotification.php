<?php

namespace App\Notifications;

use App\Models\Kegiatan;
use App\Models\KegiatanComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KegiatanCommentNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Kegiatan $kegiatan,
        private readonly KegiatanComment $comment,
        private readonly User $commenter,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Komentar baru pada kegiatan',
            'message' => $this->commenter->name . ' memberi komentar pada kegiatan ' . $this->kegiatan->nama . '.',
            'kegiatan_id' => $this->kegiatan->id,
            'kegiatan_name' => $this->kegiatan->nama,
            'comment_id' => $this->comment->id,
            'comment' => $this->comment->comment,
            'commenter_id' => $this->commenter->id,
            'commenter_name' => $this->commenter->name,
            'route' => route('kegiatan.show', $this->kegiatan),
        ];
    }
}