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
        Schema::create('user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('id_divisi')->unsigned();
            $table->string('name');
            $table->string('username')->unique()->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'superadmin'])->default('superadmin');
            $table->text('alamat')->nullable();
            $table->string('email')->unique();
            $table->rememberToken();
            $table->timestamps(false);

            $table->foreign('id_divisi')->references('id')->on('divisi')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
