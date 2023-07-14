<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksUnitsCardTopics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books_units_cards_topics', function (Blueprint $table) {
            $table->id('topicsId')->comment('Id');
            $table->integer('cardsId')->comment('卡片Id');
            $table->string('topic',800)->comment('題目');
            $table->string('topicFileType')->comment('題目檔案類型 (影片 圖片 音檔)');
            $table->string('pathOfTopicVideo')->comment('題目影片路徑');
            $table->string('pathOfTopicImage')->comment('題目圖片路徑');
            $table->string('pathOfTopicSound')->comment('題目音檔路徑');
            $table->string('topicCategory')->comment('題目類型( 單選 ) 多選 填空');

            $table->string('optionA')->comment('選項A');
            $table->string('pathOfOptionA')->comment('選項A音檔');

            $table->string('optionB')->comment('選項B');
            $table->string('pathOfOptionB')->comment('選項B音檔');

            $table->string('optionC')->comment('選項C');
            $table->string('pathOfOptionC')->comment('選項C音檔');

            $table->string('optionD')->comment('選項D');
            $table->string('pathOfOptionD')->comment('選項D音檔');

            $table->string('answerOptions')->comment('答案選項( 填空)');
            $table->string('answer')->comment('答案');

            //狀態
            $table->tinyInteger('isEnabled')->default(0)->comment('啟用停用');

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

        DB::statement("ALTER TABLE `books_units_cards_topics` comment '題目'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books_units_card_topics');
    }
}
