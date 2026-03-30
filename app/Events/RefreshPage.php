<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// class RefreshPage implements ShouldBroadcastNow
// {
//     use Dispatchable, InteractsWithSockets, SerializesModels;

//     public $message;
//     public $targetPage; // الصفحة المستهدفة (مثلاً: attendance أو employees)

//     public function __construct($message, $targetPage = 'all')
//     {
//         $this->message = $message;
//         $this->targetPage = $targetPage;
//     }

//     public function broadcastOn()
//     {
//         return new Channel('global-updates');
//     }
//     public function broadcastAs()
//     {
//         // لضمان أن الاسم يصل للجافاسكربت بدون مسارات إضافية
//         return 'RefreshPage';
//     }
// }
