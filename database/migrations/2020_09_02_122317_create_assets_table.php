<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('familyid');  //ファミリーID
            $table->string('familyname');  //ファミリー名
            $table->integer('iid');  //相続人ID
            $table->string('iname');  //相続人名
            $table->string('category');  //資産カテゴリー
            $table->string('assetname')->nullable();  //資産名
            $table->integer('hihokenid')->nullable();  //被保険者ID
            $table->integer('ukeid')->nullable();  //保険受取人ID
            $table->integer('zoyoid')->nullable();  //贈与者ID
            $table->integer('zoyoy')->nullable();  //贈与年
            $table->integer('suryo')->nullable();  //数量
            $table->integer('kingaku');  //金額（万円）
            $table->integer('zoyozei')->nullable();  //贈与税（万円）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assets');
    }
}
