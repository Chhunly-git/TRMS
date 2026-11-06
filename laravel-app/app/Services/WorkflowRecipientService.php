<?php

namespace App\Services;

use App\Models\DocumentWorkflowConfig;
use App\Models\InboundDocumentResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WorkflowRecipientService
{
    /**
     * Build the Query of allowed recipients for a given user based on DocumentWorkflowConfig
     */
    public static function allowedRecipientsQuery(User $user): Builder
    {
        $viewerPosLevel = $user->position ? (int) $user->position->level : 99;
        $isDg = ($viewerPosLevel <= 1) || (strtoupper($user->level ?? '') === 'ADMIN');

        $query = User::whereNotIn('users.status', ['DISABLED', 'SUSPENDED', 'INACTIVE'])
            ->where('users.id', '!=', $user->id)
            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->select('users.id', 'users.name', 'users.name_kh', 'users.email', 'users.position_id', 'users.department_id', 'users.office_id', 'users.profile_image')
            ->with(['position:id,title_kh,title_en,level', 'department:id,name_kh', 'office:id,name_kh'])
            ->orderByRaw('COALESCE(positions.level, 999) ASC')
            ->orderBy('users.name_kh', 'ASC');

        if ($isDg) {
            return $query;
        }

        $config = DocumentWorkflowConfig::where('user_id', $user->id)->first();

        if ($config) {
            $sendType = $config->send_type ?? 'SUBORDINATES';

            if ($sendType === 'ALL') {
                return $query;
            }

            if ($sendType === 'CUSTOM') {
                $query->where(function ($q) use ($config) {
                    if (!empty($config->send_dept_ids)) {
                        $q->orWhereIn('users.department_id', $config->send_dept_ids);
                    }
                    if (!empty($config->send_office_ids)) {
                        $q->orWhereIn('users.office_id', $config->send_office_ids);
                    }
                    if (!empty($config->send_user_ids)) {
                        $q->orWhereIn('users.id', $config->send_user_ids);
                    }
                    if (!empty($config->send_working_group_ids)) {
                        $q->orWhereHas('workingGroups', function ($wgQ) use ($config) {
                            $wgQ->whereIn('working_groups.id', $config->send_working_group_ids);
                        });
                    }
                });
                return $query;
            }

            if ($sendType === 'OFFICE' && $user->office_id) {
                $query->where('users.office_id', $user->office_id);
                return $query;
            }

            if ($sendType === 'DEPARTMENT' && $user->department_id) {
                $query->where('users.department_id', $user->department_id);
                return $query;
            }
        }

        // Default hierarchy when send_type = SUBORDINATES or no config:
        // Superiors in hierarchy within same dept/office OR DG/Leadership (level <= 2)
        $query->where(function ($q) use ($user, $viewerPosLevel) {
            $q->where(function ($sub) use ($user, $viewerPosLevel) {
                $sub->where('positions.level', '<', $viewerPosLevel)
                    ->where(function ($deptOrOff) use ($user) {
                        if ($user->department_id) {
                            $deptOrOff->where('users.department_id', $user->department_id);
                        }
                        if ($user->office_id) {
                            $deptOrOff->orWhere('users.office_id', $user->office_id);
                        }
                    });
            })->orWhere('positions.level', '<=', 2);
        });

        return $query;
    }

    /**
     * Get Collection of allowed recipients
     */
    public static function allowedRecipients(User $user): Collection
    {
        return self::allowedRecipientsQuery($user)
            ->get()
            ->filter(fn($u) => (int) $u->id !== (int) $user->id)
            ->values();
    }

    /**
     * Check if a sender can forward/send to target user
     */
    public static function canSendTo(User $sender, User $target): bool
    {
        if ((int) $sender->id === (int) $target->id) {
            return false;
        }

        $viewerPosLevel = $sender->position ? (int) $sender->position->level : 99;
        $isDg = ($viewerPosLevel <= 1) || (strtoupper($sender->level ?? '') === 'ADMIN');
        if ($isDg) {
            return true;
        }

        return self::allowedRecipientsQuery($sender)
            ->where('users.id', $target->id)
            ->exists();
    }

    /**
     * Server-side validation of a document forward
     */
    public static function validateForward(User $sender, User $target, InboundDocumentResponse $response): void
    {
        // 1. Verify target is permitted by workflow config
        if (!self::canSendTo($sender, $target)) {
            abort(403, 'លោកអ្នកមិនមានសិទ្ធិបញ្ជូនឯកសារទៅកាន់បុគ្គលនេះឡើយ (តាមការកំណត់ Workflow Sending Config)!');
        }

        // 2. Hard guard: un-numbered document cannot leave its issuing unit unless forwarded by the Unit Head
        if ($response->number_seq === null) {
            $isHead = DocumentNumberService::isUnitHead($sender, $response);
            $isLeaving = DocumentNumberService::isLeavingUnit($response, $target);

            if (!$isHead && $isLeaving) {
                abort(422, 'សេចក្តីព្រាងឯកសារនេះមិនទាន់ត្រូវបានចុះហត្ថលេខាដោយប្រធានអង្គភាពចេញលិខិតនៅឡើយទេ មិនអាចបញ្ជូនចេញក្រៅអង្គភាពបានឡើយ!');
            }
        }
    }
}
