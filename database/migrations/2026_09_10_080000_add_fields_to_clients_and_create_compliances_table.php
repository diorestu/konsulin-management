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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('client_code')->nullable()->after('id');
            $table->date('migration_date')->nullable()->after('name');
            $table->string('client_pic')->nullable()->after('migration_date');
            $table->string('location')->nullable()->after('client_pic');
            $table->string('tax_status')->nullable()->after('location'); // PKP, Non-PKP, etc.
            $table->string('business_type')->nullable()->after('tax_status');
            $table->string('contract_status')->nullable()->after('business_type'); // Active, In Review, Ended
            $table->date('start_date')->nullable()->after('contract_status');
            $table->integer('contract_duration_months')->nullable()->after('start_date');
            $table->date('end_contract_due_date')->nullable()->after('contract_duration_months');
            $table->string('client_type')->nullable()->after('end_contract_due_date'); // Badan, Orang Pribadi
            $table->string('finance_package')->nullable()->after('client_type');
            $table->string('tax_package')->nullable()->after('finance_package');
            $table->string('addon')->nullable()->after('tax_package');
            $table->text('package_detail')->nullable()->after('addon');
            $table->string('status')->default('active')->after('package_detail');
            $table->text('files')->nullable()->after('status'); // file link or attachment path
            $table->string('review_approval')->nullable()->after('files'); // Approved, Pending, etc.
            $table->string('tax_pic')->nullable()->after('review_approval');
            $table->string('accounting_pic')->nullable()->after('tax_pic');
        });

        Schema::create('client_compliances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('period'); // e.g. "Mar 26", "Apr 26", ... "Dec 26"
            $table->string('pph_21')->nullable();
            $table->string('pph_unifikasi')->nullable();
            $table->string('ppn')->nullable();
            $table->string('pp_55')->nullable();
            $table->string('pph_25')->nullable();
            $table->string('lk')->nullable(); // Laporan Keuangan
            $table->text('notes')->nullable(); // Pending / Notes
            $table->timestamps();

            $table->unique(['client_id', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_compliances');

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'client_code',
                'migration_date',
                'client_pic',
                'location',
                'tax_status',
                'business_type',
                'contract_status',
                'start_date',
                'contract_duration_months',
                'end_contract_due_date',
                'client_type',
                'finance_package',
                'tax_package',
                'addon',
                'package_detail',
                'status',
                'files',
                'review_approval',
                'tax_pic',
                'accounting_pic',
            ]);
        });
    }
};
