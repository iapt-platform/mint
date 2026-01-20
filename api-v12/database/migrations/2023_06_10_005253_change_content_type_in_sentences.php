<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('sentences', function (Blueprint $table) {
            //
            $table->string('content_type', 32)->default('markdown')->change();
            $sql = "ALTER TABLE sentences DROP CONSTRAINT sentences_content_type_check";
            DB::connection()->getPdo()->exec($sql);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('sentences', function (Blueprint $table) {
            //
            $sql = "ALTER TABLE sentences ADD CONSTRAINT sentences_content_type_check
                        CHECK(content_type::text = ANY (ARRAY['markdown'::character varying::text,
                        'text'::character varying::text,
                        'html'::character varying::text]))";
            DB::connection()->getPdo()->exec($sql);
        });
    }
};
