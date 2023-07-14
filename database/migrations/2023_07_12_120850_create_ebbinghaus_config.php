<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEbbinghausConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ebbinghaus_config', function (Blueprint $table) {
            $table->id();
            $table->string('stage', 255)->nullable()->comment('階段代碼');
            $table->integer('score')->default(0)->comment('分數');
            $table->integer('minute')->default(0)->comment('間隔分鐘');
            $table->integer('day')->default(0)->comment('間格天數');
            $table->integer('expriedTime')->default(0)->comment('過期時間');
            $table->integer('expiredDay')->default(0)->comment('過期天數');

            $table->integer('amount')->default(0)->comment('金幣獎勵(任務完成 額外給予)');

            //基本時間
            $table->timestamps();
            $table->string('creator', 255)->nullable()->comment('建立者');
            $table->string('ipOfCreator', 255)->nullable()->comment('建立者IP');
            $table->string('lastUpdater', 255)->nullable()->comment('最後更新者');
            $table->string('ipOfLastUpdater', 255)->nullable()->comment('最後更新者IP');
            $table->tinyInteger('isRemoved')->default(0)->comment('是否刪除');
            $table->dateTime('removeTime')->nullable()->comment('刪除時間');
            $table->string('remover', 255)->nullable()->comment('刪除者');
            $table->string('ipOfRemover', 255)->nullable()->comment('刪除者IP');
            //備註
            $table->string('memo')->nullable()->comment('備註');
        });

        DB::statement("ALTER TABLE `ebbinghaus_config` comment '愛彬豪斯級距設定'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ebbinghaus_config');
    }
}
