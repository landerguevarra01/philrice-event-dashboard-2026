<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signatory', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->primary();   // always 1: the app only ever updates this row
            $table->string('name');
            $table->string('role');
            $table->timestamp('updated_at')->nullable();
        });

        DB::table('signatory')->insert([
            'id' => 1,
            'name' => 'Ommal H. Abdulkadil',
            'role' => 'Branch Director, DA-PhilRice Negros',
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('signatory');
    }
};
