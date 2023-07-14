<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBooksAttrsMapping extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books_attrs_mapping', function (Blueprint $table) {
            $table->id('id')->comment('Id');
            $table->integer('booksId')->comment('書籍Id');
            $table->string('attrsId')->comment('屬性Id');
            $table->timestamps();
        });


        DB::statement("ALTER TABLE `books_attrs_mapping` comment '書籍跟屬性mapping'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books_attrs_mapping');
    }
}
