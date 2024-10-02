<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameUuidToUidInChannels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('channels', function (Blueprint $table) {
            //
        });

        DB::transaction(function () {
            $sql = 'UPDATE channels SET uuid = uuid(uid);';
            DB::select($sql);
            $sql = "ALTER TABLE channels DROP COLUMN uid;";
            DB::select($sql);
            $sql = "DROP INDEX IF EXISTS channels_uid_unique ;";
            DB::select($sql);
            $sql = "DROP INDEX IF EXISTS channels_uuid_unique ;";
            DB::select($sql);
            $sql = "ALTER TABLE channels RENAME COLUMN uuid TO uid;";
            DB::select($sql);
            $sql = "CREATE UNIQUE INDEX IF NOT EXISTS channels_uid_unique ON channels (uid);";
            DB::select($sql);
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('channels', function (Blueprint $table) {
            //
            //$table->uuid('uuid')->default(DB::raw('uuid_generate_v1mc()'));

        });
        DB::transaction(function () {
            $sql = "ALTER TABLE public.channels ADD uuid uuid NOT NULL DEFAULT uuid_generate_v1mc();";
            DB::select($sql);
            $sql = "CREATE UNIQUE INDEX IF NOT EXISTS channels_uuid_unique ON channels (uuid);";
            DB::select($sql);
            $sql = 'UPDATE channels SET uuid = uuid(uid);';
            DB::select($sql);
        });
    }
}
