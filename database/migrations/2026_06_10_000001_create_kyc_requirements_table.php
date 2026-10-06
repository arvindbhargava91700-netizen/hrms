<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_requirements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('label');
            $table->string('key')->unique();
            $table->enum('field_type', ['text', 'document'])->default('text');
            $table->enum('input_type', ['text', 'number', 'email', 'tel'])->default('text');
            $table->boolean('has_value_field')->default(true);
            $table->enum('document_mode', ['single', 'front_back'])->default('single');
            $table->string('value_label')->nullable();
            $table->string('placeholder')->nullable();
            $table->string('help_text')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('kyc_requirements')->insert([
            [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'label' => 'Aadhaar Card',
                'key' => 'aadhaar_card',
                'field_type' => 'document',
                'input_type' => 'text',
                'has_value_field' => true,
                'document_mode' => 'front_back',
                'value_label' => 'Aadhaar Number',
                'placeholder' => '12 digit Aadhaar number',
                'help_text' => 'Upload Aadhaar front and back side images.',
                'is_required' => true,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'label' => 'PAN Card',
                'key' => 'pan_card',
                'field_type' => 'document',
                'input_type' => 'text',
                'has_value_field' => true,
                'document_mode' => 'front_back',
                'value_label' => 'PAN Number',
                'placeholder' => '10 character PAN',
                'help_text' => 'Upload PAN front and back side images.',
                'is_required' => true,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'label' => 'GST Number',
                'key' => 'gst_number',
                'field_type' => 'text',
                'input_type' => 'text',
                'has_value_field' => false,
                'document_mode' => 'single',
                'value_label' => null,
                'placeholder' => '15 character GSTIN',
                'help_text' => null,
                'is_required' => false,
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'label' => 'Account Holder Name',
                'key' => 'account_name',
                'field_type' => 'text',
                'input_type' => 'text',
                'has_value_field' => false,
                'document_mode' => 'single',
                'value_label' => null,
                'placeholder' => 'Name on bank account',
                'help_text' => null,
                'is_required' => true,
                'is_active' => true,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'label' => 'Bank Name',
                'key' => 'bank_name',
                'field_type' => 'text',
                'input_type' => 'text',
                'has_value_field' => false,
                'document_mode' => 'single',
                'value_label' => null,
                'placeholder' => 'e.g. HDFC Bank',
                'help_text' => null,
                'is_required' => true,
                'is_active' => true,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'label' => 'Account Number',
                'key' => 'account_number',
                'field_type' => 'text',
                'input_type' => 'number',
                'has_value_field' => false,
                'document_mode' => 'single',
                'value_label' => null,
                'placeholder' => 'Bank account number',
                'help_text' => null,
                'is_required' => true,
                'is_active' => true,
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'label' => 'IFSC Code',
                'key' => 'ifsc_code',
                'field_type' => 'text',
                'input_type' => 'text',
                'has_value_field' => false,
                'document_mode' => 'single',
                'value_label' => null,
                'placeholder' => 'Bank IFSC code',
                'help_text' => null,
                'is_required' => true,
                'is_active' => true,
                'sort_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_requirements');
    }
};
