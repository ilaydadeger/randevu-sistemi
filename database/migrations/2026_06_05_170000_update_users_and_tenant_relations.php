<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update existing 'nail_tech' roles to 'artist' in users table
        DB::table('users')->where('role', 'nail_tech')->update(['role' => 'artist']);

        // 2. Add slug column to users (nullable initially to allow generation)
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('email');
        });

        // 3. Generate slugs for existing users
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $baseName = $user->name ?: 'artist';
            $slug = Str::slug($baseName);
            $originalSlug = $slug;
            $count = 1;
            
            while (DB::table('users')->where('slug', $slug)->where('id', '!=', $user->id)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
            
            DB::table('users')->where('id', $user->id)->update(['slug' => $slug]);
        }

        // 4. Add unique index to slug now that all values are set
        Schema::table('users', function (Blueprint $table) {
            $table->unique('slug');
            // Modify role to be an enum column
            $table->enum('role', ['super_admin', 'artist'])->default('artist')->change();
        });

        // 5. Update appointments table (Rename user_id to artist_id)
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->renameColumn('user_id', 'artist_id');
            $table->text('client_phone')->change();
        });
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('artist_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 6. Update user_prices table (Rename user_id to artist_id and update unique index)
        Schema::table('user_prices', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique('user_prices_user_id_service_category_id_unique');
            $table->renameColumn('user_id', 'artist_id');
        });
        Schema::table('user_prices', function (Blueprint $table) {
            $table->foreign('artist_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['artist_id', 'service_category_id']);
        });

        // 7. Update pricing_rules table (Rename user_id to artist_id)
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->renameColumn('user_id', 'artist_id');
        });
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->foreign('artist_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 8. Create portfolios table
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artist_id')->constrained('users')->onDelete('cascade');
            $table->string('image_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop portfolios table
        Schema::dropIfExists('portfolios');

        // 2. Revert pricing_rules table
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->dropForeign(['artist_id']);
            $table->renameColumn('artist_id', 'user_id');
        });
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 3. Revert user_prices table
        Schema::table('user_prices', function (Blueprint $table) {
            $table->dropForeign(['artist_id']);
            $table->dropUnique(['artist_id', 'service_category_id']);
            $table->renameColumn('artist_id', 'user_id');
        });
        Schema::table('user_prices', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'service_category_id']);
        });

        // 4. Revert appointments table
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['artist_id']);
            $table->renameColumn('artist_id', 'user_id');
            $table->string('client_phone', 191)->change();
        });
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 5. Revert users table changes
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('nail_tech')->change();
            $table->dropColumn('slug');
        });

        // Restore artist role to nail_tech
        DB::table('users')->where('role', 'artist')->update(['role' => 'nail_tech']);
    }
};
