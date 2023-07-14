<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksTeacherMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books_teacher_mapping', function (Blueprint $table) {
            $table->id('id')->comment('Id');
            $table->integer('booksId')->comment('書籍Id');
            $table->string('teacherId')->comment('教師Id');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE `books_teacher_mapping` comment '書籍跟教師mapping'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books_teacher_mapping');
    }
}
