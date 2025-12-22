<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToCompany
{
    /**
     * このTraitをuseしているモデルは、常に「自社(company_id)」にスコープされる。
     * さらに creating 時に company_id が空なら自動で埋める。
     */
    protected static function bootBelongsToCompany(): void
    {
        // ✅ 自社スコープ（ログイン中だけ適用）
        static::addGlobalScope('company', function (Builder $builder) {
            if (app()->runningInConsole()) {
                return; // seeder / migration 等で邪魔しない
            }

            $user = Auth::user();
            if ($user && $user->company_id) {
                $builder->where($builder->getModel()->getTable() . '.company_id', $user->company_id);
            }
        });

        // ✅ 作成時に company_id 自動付与（入れ忘れ防止）
        static::creating(function ($model) {
            if (app()->runningInConsole()) {
                return;
            }

            $user = Auth::user();
            if ($user && $user->company_id && empty($model->company_id)) {
                $model->company_id = $user->company_id;
            }
        });
    }

    /**
     * 管理者など、全社横断で見たい場合に使う（明示的に解除）
     * 例: Model::withoutCompany()->get();
     */
    public function scopeWithoutCompany(Builder $query): Builder
    {
        return $query->withoutGlobalScope('company');
    }

    /**
     * 任意の会社に絞りたい場合（管理者用）
     * 例: Model::withoutCompany()->forCompany($id)->get();
     */
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->withoutGlobalScope('company')->where('company_id', $companyId);
    }
}
