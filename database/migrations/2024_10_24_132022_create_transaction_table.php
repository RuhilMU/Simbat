<?php

use App\Models\Master\Drug;
use App\Models\Master\Repack;
use App\Models\Master\Vendor;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionDetail;
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
        //banyak field yang nullable karena hanya diperlukan pada beberapa transaksi
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->char('code',13)->nullable();
            $table->foreignIdFor(Vendor::class)->nullable()->constrained()->onDelete('restrict');
            $table->enum('destination',['customer','clinic','warehouse']);
            $table->enum('method',['credit','cash'])->nullable();
            $table->enum('variant',['LPB','LPK','Checkout','Trash','Retur'])->nullable();
            $table->integer('income')->nullable();
            $table->integer('outcome')->nullable();
            $table->integer('profit')->nullable();
            $table->integer('loss')->nullable();
            $table->integer('discount')->nullable();
            $table->timestamps();
        });
        //detail memiliki constrain onDelete cascade terhadap 2 table sehingga akan ikut terhapus
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Transaction::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Drug::class)->constrained()->onDelete('cascade');
            $table->integer('stock')->nullable();
            $table->boolean('used')->nullable();
            $table->boolean('margin')->nullable();
            $table->date('expired');
            $table->string('name');
            $table->string('quantity');
            $table->integer('piece_price');
            $table->integer('total_price');
            $table->integer('discount_price')->nullable();
            $table->integer('flow')->nullable();
            $table->timestamps();
        });
        //bill memiliki constrain onDelete cascade sehingga akan ikut terhapus
        Schema::create('bills',function(Blueprint $table){
            $table->id();
            $table->foreignIdFor(Transaction::class)->constrained()->onDelete('cascade');
            $table->integer('total');
            $table->enum('status',['Belum Bayar','Done']);
            $table->date('pay')->nullable();
            $table->date('due');
            $table->timestamps();
        });
        //bill memiliki constrain onDelete cascade terhadap 3 tabel sehingga akan ikut terhapus
        Schema::create('returs',function(Blueprint $table){
            $table->id();
            $table->foreignIdFor(Drug::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Transaction::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(TransactionDetail::class)->constrained()->onDelete('cascade');
            $table->foreignId('source');
            $table->integer('quantity');
            $table->enum('status',['Belum Kembali','Done']);
            $table->text('reason')->nullable();
            $table->date('arrive')->nullable();
            $table->timestamps();
        });
        //trash memiliki constrain onDelete cascade terhadap 3 tabel sehingga akan ikut terhapus
        Schema::create('trashes',function(Blueprint $table){
            $table->id();
            $table->foreignIdFor(Drug::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Transaction::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(TransactionDetail::class)->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('bills');
        Schema::dropIfExists('returs');
        Schema::dropIfExists('trashes');
    }
};