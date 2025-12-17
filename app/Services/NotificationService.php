<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;

class NotificationService
{
    /**
     * Send a notification to a user.
     *
     * @param User $user
     * @param string $channel
     * @param string $message
     * @param string|null $subject
     * @return Notification
     */
    public function sendNotification(User $user, string $channel, string $message, ?string $subject = null): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'channel' => $channel,
            'subject' => $subject,
            'message' => $message,
            'status' => Notification::STATUS_PENDING,
        ]);
    }

    /**
     * Send account activity notification.
     *
     * @param Account $account
     * @param Transaction $transaction
     * @return void
     */
    public function sendAccountActivityNotification(Account $account, Transaction $transaction): void
    {
        $message = "Your account {$account->account_number} has been {$transaction->type}ed with amount {$transaction->amount}.";
        $subject = "Account Activity: {$transaction->type}";

        $this->sendNotification($account->user, Notification::CHANNEL_EMAIL, $message, $subject);
        $this->sendNotification($account->user, Notification::CHANNEL_IN_APP, $message, $subject);
        
        // For large transactions, also send SMS
        if ($transaction->amount > 1000) {
            $smsMessage = "Large transaction alert: {$transaction->type} of {$transaction->amount} on account {$account->account_number}";
            $this->sendNotification($account->user, Notification::CHANNEL_SMS, $smsMessage);
        }
    }

    /**
     * Get pending notifications for a user.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingNotifications(User $user)
    {
        return $user->notifications()->pending()->get();
    }

    /**
     * Mark notification as sent.
     *
     * @param Notification $notification
     * @return Notification
     */
    public function markAsSent(Notification $notification): Notification
    {
        return $notification->update([
            'status' => Notification::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark notification as failed.
     *
     * @param Notification $notification
     * @return Notification
     */
    public function markAsFailed(Notification $notification): Notification
    {
        return $notification->update([
            'status' => Notification::STATUS_FAILED,
        ]);
    }
}