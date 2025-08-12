<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->string('user_id', 50);
            $table->string('user_name', 20);
            $table->text('content');
            $table->timestamps();

            // 外部キー制約
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');

            // インデックスを追加（パフォーマンス向上のため）
            $table->index('post_id');
            $table->index('user_id');
        });
    }


    public function down()
    {
        Schema::dropIfExists('comments');
    }
}
