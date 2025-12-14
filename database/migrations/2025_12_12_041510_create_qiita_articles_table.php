<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_qiita_articles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('qiita_articles', function (Blueprint $table) {
            $table->id();

            // 誰が送信したか
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // どのミッションと紐づくか（技術ブログミッション）
            $table->foreignId('mission_id')->nullable()->constrained()->nullOnDelete();

            // Qiita側のID
            $table->string('item_id')->index();

            // 記事情報
            $table->string('title');
            $table->longText('body');              // Markdown
            $table->json('tags')->nullable();      // ["php","laravel"] みたいな配列
            $table->unsignedInteger('likes_count')->default(0);
            $table->timestamp('posted_at')->nullable(); // Qiita上の投稿日時
            $table->string('url');

            $table->timestamps();

            // 同じユーザーが同じ記事を重複登録しないように
            $table->unique(['user_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qiita_articles');
    }
};

