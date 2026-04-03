<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_courses', function (Blueprint $table): void {
            $table->string('audience_scope', 32)->default('all')->after('catalog_visible');
            $table->json('target_departments')->nullable()->after('audience_scope');
            $table->json('target_roles')->nullable()->after('target_departments');
            $table->json('target_locations')->nullable()->after('target_roles');
        });
    }

    public function down(): void
    {
        Schema::table('learning_courses', function (Blueprint $table): void {
            $table->dropColumn([
                'audience_scope',
                'target_departments',
                'target_roles',
                'target_locations',
            ]);
        });
    }
};

