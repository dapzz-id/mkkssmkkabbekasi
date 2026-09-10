<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('konten', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('id_user')->unsigned()->required();
            $table->bigInteger('id_divisi')->unsigned()->required();
            $table->longtext('url_media');
            $table->dateTime('tanggal_upload');
            $table->text('judul');
            $table->longText('deskripsi');

            $table->foreign('id_user')->references('id')->on('user')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('id_divisi')->references('id')->on('divisi')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('konten');
    }
};
