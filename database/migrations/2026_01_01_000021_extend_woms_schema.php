<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->decimal('preset_tare', 12, 2)->nullable()->after('capacity')->comment('Stored tare kg for net-weight mode');
        });

        Schema::table('weighbridge_tickets', function (Blueprint $table) {
            $table->foreignId('station_id')->nullable()->after('id')->constrained('weighbridge_stations')->nullOnDelete();
            $table->string('weighing_mode', 20)->default('standard')->after('product_id');
            $table->string('supplier')->nullable()->after('weighing_mode');
            $table->string('carrier')->nullable()->after('supplier');
            $table->string('origin')->nullable()->after('carrier');
            $table->string('destination')->nullable()->after('origin');
            $table->string('goods_type')->nullable()->after('destination');
            $table->decimal('deduction_percentage', 8, 4)->default(0)->after('net_weight');
            $table->decimal('deduction_weight', 12, 2)->nullable()->after('deduction_percentage');
            $table->decimal('actual_weight', 12, 2)->nullable()->after('deduction_weight');
            $table->decimal('unit_price', 12, 4)->nullable()->after('actual_weight');
            $table->decimal('total_amount', 14, 2)->nullable()->after('unit_price');
            $table->decimal('weight_one', 12, 2)->nullable()->after('total_amount')->comment('Simple mode first capture');
            $table->decimal('weight_two', 12, 2)->nullable()->after('weight_one')->comment('Simple mode second capture');
            $table->unsignedTinyInteger('simple_capture_count')->default(0)->after('weight_two');
        });

        // Remap legacy statuses to the WOMS lifecycle names.
        DB::table('weighbridge_tickets')->where('status', 'GROSS_CAPTURED')->update(['status' => 'AWAITING_TARE']);
        DB::table('weighbridge_tickets')->where('status', 'TARE_PENDING')->update(['status' => 'AWAITING_TARE']);
        DB::table('weighbridge_tickets')->where('status', 'PAID')->update(['status' => 'CLOSED']);

        Schema::table('weight_invoices', function (Blueprint $table) {
            $table->decimal('actual_weight', 12, 2)->nullable()->after('net_weight')->comment('Billable kg after deduction');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('cash_session_id')->nullable()->after('invoice_id')->constrained('cash_sessions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_session_id');
        });

        Schema::table('weight_invoices', function (Blueprint $table) {
            $table->dropColumn('actual_weight');
        });

        DB::table('weighbridge_tickets')->where('status', 'AWAITING_TARE')->update(['status' => 'GROSS_CAPTURED']);
        DB::table('weighbridge_tickets')->where('status', 'AWAITING_GROSS')->update(['status' => 'CREATED']);
        DB::table('weighbridge_tickets')->where('status', 'CLOSED')->update(['status' => 'PAID']);

        Schema::table('weighbridge_tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('station_id');
            $table->dropColumn([
                'weighing_mode',
                'supplier',
                'carrier',
                'origin',
                'destination',
                'goods_type',
                'deduction_percentage',
                'deduction_weight',
                'actual_weight',
                'unit_price',
                'total_amount',
                'weight_one',
                'weight_two',
                'simple_capture_count',
            ]);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('preset_tare');
        });
    }
};
