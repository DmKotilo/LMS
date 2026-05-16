<?php

declare(strict_types=1);

namespace App\MoonShine\Handlers;

use App\Modules\Bonus\Bonus\src\Enums\BonusManualReasonEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use Bonus\Models\UserBonusHistory;
use Bonus\Services\BonusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Laravel\Handlers\Handler;
use MoonShine\Support\Enums\HttpMethod;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Modal;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Textarea;
use Symfony\Component\HttpFoundation\Response;
use User\Models\User;

final class MassBonusHandler extends Handler
{
    public function getUriKey(): string
    {
        return 'mass-bonus';
    }

    public function getButton(): ActionButtonContract
    {
        $resource = $this->getResource();
        $componentName = $resource->getListComponentName();

        $button = ActionButton::make(
            $this->getLabel(),
            $this->getUrl()
        )
            ->icon('banknotes')
            ->bulk($componentName)
            ->withConfirm(
                title: 'Массовое начисление бонусов',
                content: 'Укажите параметры начисления для выбранных пользователей.',
                button: 'Начислить',
                method: HttpMethod::POST,
                name: 'mass-bonus-modal',
                fields: function () {
                    return [
                        Number::make('Количество', 'amount')
                            ->min(1)
                            ->max(10000)
                            ->step(1)
                            ->required()
                            ->hint('Только целые числа'),
                        Enum::make('Причина', 'reason')
                            ->attach(BonusManualReasonEnum::class)
                            ->required()
                            ->options(
                                collect(BonusManualReasonEnum::forCredit())->mapWithKeys(
                                    fn (string $v): array => [$v => BonusManualReasonEnum::from($v)->toString()]
                                )->all()
                            ),
                        Textarea::make('Комментарий', 'comment')
                            ->nullable(),
                        Date::make('Дата активации', 'active_date')
                            ->nullable()
                            ->withTime()
                            ->format('d.m.Y H:i'),
                        Date::make('Дата истечения', 'expires_at')
                            ->nullable()
                            ->withTime()
                            ->format('d.m.Y H:i'),
                    ];
                },
                formBuilder: static function ($form) use ($resource) {
                    return $form->async(
                        events: [
                            $resource->getListEventName(
                                $resource->getListComponentName(),
                                array_filter([
                                    'page' => request()->getScalar('page'),
                                    'sort' => request()->getScalar('sort'),
                                    'filter' => request()->get('filter'),
                                ])
                            ),
                        ],
                        callback: null
                    );
                },
                modalBuilder: static fn (Modal $modal): Modal => $modal->wide(),
            );

        return $this->prepareButton($button);
    }

    public function handle(): Response
    {
        $ids = request()->input('ids', []);
        $ids = \is_array($ids) ? array_filter($ids) : [];

        if ($ids === []) {
            if (request()->ajax()) {
                return response()->json(['message' => 'Выберите хотя бы одного пользователя.'], 422);
            }
            return redirect()->back()->withErrors(['ids' => 'Выберите хотя бы одного пользователя.']);
        }

        $validator = Validator::make(request()->all(), [
            'amount' => ['required', 'integer', 'min:1', 'max:100000'],
            'reason' => ['required', 'string', 'in:' . implode(',', BonusManualReasonEnum::forCredit())],
            'comment' => ['nullable', 'string', 'max:1000'],
            'active_date' => ['nullable', 'string'],
            'expires_at' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            if (request()->ajax()) {
                return response()->json(['message' => $validator->errors()->first()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $amount = (float) request()->input('amount');
        $reason = BonusManualReasonEnum::from(request()->input('reason'));
        $comment = request()->input('comment');
        $activeDate = request()->input('active_date') ? \Carbon\Carbon::parse(request()->input('active_date')) : null;
        $expiresAt = request()->input('expires_at') ? \Carbon\Carbon::parse(request()->input('expires_at')) : null;
        $adminId = auth()->guard('moonshine')->id();

        $bonusService = app(BonusService::class);
        $processed = 0;
        $errors = [];

        foreach ($ids as $id) {
            $user = User::find($id);
            if (! $user) {
                continue;
            }
            try {
                // Создаём запись в истории начислений (как при ручном начислении в BonusHistoryResource)
                $history = UserBonusHistory::create([
                    'user_id' => $user->id,
                    'order_id' => null,
                    'amount' => $amount,
                    'remaining_amount' => 0,
                    'type' => BonusTypeEnum::Accrual,
                    'status' => BonusStatusEnum::Active,
                    'active_date' => $activeDate,
                    'expires_at' => $expiresAt,
                    'bonus_card_id' => null,
                    'is_manual' => false,
                    'reason' => $reason,
                    'admin_id' => $adminId,
                    'comment' => $comment,
                ]);
                // Дозаполняем карту, даты, is_manual и т.д. (как в completeManualAccrual)
                $bonusService->completeManualAccrual($history, $user, $adminId);
                $processed++;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('MassBonusHandler: ошибка начисления', [
                    'user_id' => $user->id,
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $errors[] = "Пользователь #{$user->id}: " . $e->getMessage();
            }
        }

        $message = "Начислено бонусов: {$processed} из " . count($ids);
        if ($errors !== []) {
            $message .= '. Ошибки: ' . implode('; ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $message .= '… (ещё ' . (count($errors) - 3) . ')';
            }
        }

        if (request()->ajax()) {
            return response()->json(['message' => $message]);
        }

        /** @var RedirectResponse $redirect */
        $redirect = redirect($this->getResource()?->getUrl() ?? moonshineRouter()->getEndpoints()->home());
        return $redirect->with('alert', $message);
    }
}
