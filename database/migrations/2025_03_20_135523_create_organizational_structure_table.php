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
        Schema::create('organizational_structure', function (Blueprint $table) {
            $table->id();
            $table->string('profile_img');
            $table->string('fname');
            $table->string('mid_initial')->nullable();
            $table->string('lname');
            $table->string('position')->nullable();;
            $table->string('management_and_administrative_roles')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizational_structure');
    }
};
